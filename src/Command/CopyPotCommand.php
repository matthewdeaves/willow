<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * CopyPotCommand class
 *
 * This command copies the default.pot file to language-specific default_empty.po files.
 */
class CopyPotCommand extends Command
{
    /**
     * Configures the command's option parser.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The option parser to configure.
     * @return \Cake\Console\ConsoleOptionParser The updated option parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription(__('Copies default.pot to language-specific default_empty.po files.'));

        return $parser;
    }

    /**
     * Executes the command.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console input/output.
     * @return int The exit code. 0 for success, 1 for error.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $languages = [
            'fr_FR', // French
            'de_DE', // German
            'it_IT', // Italian
            'es_ES', // Spanish
            'nl_NL', // Dutch
            'sv_SE', // Swedish
        ];

        $sourcePath = ROOT . DS . 'resources' . DS . 'locales' . DS . 'default.pot';

        if (!file_exists($sourcePath)) {
            $io->error(__('Source file default.pot not found at {0}', $sourcePath));

            return self::CODE_ERROR;
        }

        foreach ($languages as $lang) {
            $targetDir = ROOT . DS . 'resources' . DS . 'locales' . DS . $lang;
            $targetPath = $targetDir . DS . 'default_empty.po';

            if (!is_dir($targetDir)) {
                if (!mkdir($targetDir, 0755, true)) {
                    $io->error(__('Could not create directory for {0}', $lang));
                    continue;
                }
            }

            if (copy($sourcePath, $targetPath)) {
                $io->success(__('Copied default.pot to {0}', $targetPath));
            } else {
                $io->error(__('Failed to copy default.pot to {0}', $targetPath));
            }
        }

        return self::CODE_SUCCESS;
    }
}
