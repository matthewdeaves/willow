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
        $locales = [
            'de_DE', // German (Germany)
            'fr_FR', // French (France)
            /*'es_ES', // Spanish (Spain)
            'it_IT', // Italian (Italy)
            'pt_PT', // Portuguese (Portugal)
            'nl_NL', // Dutch (Netherlands)
            'pl_PL', // Polish (Poland)
            'ru_RU', // Russian (Russia)
            'sv_SE', // Swedish (Sweden)
            'da_DK', // Danish (Denmark)
            'fi_FI', // Finnish (Finland)
            'no_NO', // Norwegian (Norway)
            'el_GR', // Greek (Greece)
            'tr_TR', // Turkish (Turkey)
            'cs_CZ', // Czech (Czech Republic)
            'hu_HU', // Hungarian (Hungary)
            'ro_RO', // Romanian (Romania)
            'sk_SK', // Slovak (Slovakia)
            'sl_SI', // Slovenian (Slovenia)
            'bg_BG', // Bulgarian (Bulgaria)
            'hr_HR', // Croatian (Croatia)
            'et_EE', // Estonian (Estonia)
            'lv_LV', // Latvian (Latvia)
            'lt_LT', // Lithuanian (Lithuania)
            'uk_UA', // Ukrainian (Ukraine)*/
        ];
        
        // Fetch the I18n table
        $i18nTable = TableRegistry::getTableLocator()->get('internationalisations');

        foreach ($locales as $locale) {
            // Find all records with empty message_str for the current locale
            $emptyInternationalisations = $i18nTable->find()
                ->where(['message_str' => '', 'locale' => $locale])
                ->all();

            $batch = ['locale' => $locale, 'internationalisations' => []];
            foreach ($emptyInternationalisations as $internationalisation) {
                $batch['internationalisations'][] = $internationalisation->id;

                // If the batch size is reached, queue the batch and reset
                if (count($batch['internationalisations']) >= $this->batchSize) {
                    $this->queueBatch($batch, $io);
                    $batch = ['locale' => $locale, 'internationalisations' => []];
                }
            }

            // Queue any remaining messages in the last batch
            if (!empty($batch['internationalisations'])) {
                $this->queueBatch($batch, $io);
            }
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
        //IF SETTING AI ENABLED!
        // Queue a job to translate the batch of messages
        QueueManager::push('App\Job\TranslateI18nJob', $batch);
        $io->out(__('Queued translation job for batch of {0} messages for locale {1}', [count($batch['internationalisations']), $batch['locale']]));
    }
}
