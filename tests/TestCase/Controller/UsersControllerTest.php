<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Table\UsersTable;
use Cake\Datasource\FactoryLocator;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\UsersController Test Case
 *
 * This test case is designed to test the UsersController functionalities
 * such as login, logout, and registration processes.
 *
 * @uses \App\Controller\UsersController
 */
class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures to be loaded for the test case
     *
     * @var array
     */
    protected array $fixtures = ['app.Users', 'app.Articles', 'app.Tags', 'app.ArticlesTags', 'app.BlockedIps', 'app.Settings'];

    /**
     * UsersTable instance
     *
     * @var \App\Model\Table\UsersTable
     */
    protected UsersTable $Users;

    /**
     * Setup method
     *
     * This method is called before each test. It initializes the UsersTable
     * and configures the request environment for authentication.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Users = FactoryLocator::get('Table')->get('Users');

        // Configure authentication
        $this->configRequest([
            'environment' => [
                'AUTH_TYPE' => 'Form',
            ],
        ]);
    }

    /**
     * Test login access for unauthenticated users
     *
     * This test ensures that unauthenticated users can access the login page.
     *
     * @return void
     */
    public function testLoginAccessForUnauthenticatedUsers(): void
    {
        $this->get('/users/login');
        $this->assertResponseOk();
    }

    /**
     * Test successful login for non-admin users
     *
     * This test checks if a non-admin user can successfully log in and be redirected to the homepage.
     *
     * @return void
     */
    public function testSuccessfulNonAdminLogin(): void
    {
        $this->enableCsrfToken();
        $this->post('/users/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);
        $this->assertRedirect('/');
    }

    /**
     * Test successful login for admin users
     *
     * This test verifies that an admin user can log in and be redirected to the admin articles page.
     *
     * @return void
     */
    public function testSuccessfulAdminLogin(): void
    {
        $this->enableCsrfToken();
        $this->post('/users/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        $this->assertRedirect('/admin/articles');
    }

    /**
     * Test login with incorrect credentials
     *
     * This test ensures that users with incorrect credentials are not redirected and receive an error message.
     *
     * @return void
     */
    public function testLoginWithBadCredentials(): void
    {
        $this->enableCsrfToken();
        $this->post('/users/login', [
            'email' => 'wrong@example.com',
            'password' => 'incorrectpassword',
        ]);

        // Assert that the user is not redirected (stays on the login page)
        $this->assertNoRedirect();

        // Assert that a flash message is set (assuming your controller sets one for failed login)
        $this->assertResponseContains('Invalid username or password');

        // Optionally, check that the user is not authenticated
        $this->assertNull($this->_controller->Authentication->getIdentity());
    }

    /**
     * Test logout functionality
     *
     * This test checks if a logged-in user can successfully log out and be redirected to the login page.
     *
     * @return void
     */
    public function testLogout(): void
    {
        $this->session(['Auth' => ['User' => ['id' => 1]]]);
        $this->get('/users/logout');
        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
    }

    /**
     * Test successful user registration
     *
     * This test verifies that a new user can register successfully and be redirected to the login page.
     *
     * @return void
     */
    public function testSuccessfulRegistration(): void
    {
        $this->enableCsrfToken();
        $this->post('/users/register', [
            'email' => 'newuser@example.com',
            'password' => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ]);
        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
    }

    /**
     * Test registration failure
     *
     * This test ensures that registration fails with invalid data and returns a 403 response code.
     *
     * @return void
     */
    public function testRegistrationFailure(): void
    {
        $this->post('/users/register', [
            'email' => 'invalidemail',
            'password' => 'short',
            'confirm_password' => 'mismatch',
        ]);
        $this->assertResponseCode(403);
    }
}
