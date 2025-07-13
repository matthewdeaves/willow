<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\Product Test Case
 */
class ProductTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Entity\Product
     */
    protected $Products;

    protected array $fixtures = ['app.Products', 'app.Users', 'app.Tags', 'app.ProductsTags'];

    /**
     * Test the reorder functionality of the Products model.
     *
     * This method tests three scenarios:
     * 1. Moving a page to the root level.
     * 2. Moving a page to a new parent.
     * 3. Reordering a page within its siblings.
     *
     * @return void
     * @throws \PHPUnit\Framework\AssertionFailedError If any assertion fails.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException If a record is not found.
     * @throws \Cake\ORM\Exception\PersistenceFailedException If reordering fails.
     */
    public function testReorder()
    {
        // Test moving a page to root level
        $data = [
            'id' => 'ce6794a2-b191-445c-99ed-de93f6e7eeb1', // Page Two
            'newParentId' => 'root',
            'newIndex' => 0,
        ];
        $result = $this->Products->reorder($data);
        $this->assertTrue($result, 'Failed to move page to root level');

        $reorderedProduct = $this->Products->get('ce6794a2-b191-445c-99ed-de93f6e7eeb1');
        $this->assertNull($reorderedProduct->parent_id, 'Parent ID should be null for root level');
        $this->assertLessThan($reorderedProduct->rght, $reorderedProduct->lft, 'Left value should be less than right value');

        // Test moving a page to a new parent
        $data = [
            'id' => '5119fb0c-ff60-4c16-9e25-aba3d32d5d5c', // Page Five
            'newParentId' => '630fe0f3-7d68-472f-a1b1-c73ed3fe0c8e', // Page One
            'newIndex' => 0,
        ];
        $result = $this->Products->reorder($data);
        $this->assertTrue($result, 'Failed to move page to a new parent');

        $reorderedProduct = $this->Products->get('5119fb0c-ff60-4c16-9e25-aba3d32d5d5c');
        $this->assertEquals('630fe0f3-7d68-472f-a1b1-c73ed3fe0c8e', $reorderedProduct->parent_id, 'Parent ID should match the new parent');
        $siblings = $this->Products->find('children', for: $reorderedProduct->parent_id, direct: true)->toArray();
        $this->assertEquals(0, array_search($reorderedProduct->id, array_column($siblings, 'id')), 'Reordered page should be the first child');

        // Test reordering within siblings
        $data = [
            'id' => 'de7894a3-c292-556d-00ee-ef04g7f8ffc2', // Page Three
            'newParentId' => '630fe0f3-7d68-472f-a1b1-c73ed3fe0c8e', // Page One
            'newIndex' => 2,
        ];
        $result = $this->Products->reorder($data);
        $this->assertTrue($result, 'Failed to reorder within siblings');

        $reorderedProduct = $this->Products->get('de7894a3-c292-556d-00ee-ef04g7f8ffc2');
        $this->assertEquals('630fe0f3-7d68-472f-a1b1-c73ed3fe0c8e', $reorderedProduct->parent_id, 'Parent ID should remain the same');
        $siblings = $this->Products->find('children', for: $reorderedProduct->parent_id, direct: true)->toArray();

        $this->assertGreaterThan(1, count($siblings), 'There should be more than one child after reordering');
        $this->assertEquals(2, array_search($reorderedProduct->id, array_column($siblings, 'id')), 'Reordered page should be the third child');
    }

    /**
     * Test the slug generation and uniqueness in the beforeSave callback.
     *
     * @return void
     * @throws \PHPUnit\Framework\AssertionFailedError If any assertion fails.
     * @throws \Cake\ORM\Exception\PersistenceFailedException If saving fails.
     */
    public function testSlugGenerationAndUniqueness(): void
    {
        // Test slug generation
        $product = $this->Products->newEntity([
            'title' => 'New Test Product',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f', // Using an existing user ID from fixtures
            'body' => 'This is a new test product.',
            'slug' => '',
            'kind' => 'product',
        ]);
        $this->Products->save($product);

        $this->assertEquals('new-test-product', $product->slug, 'Slug should match the expected format');

        // Test slug uniqueness
        $duplicateProduct = $this->Products->newEntity([
            'title' => 'Product One', // This title already exists in fixtures
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'body' => 'This is another test product with the same title as an existing one.',
            'slug' => 'new-test-product',
            'kind' => 'product',
        ]);
        $result = $this->Products->save($duplicateProduct);
        $this->assertFalse($result, 'Save operation should fail due to duplicate slug');
        $expectedErrors = [
            'slug' => [
                'unique' => 'This slug is already in use.',
            ],
        ];

        $this->assertEquals($expectedErrors, $duplicateProduct->getErrors(), 'Error message for duplicate slug should match expected format');

        // Test slug generation with special characters
        $specialCharProduct = $this->Products->newEntity([
            'title' => 'Test: Product with Special Characters!&',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'body' => 'This is a test product with special characters in the title.',
            'slug' => '',
            'kind' => 'product',
        ]);
        $this->Products->save($specialCharProduct);

        $this->assertNotEmpty($specialCharProduct->slug, 'Slug for product with special characters should not be empty');
        $this->assertEquals('test-product-with-special-characters', $specialCharProduct->slug, 'Slug should be properly formatted without special characters');

        // Test slug generation with very long title
        $longTitleProduct = $this->Products->newEntity([
            'title' => str_repeat('Very Long Title ', 20), // 300 characters
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'body' => 'This is a test product with a very long title.',
            'slug' => '',
            'kind' => 'product',
        ]);
        $result = $this->Products->save($longTitleProduct);

        // Assert that the save operation failed
        $this->assertFalse($result, 'Save operation should fail due to title length');

        // Define the expected error structure
        $expectedErrors = [
            'title' => [
                'maxLength' => 'The provided value must be at most `255` characters long',
            ],
        ];

        // Assert that the errors match the expected structure
        $this->assertEquals($expectedErrors, $longTitleProduct->getErrors(), 'Error message for title max length should match expected format');
    }

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->Products = $this->getTableLocator()->get('Products');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Product);

        parent::tearDown();
    }
}
