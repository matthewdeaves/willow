<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

/**
 * DefaultDataImportCommand
 *
 * This command allows importing data from JSON files in the default_data folder into specified tables.
 * It prompts the user to choose a file to import or import all files, and then imports the data into the corresponding table(s),
 * deleting all existing data in the table(s) before importing.
 */
class DefaultDataImportCommand extends Command
{
    /**
     * Configures the option parser for the command.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The option parser to configure.
     * @return \Cake\Console\ConsoleOptionParser The configured option parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('Imports default data from JSON files into specified tables.')
            ->addOption('input', [
                'short' => 'i',
                'help' => 'Input directory containing the JSON files.',
                'default' => ROOT . DS . 'default_data',
            ])
            ->addOption('all', [
                'short' => 'a',
                'help' => 'Import data from all JSON files.',
                'boolean' => true,
            ]);

        return $parser;
    }

    /**
     * Executes the command to import data into a selected table or all tables.
     *
     * This method performs the following steps:
     * 1. Checks and creates the input directory if it doesn't exist.
     * 2. Retrieves and displays a list of available JSON files.
     * 3. Prompts the user to select a file to import or imports all files if the 'all' option is set.
     * 4. Deletes all existing data in the corresponding table(s).
     * 5. Imports the data from the selected file(s) into the corresponding table(s).
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io object.
     * @return int The exit code of the command.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $inputDir = $args->getOption('input');

        // Ensure the input directory exists
        if (!is_dir($inputDir)) {
            mkdir($inputDir, 0755, true);
        }

        // Get list of JSON files
        $files = glob($inputDir . DS . '*.json');
        if (empty($files)) {
            $io->out(__('No JSON files found in the directory: {0}', $inputDir));

            return Command::CODE_ERROR;
        }

        if ($args->getOption('all')) {
            // Import all files
            foreach ($files as $file) {
                $tableName = basename($file, '.json');
                $this->importTable($tableName, $inputDir, $io);
            }
        } else {
            // Display files and ask user to choose
            $io->out(__('Available data files:'));
            foreach ($files as $index => $file) {
                $io->out(sprintf('[%d] %s', $index + 1, basename($file)));
            }

            $choice = $io->ask(__('Choose a file to import by number:'));
            $choiceIndex = (int)$choice - 1;

            if (!isset($files[$choiceIndex])) {
                $io->error(__('Invalid choice.'));

                return Command::CODE_ERROR;
            }

            $selectedFile = $files[$choiceIndex];
            $tableName = basename($selectedFile, '.json');

            // Import the selected table
            $this->importTable($tableName, $inputDir, $io);
        }

        return Command::CODE_SUCCESS;
    }

    /**
     * Imports data from a JSON file into the specified table.
     *
     * @param string $tableName The name of the table to import data into.
     * @param string $inputDir The directory containing the JSON files.
     * @param \Cake\Console\ConsoleIo $io The console io object.
     * @return void
     */
    protected function importTable(string $tableName, string $inputDir, ConsoleIo $io): void
    {
        $table = TableRegistry::getTableLocator()->get($tableName);
        $inputFile = $inputDir . DS . Inflector::underscore($tableName) . '.json';

        if (!file_exists($inputFile)) {
            $io->error(__('Input file not found: {0}', $inputFile));

            return;
        }

        $json = file_get_contents($inputFile);
        $data = json_decode($json, true);

        // Delete all existing records from the table
        $table->deleteAll([]);
        $io->out(__('Deleted all existing records from table: {0}', $tableName));

        foreach ($data as $row) {
            $entity = $table->newEntity($row);

            if ($tableName == 'settings') {
                // Type cast $entity->value based on $entity->value_type
                switch ($entity->value_type) {
                    case 'text':
                        $entity->value = (string)$entity->value;
                        break;
                    case 'numeric':
                        $entity->value = (int)$entity->value;
                        break;
                    case 'bool':
                        $entity->value = (bool)$entity->value;
                        break;
                }
            }

            if (!$table->save($entity)) {
                $io->error(__('Failed to save entity: {0} {1}', [
                    $tableName,
                    json_encode($entity->getErrors()),
                ]));
            }
        }

        $io->success(__('Imported data into table: {0}', $tableName));
    }
}
