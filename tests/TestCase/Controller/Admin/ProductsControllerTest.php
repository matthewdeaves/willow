<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\Admin\ProductsController Test Case
 * 
 * Tests the admin functionality of the ProductsController
 * Includes tests for pending review route and bulk operations
 */
class ProductsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Products',
        'app.Users',
        'app.Tags',
        'app.Articles',
    ];

    /**
     * Test subject
     *
     * @var \App\Model\Table\ProductsTable
     */
    protected $Products;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        
        $this->Products = TableRegistry::getTableLocator()->get('Products');
        
        // Configure request environment
        $this->configRequest([
            'environment' => ['REQUEST_METHOD' => 'GET'],
        ]);
        
        // Login as admin for admin routes
        $this->loginAsAdmin();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Products);
        parent::tearDown();
    }

    /**
     * Helper method to login as admin user
     *
     * @return void
     */
    protected function loginAsAdmin(): void
    {
        $this->session([
            'Auth' => [
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
                'email' => 'admin@example.com',
                'username' => 'admin@example.com',
                'is_admin' => 1,
                'active' => 1,
            ]
        ]);
    }

    /**
     * Test pendingReview action returns 200 for authenticated admin
     * 
     * @return void
     */
    public function testPendingReviewRoute(): void
    {
        // Create a product with pending status for testing
        $this->Products->save($this->Products->newEntity([
            'id' => 'test-pending-product-001',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d02',
            'title' => 'Pending Review Product',
            'slug' => 'pending-review-product',
            'description' => 'This product is awaiting review',
            'manufacturer' => 'Test Manufacturer',
            'model_number' => 'TEST-001',
            'price' => 99.99,
            'currency' => 'USD',
            'verification_status' => 'pending',
            'is_published' => false,
            'featured' => false,
            'reliability_score' => 0.0,
            'view_count' => 0,
        ]));

        $this->get('/admin/products/pending-review');

        $this->assertResponseOk();
        $this->assertResponseContains('Pending Review Product');
        
        // Verify that view variables are set correctly
        $viewVars = $this->_controller->viewBuilder()->getVars();
        $this->assertArrayHasKey('products', $viewVars);
        $this->assertArrayHasKey('usersList', $viewVars);
    }

    /**
     * Test bulkApprove action updates verification_status to approved
     *
     * @return void
     */
    public function testBulkApprove(): void
    {
        // Create test products with pending status - only include required fields
        $entity1 = $this->Products->newEntity([
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d02',
            'title' => 'Bulk Test Product 1',
            'slug' => 'bulk-test-product-1',
            'verification_status' => 'pending',
            'is_published' => false,
            'featured' => false,
        ]);
        $product1 = $this->Products->save($entity1);
        $this->assertNotFalse($product1, 'Failed to save product1: ' . print_r($entity1->getErrors(), true));

        $entity2 = $this->Products->newEntity([
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d02',
            'title' => 'Bulk Test Product 2',
            'slug' => 'bulk-test-product-2',
            'verification_status' => 'pending',
            'is_published' => false,
            'featured' => false,
        ]);
        $product2 = $this->Products->save($entity2);
        $this->assertNotFalse($product2, 'Failed to save product2: ' . print_r($entity2->getErrors(), true));

        // Perform bulk approve (disable CSRF for test)
        $this->disableCsrfToken();
        $this->post('/admin/products/bulk-approve', [
            'ids' => [$product1->id, $product2->id]
        ]);

        $this->assertRedirect('/admin/products/pending-review');
        $this->assertFlashMessage('Successfully approved 2 product(s).', 'flash');

        // Verify products are approved
        $updatedProduct1 = $this->Products->get($product1->id);
        $updatedProduct2 = $this->Products->get($product2->id);
        
        $this->assertEquals('approved', $updatedProduct1->verification_status);
        $this->assertEquals('approved', $updatedProduct2->verification_status);
    }

    /**
     * Test bulkReject action updates verification_status to rejected
     *
     * @return void
     */
    public function testBulkReject(): void
    {
        // Create test products with pending status - simplified required fields only
        $entity1 = $this->Products->newEntity([
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d02',
            'title' => 'Reject Test Product 1',
            'slug' => 'reject-test-product-1',
            'verification_status' => 'pending',
            'is_published' => false,
            'featured' => false,
        ]);
        $product1 = $this->Products->save($entity1);
        $this->assertNotFalse($product1, 'Failed to save product1: ' . print_r($entity1->getErrors(), true));

        $entity2 = $this->Products->newEntity([
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d02',
            'title' => 'Reject Test Product 2',
            'slug' => 'reject-test-product-2',
            'verification_status' => 'pending',
            'is_published' => false,
            'featured' => false,
        ]);
        $product2 = $this->Products->save($entity2);
        $this->assertNotFalse($product2, 'Failed to save product2: ' . print_r($entity2->getErrors(), true));

        // Perform bulk reject (disable CSRF for test)
        $this->disableCsrfToken();
        $this->post('/admin/products/bulk-reject', [
            'ids' => [$product1->id, $product2->id]
        ]);

        $this->assertRedirect('/admin/products/pending-review');
        $this->assertFlashMessage('Successfully rejected 2 product(s).', 'flash');

        // Verify products are rejected
        $updatedProduct1 = $this->Products->get($product1->id);
        $updatedProduct2 = $this->Products->get($product2->id);
        
        $this->assertEquals('rejected', $updatedProduct1->verification_status);
        $this->assertEquals('rejected', $updatedProduct2->verification_status);
    }

    /**
     * Test bulkVerify action triggers log entries for queueJob logs
     *
     * @return void
     */
    public function testBulkVerify(): void
    {
        // Create test products with pending status - simplified required fields only
        $entity1 = $this->Products->newEntity([
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d02',
            'title' => 'Verify Test Product 1',
            'slug' => 'verify-test-product-1',
            'verification_status' => 'pending',
            'is_published' => false,
            'featured' => false,
        ]);
        $product1 = $this->Products->save($entity1);
        $this->assertNotFalse($product1, 'Failed to save product1: ' . print_r($entity1->getErrors(), true));

        $entity2 = $this->Products->newEntity([
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d02',
            'title' => 'Verify Test Product 2',
            'slug' => 'verify-test-product-2',
            'verification_status' => 'pending',
            'is_published' => false,
            'featured' => false,
        ]);
        $product2 = $this->Products->save($entity2);
        $this->assertNotFalse($product2, 'Failed to save product2: ' . print_r($entity2->getErrors(), true));

        // Enable logging to capture job queue logs
        \Cake\Log\Log::setConfig('test', [
            'className' => 'Array',
            'levels' => ['info', 'debug', 'warning', 'error']
        ]);

        // Perform bulk verify (disable CSRF for test)
        $this->disableCsrfToken();
        $this->post('/admin/products/bulk-verify', [
            'ids' => [$product1->id, $product2->id]
        ]);

        $this->assertRedirect('/admin/products/pending-review');
        $this->assertFlashMessage('Verification has been queued for 2 product(s).', 'flash');

        // Check that job queue logs were created
        $logs = \Cake\Log\Log::engine('test')->read();
        
        // Should have log entries for each product verification job
        $jobLogs = array_filter($logs, function($log) {
            return strpos($log, 'Would queue job ProductVerificationJob') !== false;
        });
        
        $this->assertCount(2, $jobLogs, 'Should have 2 job queue log entries');
        
        // Verify log content contains product IDs
        $logContent = implode(' ', $jobLogs);
        $this->assertStringContainsString($product1->id, $logContent);
        $this->assertStringContainsString($product2->id, $logContent);
    }

    /**
     * Test bulk operations require POST method
     *
     * @return void
     */
    public function testBulkOperationsRequirePost(): void
    {
        $this->get('/admin/products/bulk-approve');
        $this->assertResponseCode(405); // Method Not Allowed

        $this->get('/admin/products/bulk-reject');
        $this->assertResponseCode(405); // Method Not Allowed

        $this->get('/admin/products/bulk-verify');
        $this->assertResponseCode(405); // Method Not Allowed
    }

    /**
     * Test bulk operations with empty IDs array
     *
     * @return void
     */
    public function testBulkOperationsWithEmptyIds(): void
    {
        // Disable CSRF for these tests
        $this->disableCsrfToken();
        
        // Test bulk approve with empty IDs
        $this->post('/admin/products/bulk-approve', ['ids' => []]);
        $this->assertRedirect('/admin/products/pending-review');
        $this->assertFlashMessage('Please select products to approve.', 'flash');

        // Test bulk reject with empty IDs
        $this->post('/admin/products/bulk-reject', ['ids' => []]);
        $this->assertRedirect('/admin/products/pending-review');
        $this->assertFlashMessage('Please select products to reject.', 'flash');

        // Test bulk verify with empty IDs
        $this->post('/admin/products/bulk-verify', ['ids' => []]);
        $this->assertRedirect('/admin/products/pending-review');
        $this->assertFlashMessage('Please select products to verify.', 'flash');
    }

    /**
     * Test bulk operations with invalid IDs
     *
     * @return void
     */
    public function testBulkOperationsWithInvalidIds(): void
    {
        // Test with empty strings and invalid data
        $invalidIds = ['', '  ', null, false];
        
        // Disable CSRF for these tests
        $this->disableCsrfToken();

        // Test bulk approve with invalid IDs
        $this->post('/admin/products/bulk-approve', ['ids' => $invalidIds]);
        $this->assertRedirect('/admin/products/pending-review');
        $this->assertFlashMessage('Invalid product IDs provided.', 'flash');

        // Test bulk reject with invalid IDs  
        $this->post('/admin/products/bulk-reject', ['ids' => $invalidIds]);
        $this->assertRedirect('/admin/products/pending-review');
        $this->assertFlashMessage('Invalid product IDs provided.', 'flash');

        // Test bulk verify with invalid IDs
        $this->post('/admin/products/bulk-verify', ['ids' => $invalidIds]);
        $this->assertRedirect('/admin/products/pending-review');
        $this->assertFlashMessage('Invalid product IDs provided.', 'flash');
    }

    /**
     * Test pendingReview with search functionality
     *
     * @return void
     */
    public function testPendingReviewWithSearch(): void
    {
        // Create a searchable pending product
        $this->Products->save($this->Products->newEntity([
            'id' => 'searchable-pending-product',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d02',
            'title' => 'Special Searchable Product',
            'slug' => 'special-searchable-product',
            'description' => 'This product has unique searchable content',
            'manufacturer' => 'Unique Manufacturer',
            'model_number' => 'UNIQUE-001',
            'price' => 199.99,
            'currency' => 'USD',
            'verification_status' => 'pending',
            'is_published' => false,
            'featured' => false,
            'reliability_score' => 0.0,
            'view_count' => 0,
        ]));

        // Test search by title
        $this->get('/admin/products/pending-review?search=Searchable');
        $this->assertResponseOk();
        $this->assertResponseContains('Special Searchable Product');

        // Test search by manufacturer
        $this->get('/admin/products/pending-review?search=Unique');
        $this->assertResponseOk();
        $this->assertResponseContains('Unique Manufacturer');
    }

    /**
     * Test pendingReview with filters
     *
     * @return void
     */
    public function testPendingReviewWithFilters(): void
    {
        $userId = '6509480c-e7e6-4e65-9c38-1423a8d09d02';
        
        // Create a filterable pending product
        $this->Products->save($this->Products->newEntity([
            'id' => 'filterable-pending-product',
            'user_id' => $userId,
            'title' => 'Filterable Product',
            'slug' => 'filterable-product',
            'description' => 'This product can be filtered',
            'manufacturer' => 'Filterable Manufacturer',
            'model_number' => 'FILTER-001',
            'price' => 149.99,
            'currency' => 'USD',
            'verification_status' => 'pending',
            'is_published' => false,
            'featured' => true,
            'reliability_score' => 0.0,
            'view_count' => 0,
        ]));

        // Test filter by user
        $this->get('/admin/products/pending-review?user_id=' . $userId);
        $this->assertResponseOk();
        $this->assertResponseContains('Filterable Product');

        // Test filter by featured
        $this->get('/admin/products/pending-review?featured=1');
        $this->assertResponseOk();
        $this->assertResponseContains('Filterable Product');

        // Test filter by manufacturer
        $this->get('/admin/products/pending-review?manufacturer=Filterable');
        $this->assertResponseOk();
        $this->assertResponseContains('Filterable Manufacturer');
    }
}
