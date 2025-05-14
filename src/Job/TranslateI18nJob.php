<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Anthropic\AnthropicApiService;
use App\Service\Api\Google\GoogleApiService;
use App\Utility\SettingsManager;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Exception;
use Interop\Queue\Processor;

/**
 * TranslateI18nJob
 *
 * This job processes messages to update internationalizations using either the Anthropic or Google API.
 * It retrieves the internationalizations based on the provided IDs and updates them in batches.
 */
class TranslateI18nJob implements JobInterface
{
    use LogTrait;

    /**
     * Maximum number of attempts for the job.
     *
     * @var int|null
     */
    public static int $maxAttempts = 3;

    /**
     * Whether there should be only one instance of a job on the queue at a time.
     *
     * @var bool
     */
    public static bool $shouldBeUnique = true;

    /**
     * @var \App\Service\Api\Anthropic\AnthropicApiService|\App\Service\Api\Google\GoogleApiService The API service instance.
     */
    private AnthropicApiService|GoogleApiService $apiService;

    /**
     * Constructor to allow dependency injection for testing
     *
     * @param \App\Service\Api\Anthropic\AnthropicApiService|null $anthropicService
     * @param \App\Service\Api\Google\GoogleApiService|null $googleService
     */
    public function __construct(?AnthropicApiService $anthropicService = null, ?GoogleApiService $googleService = null)
    {
        $apiProvider = SettingsManager::read('i18n.provider', 'google');

        if ($apiProvider === 'google') {
            $this->apiService = $googleService ?? new GoogleApiService();
        } else {
            $this->apiService = $anthropicService ?? new AnthropicApiService();
        }
    }

    /**
     * Executes the job to update internationalizations.
     *
     * This method processes the message, retrieves the internationalizations based on the provided IDs,
     * and updates them in batches using the selected API service.
     *
     * @param \Cake\Queue\Job\Message $message The message containing internationalization IDs.
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure.
     */
    public function execute(Message $message): ?string
    {
        $internationalisations = $message->getArgument('internationalisations');
        $locale = $message->getArgument('locale');

        if (empty($internationalisations) || empty($locale)) {
            $this->log(
                sprintf('Missing required arguments in the message.'),
                'error',
                ['group_name' => 'App\Job\TranslateI18nJob'],
            );

            return Processor::REJECT;
        }

        $i18nTable = TableRegistry::getTableLocator()->get('internationalisations');
        $internationalizations = $i18nTable->find()
            ->select(['id', 'locale', 'message_id'])
            ->where(['id IN' => $internationalisations])
            ->all();

        if ($internationalizations->isEmpty()) {
            $this->log(
                sprintf('No internationalizations found for the provided IDs.'),
                'error',
                ['group_name' => 'App\Job\TranslateI18nJob'],
            );

            return Processor::REJECT;
        }

        $messageStrings = $internationalizations->extract('message_id')->toArray();

        try {
            $translatedMessages = $this->apiService->translateStrings(
                $messageStrings,
                'en_GB',
                $locale,
            );

            // Iterate over the translated messages and update the database
            foreach ($translatedMessages['translations'] as $index => $translation) {
                $originalMessage = $messageStrings[$index];

                // Find the existing translation record for the given locale and original message
                $existingTranslation = $i18nTable->find()
                    ->where([
                        'message_id' => $originalMessage,
                        'locale' => $locale,
                    ])
                    ->first();

                if ($existingTranslation) {
                    // Update the existing translation record
                    $existingTranslation->message_str = $translation['translated'];
                    if (!$i18nTable->save($existingTranslation)) {
                        $this->log(
                            sprintf('Failed to update translation for message ID: %s', $originalMessage),
                            'error',
                            ['group_name' => 'App\Job\TranslateI18nJob'],
                        );
                    }
                }
            }

            $this->log(
                sprintf('Internationalizations updated successfully.'),
                'info',
                ['group_name' => 'App\Job\TranslateI18nJob'],
            );

            return Processor::ACK;
        } catch (Exception $e) {
            $this->log(
                sprintf('Failed to update internationalizations: %s', $e->getMessage()),
                'error',
                ['group_name' => 'App\Job\TranslateI18nJob'],
            );
        }

        return Processor::REJECT;
    }
}
