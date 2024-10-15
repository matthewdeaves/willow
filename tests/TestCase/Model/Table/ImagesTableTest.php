<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ImagesTable;
use ArrayObject;
use Cake\Event\Event;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;

class ImagesTableTest extends TestCase
{
    protected $ImagesTable;
    protected array $fixtures = ['app.Images'];

    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Images') ? [] : ['className' => ImagesTable::class];
        $this->ImagesTable = $this->getTableLocator()->get('Images', $config);
    }

    protected function tearDown(): void
    {
        unset($this->ImagesTable);
        parent::tearDown();
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

        $this->assertTrue($validator->hasField('image_file'));
        $this->assertTrue($validator->isPresenceRequired('image_file', true));
        $this->assertFalse($validator->isEmptyAllowed('image_file', false));

        $this->assertArrayHasKey('mimeType', $validator->field('image_file')->rules());
        $this->assertArrayHasKey('fileSize', $validator->field('image_file')->rules());
    }

    public function testValidationUpdate(): void
    {
        $validator = new Validator();
        $validator = $this->ImagesTable->validationUpdate($validator);

        $this->assertTrue($validator->hasField('image_file'));
        $this->assertTrue($validator->isEmptyAllowed('image_file', false));

        $this->assertArrayHasKey('mimeType', $validator->field('image_file')->rules());
        $this->assertArrayHasKey('fileSize', $validator->field('image_file')->rules());
    }

    public function testBeforeSave(): void
    {
        // Remove the QueueableImage behavior, dont want to trigger this for now
        $this->ImagesTable->removeBehavior('QueueableImage');

        $oldImagePath = WWW_ROOT . 'files/Images/image_file/old_image.jpg';
        file_put_contents($oldImagePath, 'dummy content');

        $entity = $this->ImagesTable->newEntity([
            'name' => 'Test Image',
            'image_file' => 'old_image.jpg',
            'image_dir' => 'files/Images/image_file/',
            'image_size' => 1024, // Added this line
            'image_type' => 'image/jpeg', // Added this line
        ]);
        $entity = $this->ImagesTable->save($entity);

        $newImageFile = [
            'name' => 'new_image.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => __DIR__ . '/test_image.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        $entity = $this->ImagesTable->patchEntity($entity, ['image_file' => $newImageFile]);
        $entity->setDirty('image_file', true);

        $event = new Event('Model.beforeSave', $this->ImagesTable, ['entity' => $entity]);
        $options = new ArrayObject();

        $result = $this->ImagesTable->beforeSave($event, $entity, $options);

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($oldImagePath);
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
