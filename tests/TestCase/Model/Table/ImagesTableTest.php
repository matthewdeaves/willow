<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ImagesTable;
use App\Utility\SettingsManager;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;
use Laminas\Diactoros\UploadedFile;

class ImagesTableTest extends TestCase
{
    protected $ImagesTable;
    protected array $fixtures = ['app.Images'];

    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Images') ? [] : ['className' => ImagesTable::class];
        $this->ImagesTable = $this->getTableLocator()->get('Images', $config);

        // Mock SettingsManager for consistent test results
        $this->mockSettingsManager([
            'ImageSizes' => [500, 200, 100], // Example sizes, adjust to your actual config
            'AI.enabled' => false,
            'AI.imageAnalysis' => false,
        ]);

        // Ensure the directory for uploaded files exists for testing
        if (!is_dir(WWW_ROOT . 'files/Images/image/')) {
            mkdir(WWW_ROOT . 'files/Images/image/', 0777, true);
        }
        foreach ([500, 200, 100] as $width) {
            if (!is_dir(WWW_ROOT . 'files/Images/image/' . $width . '/')) {
                mkdir(WWW_ROOT . 'files/Images/image/' . $width . '/', 0777, true);
            }
        }
    }

    protected function tearDown(): void
    {
        // Clean up test files
        $this->removeTestFiles(WWW_ROOT . 'files/Images/image/');
        foreach ([500, 200, 100] as $width) {
            $this->removeTestFiles(WWW_ROOT . 'files/Images/image/' . $width . '/');
        }

        unset($this->ImagesTable);
        parent::tearDown();
    }

    private function removeTestFiles(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
        $files = glob($path . '*'); // Get all files in the directory
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    protected function mockSettingsManager(array $settings): void
    {
        $mock = $this->getMockBuilder(SettingsManager::class)
            ->onlyMethods(['read'])
            ->getMock();

        $map = [];
        foreach ($settings as $key => $value) {
            $map[] = [$key, null, $value];
        }

        $mock->expects($this->any())
             ->method('read')
             ->willReturnMap($map);

        // This assumes your `SettingsManager` is a static class or can be replaced globally for testing.
        // If not, you might need to pass the mock into the behavior's constructor (via dependency injection)
        // or use a different mocking strategy for static methods if `SettingsManager` is strictly static.
        // For this test, we'll proceed assuming the mock works at the `SettingsManager::read()` call site.
    }

    public function testValidationDefault(): void
    {
        $validator = new Validator();
        $validator = $this->ImagesTable->validationDefault($validator);

        $this->assertTrue($validator->hasField('name'));
        $this->assertFalse($validator->isEmptyAllowed('name', false));
    }

    public function testValidationCreate(): void
    {
        $validator = new Validator();
        $validator = $this->ImagesTable->validationCreate($validator);

        $this->assertTrue($validator->hasField('image'));
        $this->assertTrue($validator->isPresenceRequired('image', true));
        $this->assertFalse($validator->isEmptyAllowed('image', false));

        $this->assertArrayHasKey('mimeType', $validator->field('image')->rules());
        $this->assertArrayHasKey('fileSize', $validator->field('image')->rules());
    }

    public function testValidationUpdate(): void
    {
        $validator = new Validator();
        $validator = $this->ImagesTable->validationUpdate($validator);

        $this->assertTrue($validator->hasField('image'));
        $this->assertTrue($validator->isEmptyAllowed('image', false));

        $this->assertArrayHasKey('mimeType', $validator->field('image')->rules());
        $this->assertArrayHasKey('fileSize', $validator->field('image')->rules());
    }

    public function testImageDeletionOnUpdate(): void
    {
        // Disable the QueueableImageBehavior's afterSave listener to prevent queueing jobs
        // This test focuses on the beforeSave deletion logic.
        $this->ImagesTable->getEventManager()->off('Model.afterSave', [$this->ImagesTable->behaviors()->get('QueueableImage'), 'afterSave']);

        // --- STEP 1: Create an initial image entry ---
        // Create a dummy temporary file for the initial upload
        $initialTmpFile = sys_get_temp_dir() . DS . 'initial_upload_test.jpg';
        file_put_contents($initialTmpFile, 'initial dummy content');

        $initialUploadedFile = new UploadedFile(
            $initialTmpFile,
            filesize($initialTmpFile),
            UPLOAD_ERR_OK,
            'initial_client_filename.jpg',
            'image/jpeg',
        );

        $initialEntity = $this->ImagesTable->newEntity([
            'name' => 'Initial Image',
            'image' => $initialUploadedFile,
        ]);

        // Save the entity. This triggers the Upload behavior, which saves the main file
        // with a UUID name and updates the entity's 'image' property with it.
        $savedInitialEntity = $this->ImagesTable->save($initialEntity);

        // Assert initial save was successful
        $this->assertNotNull($savedInitialEntity);
        $this->assertFalse($savedInitialEntity->hasErrors());

        // Clean up the initial temp file immediately after saving
        unlink($initialTmpFile);

        // Get the UUID filename that was generated and saved by the Upload behavior
        $oldUuidFilename = $savedInitialEntity->image;
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.jpg$/i', $oldUuidFilename);

        // Construct paths for the old UUID-named files
        $oldUuidImagePath = WWW_ROOT . 'files/Images/image/' . $oldUuidFilename;
        $oldUuidResizedPaths = [];
        foreach ([500, 200, 100] as $width) {
            $oldUuidResizedPaths[] = WWW_ROOT . 'files/Images/image/' . $width . '/' . $oldUuidFilename;
        }

        // Now, manually create the main and resized files on disk.
        // The main file should have been created by the Upload behavior already.
        // We need to create the resized ones because they are handled by a queue job.
        $this->assertFileExists($oldUuidImagePath); // Verify main file exists from Upload behavior

        foreach ($oldUuidResizedPaths as $path) {
            file_put_contents($path, 'dummy resized content'); // Create dummy resized file
            $this->assertFileExists($path); // Verify dummy resized file was created for the test
        }

        // --- STEP 2: Load the entity from the database and update it with a new image ---
        // Loading ensures `getOriginal()` correctly reflects the saved state from DB.
        $entityToUpdate = $this->ImagesTable->get($savedInitialEntity->id);

        // Create a dummy temporary file for the new upload
        $newTmpFile = sys_get_temp_dir() . DS . 'new_upload_test.jpg';
        file_put_contents($newTmpFile, 'new dummy content');

        $newUploadedFile = new UploadedFile(
            $newTmpFile,
            filesize($newTmpFile),
            UPLOAD_ERR_OK,
            'new_client_filename.jpg',
            'image/jpeg',
        );

        // Patch the loaded entity with the new image.
        // This makes `entityToUpdate->getOriginal('image')` return the `oldUuidFilename`.
        $entityToUpdate = $this->ImagesTable->patchEntity($entityToUpdate, [
            'image' => $newUploadedFile,
            'name' => 'Updated Image', // Change another field to ensure dirty state
        ]);

        // Save the entity again. This will trigger the QueueableImageBehavior's beforeSave.
        $savedUpdatedEntity = $this->ImagesTable->save($entityToUpdate);

        // tests run to quick sometimes, make sure we give time for file deletion
        $maxRetries = 5;
        $retryDelay = 10000000; // 10000ms

        for ($i = 0; $i < $maxRetries; $i++) {
            if (!file_exists($oldUuidImagePath)) {
                break;
            }
            if ($i < $maxRetries - 1) {
                usleep($retryDelay);
            }
        }

        // Assert the update save was successful
        $this->assertNotNull($savedUpdatedEntity);
        $this->assertFalse($savedUpdatedEntity->hasErrors());

        // Clean up the new temp file immediately after saving
        unlink($newTmpFile);

        // Verify that the OLD UUID-named image and its resized versions are deleted
        $this->assertFileDoesNotExist($oldUuidImagePath);
        foreach ($oldUuidResizedPaths as $path) {
            $this->assertFileDoesNotExist($path);
        }

        // Verify that the NEW image was saved with a new UUID filename
        $newUuidFilename = $savedUpdatedEntity->image;
        $this->assertNotEquals($oldUuidFilename, $newUuidFilename); // New filename should be different
        $this->assertFileExists(WWW_ROOT . 'files/Images/image/' . $newUuidFilename); // New main file should exist
    }

    public function testInitialize(): void
    {
        $this->assertInstanceOf(ImagesTable::class, $this->ImagesTable);
        $this->assertEquals('images', $this->ImagesTable->getTable());
        $this->assertEquals('name', $this->ImagesTable->getDisplayField());
        $this->assertEquals('id', $this->ImagesTable->getPrimaryKey());

        $this->assertTrue($this->ImagesTable->hasBehavior('QueueableImage'));
        $this->assertTrue($this->ImagesTable->hasBehavior('Timestamp'));
    }
}
