<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Table\UsersTable;
use Authentication\AuthenticationService;
use Authentication\Authenticator\Result;
use Authentication\Identity;
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
    protected array $fixtures = [
        'app.Users',
        'app.Articles',
        'app.Tags',
        'app.ArticlesTags',
        'app.BlockedIps',
        'app.UserAccountConfirmations',
    ];

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

    public function testEmailConfirmationProcess(): void
    {
        $this->disableErrorHandlerMiddleware();
        // Test sending confirmation email
        $this->enableCsrfToken();
        $this->post('/users/register', [
            'email' => 'newuser@example.com',
            'password' => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ]);
        $this->assertResponseSuccess();

        // Fetch the confirmation code from the database
        $confirmationsTable = $this->getTableLocator()->get('UserAccountConfirmations');
        $confirmation = $confirmationsTable->find()->where(['user_id' => $this->Users->find()->order(['id' => 'DESC'])->first()->id])->first();
        $confirmationCode = $confirmation->confirmation_code;

        // Test confirming email with valid confirmation code
        $this->get("/users/confirm-email/{$confirmationCode}");
        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
        $this->assertFlashMessage('Your account has been confirmed. You can now log in.');

        // Test confirming email with invalid confirmation code
        $this->get('/users/confirm-email/invalidcode');
        $this->assertRedirect(['controller' => 'Users', 'action' => 'register']);
        $this->assertFlashMessage('Invalid confirmation code. Please contact support.');

        // Test confirming email with already used confirmation code
        $this->get("/users/confirm-email/{$confirmationCode}");
        $this->assertRedirect(['controller' => 'Users', 'action' => 'register']);
        $this->assertFlashMessage('Invalid confirmation code. Please contact support.');
    }

    public function testRegistrationWithInvalidData(): void
    {
        $this->enableCsrfToken();

        // Test registration with mismatched passwords
        $this->post('/users/register', [
            'email' => 'mismatch@example.com',
            'password' => 'password123',
            'confirm_password' => 'password456',
        ]);
        $this->assertResponseCode(403);
        $this->assertFlashMessage('Registration failed. Please, try again.');

        // Test registration with an existing email
        $this->post('/users/register', [
            'email' => 'user@example.com', // Assuming this email already exists in fixtures
            'password' => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ]);
        $this->assertResponseCode(403);
        $this->assertFlashMessage('Registration failed. Please, try again.');
    }

    public function testUserEditFunctionality(): void
    {
        $this->enableCsrfToken();
        //non admin user
        $userId = '6509480c-e7e6-4e65-9c38-1423a8d09d02';

        // Create a mock identity
        $identity = new Identity([
            'id' => $userId,
            'username' => 'user@example.com',
            // Add other necessary user data here
        ]);

        // Set the identity in the test
        $this->session(['Auth' => $identity]);

        // Mock the Authentication component
        $authenticationService = $this->createMock(AuthenticationService::class);
        $authenticationService->method('getIdentity')->willReturn($identity);
        $authenticationService->method('getResult')->willReturn(new Result($identity, Result::SUCCESS));

        $this->_controller = $this->getMockBuilder('App\Controller\UsersController')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_controller->Authentication = $authenticationService;

        $data = [
            'username' => 'updatedusername',
            'email' => 'updated@example.com',
        ];
        // Test editing user's own account
        $this->post("/users/edit/{$userId}", $data);

        $this->assertResponseSuccess();
        $this->assertFlashMessage('Your account has been updated.');

        $user = $this->Users->get($userId);
        $this->assertEquals('updated@example.com', $user->email);
        //todo fix this test
        /*
        // Test attempting to edit another user's account
        // admin user ID
        $anotherUserId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->post("/users/edit/{$anotherUserId}", [
            'email' => 'hacked@example.com',
            'username' => 'updatedusername',
        ]);
        //$this->assertResponseCode(302);
        //$this->assertRedirectContains("/users/edit/{$userId}");
        $this->assertFlashMessage('You are not authorized to edit this account, stick to your own.');
        $anotherUser = $this->Users->get($anotherUserId);
        $this->assertNotEquals('hacked@example.com', $anotherUser->email);
*/
        // Test attempting to change admin status
        $this->post("/users/edit/{$userId}", [
            'is_admin' => true,
        ]);
        $this->assertResponseCode(302);
        $this->assertRedirectContains("/users/edit/{$userId}");
        $user = $this->Users->get($userId);
        $this->assertFalse($user->is_admin);

        // Test submitting invalid data
        $this->post("/users/edit/{$userId}", [
            'email' => 'invalid-email',
        ]);
        $this->assertResponseCode(403);
        $this->assertNoRedirect();
        $this->assertFlashMessage('The user could not be saved. Please, try again.');
    }

    public function testFindAuthMethod(): void
    {
        $result = $this->Users->find('auth')->toArray();
        $this->assertNotEmpty($result);
        foreach ($result as $user) {
            $this->assertEquals(0, $user->is_disabled);
        }
    }

    public function testPasswordValidation(): void
    {
        $this->enableCsrfToken();

        // Test password too short
        $this->post('/users/register', [
            'email' => 'short@example.com',
            'password' => 'short',
            'confirm_password' => 'short',
        ]);
        $this->assertResponseCode(403);
    }

    public function testUserDisabling(): void
    {
        $this->enableCsrfToken();
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $userId = '6509480c-e7e6-4e65-9c38-1423a8d09d02';
        $this->session(['Auth.User.id' => $adminId]);

        // Test disabling a user account
        $this->post("/users/edit/{$userId}", [
            'username' => 'user@example.com',
            'email' => 'user@example.com',
            'is_disabled' => true,
        ]);
        $this->assertResponseSuccess();
        $user = $this->Users->get($userId);
        //todo fix this test
        //$this->assertTrue($user->is_disabled);

        // Test logging in with a disabled account
        $this->post('/users/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        //todo fix this test
        //$this->assertNoRedirect();
        //$this->assertNull($this->_controller->Authentication->getIdentity());
    }
}
