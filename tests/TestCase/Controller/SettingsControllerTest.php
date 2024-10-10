<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use Authentication\AuthenticationService;
use Authentication\Authenticator\Result;
use Authentication\Identity;
use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Admin\SettingsController Test Case
 *
 * @uses \App\Controller\Admin\SettingsController
 */
class SettingsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Settings',
        'app.Users',
    ];

    /**
     * Setup method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->disableErrorHandlerMiddleware();

        $this->configRequest([
            'environment' => [
                'AUTH_TYPE' => 'Form',
            ],
        ]);

        // Clear rate limiting cache
        Cache::clear('rate_limit');
    }

    /**
     * Setup authentication for tests
     *
     * @param string|null $id The ID of the user to authenticate, or null for no authentication
     * @return void
     */
    private function setupAuthentication(?string $id = null): void
    {
        if ($id === null) {
            $identity = null;
            $result = new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
        } else {
            $usersTable = TableRegistry::getTableLocator()->get('Users');
            $user = $usersTable->find()->where(['id' => $id])->first();

            if ($user) {
                $identity = new Identity([
                    'id' => $id,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                ]);
                $result = new Result($identity, Result::SUCCESS);
            } else {
                $identity = null;
                $result = new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
            }
        }

        $this->session(['Auth' => $identity]);

        $authenticationService = $this->createMock(AuthenticationService::class);
        $authenticationService->method('getIdentity')->willReturn($identity);
        $authenticationService->method('getResult')->willReturn($result);

        $this->_controller = $this->getMockBuilder('App\Controller\Admin\SettingsController')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_controller->Authentication = $authenticationService;
    }

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\Admin\SettingsController::index()
     */
    public function testIndex(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f'; // Assuming this is a valid admin ID in your fixtures
        $this->setupAuthentication($adminId);

        $this->get('/admin/settings');

        $this->assertResponseOk();
        $this->assertResponseContains('Edit Settings');

        $viewVars = $this->viewVariable('groupedSettings');
        $this->assertNotEmpty($viewVars);
        $this->assertIsArray($viewVars);

        // Check for default keys
        $this->assertArrayHasKey('AI', $viewVars);
        $this->assertArrayHasKey('Email', $viewVars);
        $this->assertArrayHasKey('ImageSizes', $viewVars);
        $this->assertArrayHasKey('SEO', $viewVars);

        // Check for some key_names
        $this->assertArrayHasKey('value', $viewVars['AI']['anthropicApiKey']);
        $this->assertArrayHasKey('value', $viewVars['Email']['reply_email']);
        $this->assertArrayHasKey('value', $viewVars['ImageSizes']['extra-large']);
        $this->assertArrayHasKey('value', $viewVars['SEO']['siteStrapline']);

        // Check for some values
        $this->assertEquals('50', $viewVars['ImageSizes']['teeny']['value']);
        $this->assertEquals('500', $viewVars['ImageSizes']['extra-large']['value']);
    }

    /**
     * Test saveSettings method
     *
     * @return void
     * @uses \App\Controller\Admin\SettingsController::saveSettings()
     */
    public function testSaveSettings(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->setupAuthentication($adminId);

        $this->enableCsrfToken();
        $data = [
            'ImageSizes' => [
                'extra-large' => '501',
            ],
        ];

        $this->post('/admin/settings/saveSettings', $data);

        $this->assertResponseSuccess();
        $this->assertRedirect(['controller' => 'Settings', 'action' => 'index', 'prefix' => 'Admin']);
        $this->assertFlashMessage('The settings have been saved.');

        // Verify the settings were actually saved
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');
        $setting = $settingsTable->find()->where(['category' => 'ImageSizes', 'key_name' => 'extra-large'])->first();
        $this->assertEquals('501', $setting->value);
    }

    /**
     * Test saveSettings method with invalid data
     *
     * @return void
     * @uses \App\Controller\Admin\SettingsController::saveSettings()
     */
    public function testSaveSettingsInvalidData(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f'; // Assuming this is a valid admin ID in your fixtures
        $this->setupAuthentication($adminId);

        $this->enableCsrfToken();
        $data = [
            'NonExistentCategory' => [
                'non_existent_key' => 'Some Value',
            ],
        ];

        $this->post('/admin/settings/saveSettings', $data);

        $this->assertResponseSuccess();
        $this->assertRedirect(['controller' => 'Settings', 'action' => 'index', 'prefix' => 'Admin']);
        $this->assertFlashMessage('Some settings could not be saved. Please, try again.');
    }
}
