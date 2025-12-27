<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Google\GoogleApiService;
use App\Utility\SettingsManager;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\Queue\Job\Message;
use Cake\Queue\QueueManager;
use Interop\Queue\Processor;

/**
 * AbstractTranslateJob Class
 *
 * Base class for translation jobs providing common functionality for
 * translating entities using the Google API service. Reduces code
 * duplication across TranslateArticleJob, TranslateTagJob, and
 * TranslateImageGalleryJob.
 */
abstract class AbstractTranslateJob extends AbstractJob
{
    /**
     * @var \App\Service\Api\Google\GoogleApiService The API service instance.
     */
    protected GoogleApiService $apiService;

    /**
     * Constructor to allow dependency injection for testing
     *
     * @param \App\Service\Api\Google\GoogleApiService|null $googleService
     */
    public function __construct(?GoogleApiService $googleService = null)
    {
        $this->apiService = $googleService ?? new GoogleApiService();
    }

    /**
     * Get the table alias for this entity type (e.g., 'Articles', 'Tags')
     *
     * @return string
     */
    abstract protected function getTableAlias(): string;

    /**
     * Get the message argument keys (e.g., ['id', 'title'] or ['id', 'name'])
     *
     * @return array<string>
     */
    abstract protected function getRequiredArguments(): array;

    /**
     * Get the display name argument key (e.g., 'title' or 'name')
     * Used for logging purposes.
     *
     * @return string
     */
    abstract protected function getDisplayNameArgument(): string;

    /**
     * Get the entity type name for logging (e.g., 'Article', 'Tag', 'Gallery')
     *
     * @return string
     */
    abstract protected function getEntityTypeName(): string;

    /**
     * Get the fields to extract from the entity for translation.
     * Returns an array of field names.
     *
     * @param \Cake\Datasource\EntityInterface $entity
     * @return array<string, string>
     */
    abstract protected function getFieldsForTranslation(EntityInterface $entity): array;

    /**
     * Get HTML fields that need preprocessing (e.g., body content with code blocks)
     *
     * @return array<string>
     */
    protected function getHtmlFields(): array
    {
        return [];
    }

    /**
     * Whether to use HTML format for translation
     *
     * @return bool
     */
    protected function useHtmlFormat(): bool
    {
        return false;
    }

    /**
     * Execute the translation job
     *
     * @param \Cake\Queue\Job\Message $message The job message.
     * @return string|null The result of the job execution.
     */
    public function execute(Message $message): ?string
    {
        $requiredArgs = $this->getRequiredArguments();
        if (!$this->validateArguments($message, $requiredArgs)) {
            return Processor::REJECT;
        }

        $id = $message->getArgument('id');
        $displayName = $message->getArgument($this->getDisplayNameArgument());
        $attempt = $message->getArgument('_attempt', 0);

        // Check if translations are enabled
        if (empty(array_filter(SettingsManager::read('Translations', [])))) {
            $this->log(
                sprintf('No languages enabled for translation: %s : %s', $id, $displayName),
                'warning',
                ['group_name' => static::class],
            );

            return Processor::REJECT;
        }

        $table = $this->getTable($this->getTableAlias());
        $entity = $table->get($id);

        // If there are empty SEO fields, requeue to wait for them to be populated
        if (!empty($table->emptySeoFields($entity))) {
            return $this->handleEmptySeoFields($id, $displayName, $attempt);
        }

        return $this->executeWithErrorHandling($id, function () use ($entity, $table) {
            $fields = $this->getFieldsForTranslation($entity);
            $result = $this->apiService->translateContent(
                $fields,
                $this->getHtmlFields(),
                $this->useHtmlFormat(),
            );

            if ($result) {
                $this->applyTranslations($entity, $table, $result);

                return true;
            }

            return false;
        }, $displayName);
    }

    /**
     * Apply translations to entity and save
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity to update
     * @param \Cake\ORM\Table $table The table to save to
     * @param array<string, array<string, string>> $translations Translations keyed by locale
     * @return void
     */
    protected function applyTranslations(EntityInterface $entity, Table $table, array $translations): void
    {
        foreach ($translations as $locale => $translation) {
            foreach ($translation as $field => $value) {
                $entity->translation($locale)->{$field} = $value;
            }
            $table->save($entity, ['noMessage' => true]);
        }
    }

    /**
     * Handle the case where SEO fields are empty by requeuing the job
     *
     * @param string $id The entity ID
     * @param string $displayName The entity display name (title/name)
     * @param int $attempt The current attempt number
     * @return string|null
     */
    protected function handleEmptySeoFields(string $id, string $displayName, int $attempt): ?string
    {
        if ($attempt >= 5) {
            $this->logJobError(
                $id,
                sprintf('%s still has empty SEO fields after %d attempts', $this->getEntityTypeName(), $attempt),
                $displayName,
            );

            return Processor::REJECT;
        }

        $data = [
            'id' => $id,
            $this->getDisplayNameArgument() => $displayName,
            '_attempt' => $attempt + 1,
        ];

        QueueManager::push(
            static::class,
            $data,
            [
                'config' => 'default',
                'delay' => 10 * ($attempt + 1),
            ],
        );

        $this->log(
            sprintf(
                '%s has empty SEO fields, re-queuing with %d second delay: %s : %s',
                $this->getEntityTypeName(),
                10 * ($attempt + 1),
                $id,
                $displayName,
            ),
            'info',
            ['group_name' => static::class],
        );

        return Processor::ACK;
    }
}
