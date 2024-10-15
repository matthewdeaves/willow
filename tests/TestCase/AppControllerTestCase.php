<?php
declare(strict_types=1);

namespace App\Test\TestCase;

use Authentication\AuthenticationService;
use Authentication\Authenticator\Result;
use Authentication\Identity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * AppControllerTestCase Class
 *
 * This class extends the CakePHP TestCase and provides common functionality
 * for controller tests, including methods for simulating user authentication.
 */
class AppControllerTestCase extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Simulates a user login for testing purposes.
     *
     * This method can either set up a simple session-based login or a full
     * authentication simulation, depending on the $fullAuth parameter.
     *
     * @param string $userId The ID of the user to log in.
     * @param bool $fullAuth Whether to perform a full authentication simulation.
     *                       If false, only sets up a session-based login.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException If the user is not found.
     */
    protected function loginUser(string $userId, bool $fullAuth = false): void
    {
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $user = $usersTable->get($userId);

        if (!$fullAuth) {
            $this->session(['Auth' => $user]);
        } else {
            $identity = new Identity($user->toArray());
            $result = new Result($identity, Result::SUCCESS);

            $this->session(['Auth' => $identity]);

            $authenticationService = $this->createMock(AuthenticationService::class);
            $authenticationService->method('getIdentity')->willReturn($identity);
            $authenticationService->method('getResult')->willReturn($result);

            $this->_controller = $this->getMockBuilder('App\Controller\Admin\UsersController')
                ->disableOriginalConstructor()
                ->getMock();
            $this->_controller->Authentication = $authenticationService;
        }
    }
}
