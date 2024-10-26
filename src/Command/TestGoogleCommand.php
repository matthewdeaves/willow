<?php
declare(strict_types=1);

namespace App\Command;

use App\Utility\SettingsManager;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Log\LogTrait;
use Exception;
use Google\Cloud\Translate\V2\TranslateClient;


class TestGoogleCommand extends Command
{
    use LogTrait;

    /**
     * Stores the ConsoleIo instance for output operations.
     *
     * @var \Cake\Console\ConsoleIo
     */
    protected ConsoleIo $io;

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        return $parser;
    }

    /**
     * Executes the command to resize images.
     *
     * This method iterates through the specified models, retrieves images,
     * and resizes them according to the configured sizes.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int The exit code of the command.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $apiKey = SettingsManager::read('Google.apiKey', '');
        $text = 'Hello world!';
        $targetLanguage = 'fr';

        $translate = new TranslateClient([
            'key' => $apiKey
        ]);

        $result = $translate->translate($text, [
            'target' => $targetLanguage
        ]);

        $io->out('Original text: ' . $text);
        $io->out('Translated text: ' . $result['text']);

        $texts = [
            'Hello world!',
            'How are you?',
            'Goodbye!',
        ];
        $targetLanguage = 'fr';
    
        $results = $translate->translateBatch($texts, [
            'target' => $targetLanguage
        ]);
    
        foreach ($results as $index => $result) {
            $io->out('----');
            $io->out('Original text: ' . $texts[$index]);
            $io->out('Translated text: ' . $result['text']);
            $io->out('----');
        }


        return static::CODE_SUCCESS;
    }
}
