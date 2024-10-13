<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use Authentication\AuthenticationService;
use Authentication\Authenticator\Result;
use Authentication\Identity;
use Cake\Cache\Cache;
use Cake\Datasource\FactoryLocator;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Admin\SlugsController Test Case
 *
 * @uses \App\Controller\Admin\SlugsController
 */
class SlugsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Slugs',
        'app.Articles',
        'app.Users',
    ];

    /**
     * @var \Cake\ORM\Table
     */
    protected $Slugs;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Slugs = FactoryLocator::get('Table')->get('Slugs');
        $this->disableErrorHandlerMiddleware();
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

        $this->_controller = $this->getMockBuilder('App\Controller\Admin\SlugsController')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_controller->Authentication = $authenticationService;
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f'; // Assuming this is an admin user ID
        $this->setupAuthentication($adminId);

        $this->get('/admin/slugs');
        $this->assertResponseOk();
        $this->assertResponseContains('Slugs');
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->setupAuthentication($adminId);

        $slug = $this->Slugs->find()->first();
        $this->get('/admin/slugs/view/' . $slug->id);
        $this->assertResponseOk();
        $this->assertResponseContains($slug->slug);
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->setupAuthentication($adminId);

        $this->enableCsrfToken();
        $this->post('/admin/slugs/add', [
            'article_id' => 'hi1238e7-g606-990h-44ii-ij48k1j2jjf6',
            'slug' => 'new-test-slug',
        ]);

        $this->assertRedirect(['action' => 'index']);
        $slug = $this->Slugs->find()->where(['slug' => 'new-test-slug'])->first();
        $this->assertNotNull($slug);
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->setupAuthentication($adminId);

        $slug = $this->Slugs->find()->first();
        $this->enableCsrfToken();
        $this->post('/admin/slugs/edit/' . $slug->id, [
            'slug' => 'updated-test-slug',
        ]);

        $this->assertRedirect(['action' => 'index']);
        $updatedSlug = $this->Slugs->get($slug->id);
        $this->assertEquals('updated-test-slug', $updatedSlug->slug);
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->setupAuthentication($adminId);

        $slug = $this->Slugs->find()->first();
        $this->enableCsrfToken();
        $this->post('/admin/slugs/delete/' . $slug->id);

        $this->assertRedirect(['action' => 'index']);
        $this->assertNull($this->Slugs->find()->where(['id' => $slug->id])->first());
    }

    /**
     * Test access control
     *
     * @return void
     */
    public function testAccessControl(): void
    {
        // Test access without authentication
        $this->get('/admin/slugs');
        $this->assertRedirectContains('/users/login');

        // Test access with non-admin user
        $nonAdminId = '6509480c-e7e6-4e65-9c38-1423a8d09d02'; // Assuming this is a non-admin user ID
        $this->setupAuthentication($nonAdminId);
        $this->get('/admin/slugs');
        $this->assertResponseCode(403); // Forbidden
    }

    /**
     * Test search functionality
     *
     * @return void
     */
    public function testSearch(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->setupAuthentication($adminId);

        $this->enableCsrfToken();
        $this->get('/admin/slugs?search=article-one');
        $this->assertResponseOk();
        $this->assertResponseContains('article-one');
    }

    /**
     * Test pagination
     *
     * @return void
     */
    public function testPagination(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->setupAuthentication($adminId);

        $this->get('/admin/slugs?page=1');
        $this->assertResponseOk();
        // Add assertions to check pagination elements
    }
}