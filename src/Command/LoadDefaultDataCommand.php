<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\ConnectionManager;
use Cake\Log\LogTrait;
use Cake\Utility\Inflector;
use Cake\Utility\Text;
use Exception;

/**
 * LoadDefaultDataCommand
 *
 * This command is responsible for loading default data into specified database tables.
 * It provides a flexible way to populate tables with initial data, particularly useful
 * for setting up email templates and other default records.
 */
class LoadDefaultDataCommand extends Command
{
    use LogTrait;

    /**
     * Builds the option parser for the command.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined.
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser
            ->setDescription(
                'Loads default data into specified database tables. ' .
                'This command is useful for initializing tables with predefined data, ' .
                'such as email templates or system settings.'
            )
            ->addArgument('table', [
                'help' => 'The name of the table to load default data into. ' .
                          'Currently supported: email_templates',
                'required' => true,
            ])
            ->addOption('dry-run', [
                'help' => 'Run the command without making any changes to the database.',
                'boolean' => true,
                'short' => 'd',
            ])
            ->setEpilog(
                "Examples:\n" .
                "  cake load_default_data email_templates\n" .
                '  cake load_default_data email_templates --dry-run'
            );

        return $parser;
    }

    /**
     * Executes the command to load default data into the specified table.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io.
     * @return int The exit code.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $table = $args->getArgument('table');
        $method = 'load' . Inflector::camelize($table) . 'Data';
        $dryRun = $args->getOption('dry-run');

        $this->log(
            __('Starting LoadDefaultDataCommand for table: {0}', [$table]),
            'info',
            ['group_name' => 'default_data']
        );

        if ($dryRun) {
            $this->log(
                __('Dry run mode enabled for table: {0}', [$table]),
                'info',
                ['group_name' => 'default_data']
            );
        }

        if (method_exists($this, $method)) {
            try {
                $this->$method($io, $dryRun);
                $message = $dryRun ? 'Dry run completed for {table} table.' : 'Default data loaded into {table} table.';
                $this->log(
                    __($message, ['table' => $table]),
                    'info',
                    ['group_name' => 'default_data']
                );

                return Command::CODE_SUCCESS;
            } catch (Exception $e) {
                $this->log(
                    __('Error loading default data for table: {0}. Error: {1}', [$table, $e->getMessage()]),
                    'error',
                    ['group_name' => 'default_data']
                );

                return Command::CODE_ERROR;
            }
        } else {
            $this->log(
                __('No default data method found for table: {0}', [$table]),
                'error',
                ['group_name' => 'default_data']
            );

            return Command::CODE_ERROR;
        }
    }

    /**
     * Loads default data into the email_templates table.
     *
     * This method deletes existing data for the 'confirm_email' template
     * and inserts new default data.
     *
     * @param \Cake\Console\ConsoleIo $io The console io.
     * @param bool $dryRun Whether to perform a dry run.
     * @return void
     */
    protected function loadEmailTemplatesData(ConsoleIo $io, bool $dryRun): void
    {
        $this->log(
            __('Starting to load default data for email_templates table. Dry run: {0}', [$dryRun ? 'Yes' : 'No']),
            'info',
            ['group_name' => 'default_data']
        );

        if (!$dryRun) {
            // Delete existing data
            $this->deleteData('email_templates', ['template_identifier' => 'confirm_email'], $io);
        }

        $connection = ConnectionManager::get('default');

        $uuid = Text::uuid();

        $query = "INSERT INTO `email_templates` 
        (`id`, `template_identifier`, `name`, `subject`, `body_html`, `body_plain`) 
        VALUES (:id, :template_identifier, :name, :subject, :body_html, :body_plain)";

        $params = [
            'id' => $uuid,
            'template_identifier' => 'confirm_email',
            'name' => 'Confirm your email',
            'subject' => 'Confirm your email',
            'body_html' => '<p>Hello {username},</p><p>Thanks for registering for an account. ' .
                           'Click this link to activate it!</p><p>{confirm_email_link}</p>',
            'body_plain' => 'Hello {username},Thanks for registering for an account. ' .
                            'Click this link to activate it!{confirm_email_link}',
        ];

        if ($dryRun) {
            $this->log(
                __('Dry run: Would insert new data into email_templates table: {0}', [json_encode($params)]),
                'info',
                ['group_name' => 'default_data']
            );
        } else {
            try {
                $connection->execute($query, $params);
                $this->log(
                    __('Successfully inserted new data into email_templates table with id: {0}', [$uuid]),
                    'info',
                    ['group_name' => 'default_data']
                );
            } catch (Exception $e) {
                $this->log(
                    __('Error inserting data into email_templates table: {0}', [$e->getMessage()]),
                    'error',
                    ['group_name' => 'default_data']
                );
                throw $e;
            }
        }
    }

