<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

/**
 * DefaultDataExportCommand
 *
 * This command allows exporting data from a selected database table to a JSON file.
 * It excludes timestamp columns (created, modified, created, modified) and the ID column from the export.
 */
class DefaultDataExportCommand extends Command
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
            ->setDescription('Exports default data from a specified table to a JSON file.')
            ->addOption('output', [
                'short' => 'o',
                'help' => 'Output directory for the exported JSON file.',
                'default' => ROOT . DS . 'default_data',
            ]);

        return $parser;
    }

    /**
     * Executes the command to export data from a selected table.
     *
     * This method performs the following steps:
     * 1. Checks and creates the output directory if it doesn't exist.
     * 2. Retrieves and displays a list of available database tables.
     * 3. Prompts the user to select a table.
     * 4. Exports the data from the selected table, excluding timestamp columns and the ID column.
     * 5. Saves the exported data as a JSON file.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io object.
     * @return int The exit code of the command.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $outputDir = $args->getOption('output');

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Get the list of tables
        $connection = ConnectionManager::get('default');
        $tables = $connection->getSchemaCollection()->listTables();

        // Display the list of tables
        $io->out('Available tables:');
        foreach ($tables as $index => $table) {
            $io->out(sprintf('[%d] %s', $index + 1, $table));
        }

        // Prompt the user to select a table
        $choice = $io->ask('Please select a table to export by number:');
        $tableIndex = (int)$choice - 1;

        if (!isset($tables[$tableIndex])) {
            $io->error('Invalid selection. Exiting.');

            return Command::CODE_ERROR;
        }

        $tableName = $tables[$tableIndex];

        // Export the selected table
        $table = TableRegistry::getTableLocator()->get($tableName);
        $query = $table->find();

        // Get all columns except 'id', 'created', 'modified', 'created', and 'modified'
        $schema = $table->getSchema();
        $columns = $schema->columns();
        $columnsToExclude = ['id', 'created', 'modified'];
        $columns = array_diff($columns, $columnsToExclude);

        $query->select($columns);
        $data = $query->disableHydration()->all()->toArray();

        $outputFile = $outputDir . DS . Inflector::underscore($tableName) . '.json';
        $json = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents($outputFile, $json);

        $io->success(sprintf('Data exported to %s', $outputFile));

        return Command::CODE_SUCCESS;
    }
}
