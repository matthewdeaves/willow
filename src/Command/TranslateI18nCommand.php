<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\ORM\TableRegistry;
use Cake\Queue\QueueManager;

/**
 * Class TranslateI18nCommand
 *
 * This command finds internationalisations with empty message_str and queues translation jobs in batches.
 */
class TranslateI18nCommand extends Command
{
    /**
     * Default batch size for processing.
     *
     * @var int
     */
    protected int $batchSize = 50; // Adjust this number based on your AI's capacity

    /**
     * Executes the command to queue translation jobs for empty internationalisations.
     *
     * @param \Cake\Console\Arguments $args The command line arguments.
     * @param \Cake\Console\ConsoleIo $io The console input/output.
     * @return void
     */
    public function execute(Arguments $args, ConsoleIo $io): void
    {
        // Fetch the I18n table
        $i18nTable = TableRegistry::getTableLocator()->get('internationalisations');

        // Find all records with empty message_str
        $emptyInternationalisations = $i18nTable->find()
            ->where(['message_str' => ''])
            ->all();

        $batch = [];
        foreach ($emptyInternationalisations as $internationalisation) {
            $batch['internationalisations'][] = $internationalisation->id;

            // If the batch size is reached, queue the batch and reset
            if (count($batch['internationalisations']) >= $this->batchSize) {
                $this->queueBatch($batch, $io);
                $batch = [];
            }
        }

        // Queue any remaining messages in the last batch
        if (!empty($batch)) {
            $this->queueBatch($batch, $io);
        }
    }

    /**
     * Queues a batch of messages for translation.
     *
     * @param array $batch The batch of messages to queue.
     * @param \Cake\Console\ConsoleIo $io The console input/output.
     * @return void
     */
    protected function queueBatch(array $batch, ConsoleIo $io): void
    {
        // Queue a job to translate the batch of messages
        QueueManager::push('App\Job\TranslateI18nJob', $batch);
        $io->out(__('Queued translation job for batch of {0} messages', [count($batch['internationalisations'])]));
    }
}