    /**
     * Loads default data into the settings table.
     *
     * This method inserts predefined settings data into the database. It supports a dry run mode
     * where actions are logged without actually inserting data. The method handles various categories
     * of settings including ImageSizes, Email, SEO, and AI.
     *
     * @param \Cake\Console\ConsoleIo $io The ConsoleIo instance for output handling.
     * @param bool $dryRun If true, performs a dry run without inserting data.
     * @return void
     * @throws \Exception If there's an error during data insertion.
     * @uses \Cake\Database\Connection
     * @uses \Cake\Utility\Text
     * @uses \Cake\Log\Log
     */
    protected function loadSettingsData(ConsoleIo $io, bool $dryRun): void
    {
        $this->log(
            __('Starting to load default data for settings table. Dry run: {0}', [$dryRun ? 'Yes' : 'No']),
            'info',
            ['group_name' => 'default_data']
        );

        $connection = ConnectionManager::get('default');

        $data = [
            ['id' => Text::uuid(), 'category' => 'ImageSizes',
            'key_name' => 'massive', 'value' => '800', 'is_numeric' => 1],
            ['id' => Text::uuid(), 'category' => 'ImageSizes',
            'key_name' => 'extra-large', 'value' => '500', 'is_numeric' => 1],
            ['id' => Text::uuid(), 'category' => 'ImageSizes',
            'key_name' => 'large', 'value' => '400', 'is_numeric' => 1],
            ['id' => Text::uuid(), 'category' => 'ImageSizes',
            'key_name' => 'medium', 'value' => '300', 'is_numeric' => 1],
            ['id' => Text::uuid(), 'category' => 'ImageSizes',
            'key_name' => 'small', 'value' => '200', 'is_numeric' => 1],
            ['id' => Text::uuid(), 'category' => 'ImageSizes',
            'key_name' => 'tiny', 'value' => '100', 'is_numeric' => 1],
            ['id' => Text::uuid(), 'category' => 'ImageSizes',
            'key_name' => 'teeny', 'value' => '50', 'is_numeric' => 1],
            ['id' => Text::uuid(), 'category' => 'ImageSizes',
            'key_name' => 'micro', 'value' => '10', 'is_numeric' => 1],
            ['id' => Text::uuid(), 'category' => 'Email',
            'key_name' => 'reply_email', 'value' => 'noreply@example.com', 'is_numeric' => 0,],
            ['id' => Text::uuid(), 'category' => 'SEO',
            'key_name' => 'siteStrapline', 'value' => 'Welcome to Willow CMS', 'is_numeric' => 0],
            ['id' => Text::uuid(), 'category' => 'AI',
            'key_name' => 'anthropicApiKey', 'value' => 'your-api-key-here', 'is_numeric' => 0],
        ];

        foreach ($data as $row) {
            if ($dryRun) {
                $this->log(
                    __('Dry run: Would insert new data into settings table: {0}', [json_encode($row)]),
                    'info',
                    ['group_name' => 'default_data']
                );
            } else {
                try {
                    $query = "INSERT INTO `settings` 
                    (`id`, `category`, `key_name`, `value`, `is_numeric`, `created`, `modified`) 
                    VALUES (:id, :category, :key_name, :value, :is_numeric, :created, :modified)";

                    $connection->execute($query, $row);
                    $this->log(
                        __('Successfully inserted new data into settings table with id: {0}', [$row['id']]),
                        'info',
                        ['group_name' => 'default_data']
                    );
                } catch (Exception $e) {
                    $this->log(
                        __('Error inserting data into settings table: {0}', [$e->getMessage()]),
                        'error',
                        ['group_name' => 'default_data']
                    );
                    throw $e;
                }
            }
        }
    }

    /**
     * Deletes data from a specified table based on conditions.
     *
     * @param string $table The name of the table to delete from.
     * @param array $conditions The conditions for deletion.
     * @param \Cake\Console\ConsoleIo $io The console io.
     * @return void
     */
    protected function deleteData(string $table, array $conditions, ConsoleIo $io): void
    {
        $connection = ConnectionManager::get('default');
        $query = "DELETE FROM `$table`";
        $params = [];

        if ($table === 'email_templates' && isset($conditions['template_identifier'])) {
            $query .= ' WHERE template_identifier = :template_identifier';
            $params['template_identifier'] = $conditions['template_identifier'];
        } else {
            // For other tables, implement a more generic condition handling
            // This is just a placeholder and should be adjusted based on your needs
            $query .= ' WHERE 1=1';
        }

        $this->log(
            __('Deleting data from {0} table with conditions: {1}', [$table, json_encode($conditions)]),
            'info',
            ['group_name' => 'default_data']
        );

        try {
            $statement = $connection->execute($query, $params);
            $count = $statement->rowCount();
            $this->log(
                __('Deleted {0} rows from {1} table', [$count, $table]),
                'info',
                ['group_name' => 'default_data']
            );
        } catch (Exception $e) {
            $this->log(
                __('Error deleting data from {0} table: {1}', [$table, $e->getMessage()]),
                'error',
                ['group_name' => 'default_data']
            );
            throw $e;
        }
    }
}
