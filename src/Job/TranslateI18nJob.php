<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\AnthropicApiService;
use Cake\I18n\I18n;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

/**
 * TranslateI18nJob
 *
 * This job processes messages to update internationalizations using the Anthropic API.
 * It retrieves the internationalizations based on the provided IDs and updates them in batches.
 */
class TranslateI18nJob implements JobInterface
{
    /**
     * @var \App\Service\Api\AnthropicApiService The Anthropic API service instance.
     */
    private AnthropicApiService $anthropicService;

    /**
     * Constructor to allow dependency injection for testing
     *
     * @param \App\Service\Api\AnthropicApiService|null $anthropicService
     */
    public function __construct(?AnthropicApiService $anthropicService = null)
    {
        $this->anthropicService = $anthropicService ?? new AnthropicApiService();
    }

    /**
     * Executes the job to update internationalizations.
     *
     * This method processes the message, retrieves the internationalizations based on the provided IDs,
     * and updates them in batches using the Anthropic API service.
     *
     * @param \Cake\Queue\Job\Message $message The message containing internationalization IDs.
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure.
     */
    public function execute(Message $message): ?string
    {
        $ids = $message->getArgument('internationalisations');

        if (empty($ids)) {
            Log::warning('No internationalization IDs provided in the message.');
            return Processor::REJECT;
        }

        $i18nTable = TableRegistry::getTableLocator()->get('internationalisations');
        $internationalizations = $i18nTable->find()
            ->select(['id', 'locale', 'message_id', 'message_str'])
            ->where(['id IN' => $ids])
            ->all();

        
        return Processor::ACK;
    }
}