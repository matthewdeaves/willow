<?php
namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\ORM\TableRegistry;

class Process18nCommand extends Command
{
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $potFile = 'resources/locales/default.pot';
        $translations = $this->parsePoFile($potFile);

        // Assuming you have an InternationalisationsTable to handle translation records
        $internationalisationsTable = TableRegistry::getTableLocator()->get('Internationalisations');

        // Define the locales you want to support
        $locales = ['de_DE', 'fr_FR'];

        foreach ($translations as $messageId => $messageStr) {
            foreach ($locales as $locale) {
                // Check if the message ID exists in the database for the given locale
                $internationalisation = $internationalisationsTable->find()
                    ->where(['message_id' => $messageId, 'locale' => $locale])
                    ->first();

                if (!$internationalisation) {
                    // If not, add it with an empty translation for each locale
                    $internationalisation = $internationalisationsTable->newEntity([
                        'message_id' => $messageId,
                        'locale' => $locale,
                        'message_str' => ''
                    ]);
                    $internationalisationsTable->save($internationalisation);
                }
            }
        }

        $io->success('Internationalisations updated successfully.');
    }

    private function parsePoFile($file)
    {
        $translations = [];
        $currentMsgId = null;
        $currentMsgStr = '';
    
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
    
            if (strpos($line, 'msgid "') === 0) {
                if ($currentMsgId !== null) {
                    $translations[$currentMsgId] = $currentMsgStr;
                    $currentMsgStr = '';
                }
                $currentMsgId = substr($line, 7, -1);
            } elseif (strpos($line, 'msgstr "') === 0) {
                $currentMsgStr = substr($line, 8, -1);
            } elseif ($currentMsgId !== null && strpos($line, '"') === 0) {
                if (strpos($line, 'msgid') === false && strpos($line, 'msgstr') === false) {
                    $currentMsgStr .= substr($line, 1, -1);
                }
            }
        }
    
        // Add the last translation
        if ($currentMsgId !== null) {
            $translations[$currentMsgId] = $currentMsgStr;
        }
    
        return $translations;
    }
}