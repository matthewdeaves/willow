<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use App\Test\TestCase\AppControllerTestCase;
use Cake\Cache\Cache;
use Cake\Datasource\FactoryLocator;
use Cake\TestSuite\IntegrationTestTrait;

/**
 * App\Controller\Admin\SlugsController Test Case
 *
 * @uses \App\Controller\Admin\SlugsController
 */
class SlugsControllerTest extends AppControllerTestCase
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
        'app.Settings',
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
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f'; // Assuming this is an admin user ID
        $this->loginUser($adminId);

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
        $this->loginUser($adminId);

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
        $this->loginUser($adminId);

        $this->enableCsrfToken();
        $this->post('/admin/slugs/add', [
            'article_id' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
            'slug' => 'new-test-slug',
        ]);

        //$this->assertRedirect(['action' => 'index']);
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
        $this->loginUser($adminId);

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
        $this->loginUser($adminId);

        $slug = $this->Slugs->find()->first();
        $this->enableCsrfToken();
        $this->post('/admin/slugs/delete/' . $slug->id);

        $this->assertRedirect();
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
        $this->assertRedirect('/en');

        // Test access with non-admin user
        $nonAdminId = '6509480c-e7e6-4e65-9c38-1423a8d09d02'; // Assuming this is a non-admin user ID
        $this->loginUser($nonAdminId);
        $this->get('/admin/slugs');
        $this->assertResponseCode(302);
        $this->assertRedirect('/en');

        $adminID = '6509480c-e7e6-4e65-9c38-1423a8d09d0f'; /// admin id
        $this->loginUser($adminID);
        $this->get('/admin/slugs');
        $this->assertResponseOk();
    }

    /**
     * Test search functionality
     *
     * @return void
     */
    public function testSearch(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->loginUser($adminId);

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
        $this->loginUser($adminId);

        $this->get('/admin/slugs?page=1');
        $this->assertResponseOk();
        // Add assertions to check pagination elements
    }
}
