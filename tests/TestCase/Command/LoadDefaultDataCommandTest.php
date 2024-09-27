<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use Cake\Command\Command;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;

/**
 * App\Command\LoadDefaultDataCommand Test Case
 *
 * @uses \App\Command\LoadDefaultDataCommand
 */
class LoadDefaultDataCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.EmailTemplates',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->configApplication(Configure::read('App.namespace') . '\Application', []);
    }

    /**
     * Test execute method with dry-run option
     *
     * @return void
     * @uses \App\Command\LoadDefaultDataCommand::execute()
     */
    public function testExecuteDryRun(): void
    {
        $this->exec('load_default_data email_templates --dry-run');
        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertOutputContains('Dry run mode enabled for table: email_templates');
        $this->assertOutputContains('Dry run: Would insert new data into email_templates table');
    }

    /**
     * Test execute method without dry-run option
     *
     * @return void
     * @uses \App\Command\LoadDefaultDataCommand::execute()
     */
    public function testExecute(): void
    {
        $this->exec('load_default_data email_templates');
        $this->assertExitCode(Command::CODE_SUCCESS);
        $this->assertOutputContains('Default data loaded into email_templates table.');

        // Verify that the data was actually inserted
        $connection = ConnectionManager::get('test');
        $result = $connection->execute('SELECT * FROM email_templates WHERE template_identifier = ?', ['confirm_email'])->fetchAll('assoc');
        $this->assertNotEmpty($result);
        $this->assertEquals('Confirm your email', $result[0]['name']);
    }

    /**
     * Test execute method with unsupported table
     *
     * @return void
     * @uses \App\Command\LoadDefaultDataCommand::execute()
     */
    public function testExecuteUnsupportedTable(): void
    {
        $this->exec('load_default_data unsupported_table');
        $this->assertExitCode(Command::CODE_ERROR);
        $this->assertErrorContains('No default data method found for table: unsupported_table');
    }

    /**
     * Test that existing data is deleted before inserting new data
     *
     * @return void
     * @uses \App\Command\LoadDefaultDataCommand::execute()
     */
    public function testDeleteExistingData(): void
    {
        $connection = ConnectionManager::get('test');

        // Insert a dummy record with all required fields
        $connection->execute(
            'INSERT INTO email_templates (id, template_identifier, name, subject, body_html, body_plain) VALUES (?, ?, ?, ?, ?, ?)',
            ['dummy-uuid', 'confirm_email', 'Old Template', 'Old Subject', 'Old HTML Body', 'Old Plain Body']
        );

        $this->exec('load_default_data email_templates');
        $this->assertExitCode(Command::CODE_SUCCESS);

        // Verify that the old record was deleted and new one inserted
        $result = $connection->execute('SELECT * FROM email_templates WHERE template_identifier = ?', ['confirm_email'])->fetchAll('assoc');
        $this->assertCount(1, $result);
        $this->assertNotEquals('Old Template', $result[0]['name']);
        $this->assertEquals('Confirm your email', $result[0]['name']);
    }
}
