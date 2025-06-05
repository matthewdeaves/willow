<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use App\Test\TestCase\AppControllerTestCase;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\TestSuite\IntegrationTestTrait;

/**
 * App\Controller\Admin\ImageGalleriesController Test Case
 *
 * @uses \App\Controller\Admin\ImageGalleriesController
 */
class ImageGalleriesControllerTest extends AppControllerTestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.ImageGalleries',
        'app.Images',
        'app.ImageGalleriesImages',
        'app.Slugs',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Disable error handler middleware for cleaner error messages
        $this->disableErrorHandlerMiddleware();

        // Enable CSRF token for testing
        $this->enableCsrfToken();

        // Login as admin user using the proper method from AppControllerTestCase
        // Using admin user ID from Users fixture
        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d0f');
    }

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::index()
     */
    public function testIndex(): void
    {
        $this->get('/admin/image-galleries');
        $this->assertResponseOk();
        $this->assertResponseContains('Image Galleries');

        // Test that galleries are loaded
        $this->assertResponseContains('Lorem ipsum dolor sit amet'); // From fixture
    }

    /**
     * Test index method with grid view
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::index()
     */
    public function testIndexGridView(): void
    {
        $this->get('/admin/image-galleries?view=grid');
        $this->assertResponseOk();

        // Should render index_grid template
        $this->assertTemplate('index_grid');
    }

    /**
     * Test index method with list view
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::index()
     */
    public function testIndexListView(): void
    {
        $this->get('/admin/image-galleries?view=list');
        $this->assertResponseOk();

        // Should render index template (list view)
        $this->assertTemplate('index');
    }

    /**
     * Test index method view type switching
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::index()
     */
    public function testIndexViewTypePersistence(): void
    {
        // Test list view
        $this->get('/admin/image-galleries?view=list');
        $this->assertResponseOk();
        $this->assertTemplate('index'); // List view uses index template

        // Test grid view
        $this->get('/admin/image-galleries?view=grid');
        $this->assertResponseOk();
    }

    /**
     * Test index method with search functionality
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::index()
     */
    public function testIndexSearch(): void
    {
        $this->get('/admin/image-galleries?search=Lorem');
        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum dolor sit amet');

        // Test search with no results
        $this->get('/admin/image-galleries?search=NonExistentGallery');
        $this->assertResponseOk();
        $this->assertResponseNotContains('Lorem ipsum dolor sit amet');
    }

    /**
     * Test index method with status filtering
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::index()
     */
    public function testIndexStatusFilter(): void
    {
        // Test published filter
        $this->get('/admin/image-galleries?status=1');
        $this->assertResponseOk();

        // Test unpublished filter
        $this->get('/admin/image-galleries?status=0');
        $this->assertResponseOk();
    }

    /**
     * Test index method with AJAX request
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::index()
     */
    public function testIndexAjax(): void
    {
        $this->configRequest([
            'headers' => ['X-Requested-With' => 'XMLHttpRequest'],
        ]);

        $this->get('/admin/image-galleries?search=Lorem');
        $this->assertResponseOk();
        $this->assertTemplate('search_results');
        $this->assertLayout('ajax');
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::view()
     */
    public function testView(): void
    {
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture

        $this->get("/admin/image-galleries/view/{$galleryId}");
        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum dolor sit amet');
    }

    /**
     * Test view method with invalid gallery ID
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::view()
     */
    public function testViewNotFound(): void
    {
        $this->expectException(RecordNotFoundException::class);

        $this->get('/admin/image-galleries/view/00000000-0000-0000-0000-000000000000');
    }

    /**
     * Test add method GET request
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::add()
     */
    public function testAddGet(): void
    {
        $this->get('/admin/image-galleries/add');
        $this->assertResponseOk();
        $this->assertResponseContains('Add Image Gallery');
    }

    /**
     * Test add method POST request with valid data
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::add()
     */
    public function testAddPostValid(): void
    {
        $data = [
            'name' => 'Test Gallery',
            'description' => 'A test gallery description',
            'is_published' => true,
        ];

        $this->post('/admin/image-galleries/add', $data);
        $this->assertResponseSuccess();
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The image gallery has been saved.');

        // Verify gallery was created
        $galleries = $this->getTableLocator()->get('ImageGalleries');
        $gallery = $galleries->find()->where(['name' => 'Test Gallery'])->first();
        $this->assertNotNull($gallery);
        $this->assertEquals('test-gallery', $gallery->slug); // SlugBehavior should create this
    }

    /**
     * Test add method POST request with invalid data
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::add()
     */
    public function testAddPostInvalid(): void
    {
        $data = [
            'name' => '', // Name is required
            'description' => 'A test gallery description',
            'is_published' => true,
        ];

        $this->post('/admin/image-galleries/add', $data);
        $this->assertResponseOk(); // Should render form again with errors
    }

    /**
     * Test add method POST request without file uploads
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::add()
     */
    public function testAddPostWithoutFileUploads(): void
    {
        $data = [
            'name' => 'Gallery Without Files',
            'description' => 'A test gallery without file uploads',
            'is_published' => true,
        ];

        $this->post('/admin/image-galleries/add', $data);
        $this->assertResponseSuccess();
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The image gallery has been saved.');
    }

    /**
     * Test edit method GET request
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::edit()
     */
    public function testEditGet(): void
    {
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture

        $this->get("/admin/image-galleries/edit/{$galleryId}");
        $this->assertResponseOk();
        $this->assertResponseContains('Edit Image Gallery');
        $this->assertResponseContains('Lorem ipsum dolor sit amet'); // Gallery name from fixture
    }

    /**
     * Test edit method GET request with invalid gallery ID
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::edit()
     */
    public function testEditGetNotFound(): void
    {
        $this->expectException(RecordNotFoundException::class);

        $this->get('/admin/image-galleries/edit/00000000-0000-0000-0000-000000000000');
    }

    /**
     * Test edit method POST request with valid data
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::edit()
     */
    public function testEditPostValid(): void
    {
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture
        $data = [
            'name' => 'Updated Gallery Name',
            'description' => 'Updated description',
            'is_published' => false,
        ];

        $this->put("/admin/image-galleries/edit/{$galleryId}", $data);
        $this->assertResponseSuccess();
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The image gallery has been saved.');

        // Verify gallery was updated
        $galleries = $this->getTableLocator()->get('ImageGalleries');
        $gallery = $galleries->get($galleryId);
        $this->assertEquals('Updated Gallery Name', $gallery->name);
        $this->assertEquals('Updated description', $gallery->description);
        $this->assertFalse($gallery->is_published);
    }

    /**
     * Test edit method POST request with invalid data
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::edit()
     */
    public function testEditPostInvalid(): void
    {
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture
        $data = [
            'name' => '', // Name is required
            'description' => 'Updated description',
            'is_published' => false,
        ];

        $this->put("/admin/image-galleries/edit/{$galleryId}", $data);
        $this->assertResponseOk(); // Should render form again with errors
    }

    /**
     * Test delete method with valid gallery
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::delete()
     */
    public function testDeleteSuccess(): void
    {
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture

        // Set referer so we can test redirect
        $this->configRequest([
            'headers' => ['Referer' => '/admin/image-galleries'],
        ]);

        $this->delete("/admin/image-galleries/delete/{$galleryId}");
        $this->assertResponseSuccess();
        $this->assertRedirect('/admin/image-galleries');
        $this->assertFlashMessage('The image gallery has been deleted.');

        // Verify gallery was deleted
        $galleries = $this->getTableLocator()->get('ImageGalleries');
        $exists = $galleries->exists(['id' => $galleryId]);
        $this->assertFalse($exists);
    }

    /**
     * Test delete method with invalid gallery ID
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::delete()
     */
    public function testDeleteNotFound(): void
    {
        $this->expectException(RecordNotFoundException::class);

        $this->delete('/admin/image-galleries/delete/00000000-0000-0000-0000-000000000000');
    }

    /**
     * Test delete method requires POST or DELETE method
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::delete()
     */
    public function testDeleteMethodSecurity(): void
    {
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture

        $this->expectException(MethodNotAllowedException::class);

        $this->get("/admin/image-galleries/delete/{$galleryId}");
    }

    /**
     * Test manageImages method
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::manageImages()
     */
    public function testManageImages(): void
    {
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture

        $this->get("/admin/image-galleries/manageImages/{$galleryId}");
        $this->assertResponseOk();
        $this->assertResponseContains('Manage Images');
    }

    /**
     * Test manageImages method with invalid gallery ID
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::manageImages()
     */
    public function testManageImagesNotFound(): void
    {
        $this->expectException(RecordNotFoundException::class);

        $this->get('/admin/image-galleries/manageImages/00000000-0000-0000-0000-000000000000');
    }
}
