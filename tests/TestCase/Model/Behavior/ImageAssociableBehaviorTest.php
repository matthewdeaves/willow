<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Behavior;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * ImageAssociableBehavior Test Case
 */
class ImageAssociableBehaviorTest extends TestCase
{
    protected array $fixtures = [
        'app.Articles',
        'app.Images',
        'app.ModelsImages',
        'app.Users',
    ];

    protected Table $Articles;
    protected Table $Images;
    protected Table $ModelsImages;

    public function setUp(): void
    {
        parent::setUp();
        $this->Articles = TableRegistry::getTableLocator()->get('Articles');
        $this->Images = TableRegistry::getTableLocator()->get('Images');
        $this->ModelsImages = TableRegistry::getTableLocator()->get('ModelsImages');

        // Ensure the behavior is attached
        if (!$this->Articles->behaviors()->has('ImageAssociable')) {
            $this->Articles->addBehavior('ImageAssociable');
        }
    }

    public function tearDown(): void
    {
        unset($this->Articles, $this->Images, $this->ModelsImages);
        TableRegistry::getTableLocator()->clear();
        parent::tearDown();
    }

    /**
     * Test behavior initialization sets up belongsToMany association
     */
    public function testInitializationCreatesAssociation(): void
    {
        $this->assertTrue($this->Articles->hasAssociation('Images'));

        $association = $this->Articles->getAssociation('Images');
        $this->assertEquals('manyToMany', $association->type());
        $this->assertEquals('foreign_key', $association->getForeignKey());
        $this->assertEquals('image_id', $association->getTargetForeignKey());
    }

    /**
     * Test association uses correct join table
     */
    public function testAssociationJoinTableConfiguration(): void
    {
        $association = $this->Articles->getAssociation('Images');

        // Get the junction table name
        $junctionTable = $association->junction();
        $this->assertEquals('ModelsImages', $junctionTable->getAlias());
    }

    /**
     * Test unlinkImages removes image associations
     */
    public function testUnlinkImagesRemovesAssociations(): void
    {
        $article = $this->Articles->find()->first();
        $this->assertNotNull($article, 'Article should exist in fixtures');

        $image = $this->Images->find()->first();
        $this->assertNotNull($image, 'Image should exist in fixtures');

        // Create an association between article and image
        $modelImage = $this->ModelsImages->newEntity([
            'model' => 'Articles',
            'foreign_key' => $article->id,
            'image_id' => $image->id,
        ]);
        $savedAssoc = $this->ModelsImages->save($modelImage);
        $this->assertNotFalse($savedAssoc, 'ModelsImages association should be saved');

        // Verify the association exists
        $countBefore = $this->ModelsImages->find()
            ->where([
                'model' => 'Articles',
                'foreign_key' => $article->id,
                'image_id' => $image->id,
            ])
            ->count();
        $this->assertEquals(1, $countBefore, 'Association should exist before unlink');

        // Unlink the image
        $behavior = $this->Articles->behaviors()->get('ImageAssociable');
        $behavior->unlinkImages($article, [$image->id]);

        // Verify the association was removed
        $countAfter = $this->ModelsImages->find()
            ->where([
                'model' => 'Articles',
                'foreign_key' => $article->id,
                'image_id' => $image->id,
            ])
            ->count();
        $this->assertEquals(0, $countAfter, 'Association should be removed after unlink');
    }

    /**
     * Test unlinkImages filters out '0' values
     */
    public function testUnlinkImagesFiltersZeroValues(): void
    {
        $article = $this->Articles->find()->first();
        $this->assertNotNull($article);

        $image = $this->Images->find()->first();
        $this->assertNotNull($image);

        // Create an association
        $modelImage = $this->ModelsImages->newEntity([
            'model' => 'Articles',
            'foreign_key' => $article->id,
            'image_id' => $image->id,
        ]);
        $this->ModelsImages->save($modelImage);

        // Try to unlink with only '0' values - should do nothing
        $behavior = $this->Articles->behaviors()->get('ImageAssociable');
        $behavior->unlinkImages($article, ['0', '0']);

        // Verify the association still exists
        $count = $this->ModelsImages->find()
            ->where([
                'model' => 'Articles',
                'foreign_key' => $article->id,
                'image_id' => $image->id,
            ])
            ->count();
        $this->assertEquals(1, $count, 'Association should still exist when only 0 values passed');
    }

    /**
     * Test unlinkImages handles empty array
     */
    public function testUnlinkImagesWithEmptyArray(): void
    {
        $article = $this->Articles->find()->first();
        $this->assertNotNull($article);

        // Should not throw any errors with empty array
        $behavior = $this->Articles->behaviors()->get('ImageAssociable');
        $behavior->unlinkImages($article, []);

        // Just verify no exception was thrown
        $this->assertTrue(true);
    }

    /**
     * Test unlinkImages handles mixed zero and valid values
     */
    public function testUnlinkImagesMixedValues(): void
    {
        $article = $this->Articles->find()->first();
        $this->assertNotNull($article);

        $images = $this->Images->find()->limit(2)->all()->toArray();
        $this->assertCount(2, $images, 'Should have 2 images in fixtures');

        // Create associations for both images
        foreach ($images as $image) {
            $modelImage = $this->ModelsImages->newEntity([
                'model' => 'Articles',
                'foreign_key' => $article->id,
                'image_id' => $image->id,
            ]);
            $this->ModelsImages->save($modelImage);
        }

        // Unlink with mixed values: one '0' and one valid ID
        $behavior = $this->Articles->behaviors()->get('ImageAssociable');
        $behavior->unlinkImages($article, ['0', $images[0]->id]);

        // First image should be unlinked, second should remain
        $remaining = $this->ModelsImages->find()
            ->where([
                'model' => 'Articles',
                'foreign_key' => $article->id,
            ])
            ->all();

        $this->assertCount(1, $remaining, 'Only one association should remain');
        $this->assertEquals($images[1]->id, $remaining->first()->image_id, 'Second image should still be linked');
    }

    /**
     * Test afterSave triggers unlinkImages when unlinkedImages property set
     */
    public function testAfterSaveUnlinksImages(): void
    {
        $article = $this->Articles->find()->first();
        $this->assertNotNull($article);

        $image = $this->Images->find()->first();
        $this->assertNotNull($image);

        // Create an association
        $modelImage = $this->ModelsImages->newEntity([
            'model' => 'Articles',
            'foreign_key' => $article->id,
            'image_id' => $image->id,
        ]);
        $this->ModelsImages->save($modelImage);

        // Verify association exists
        $countBefore = $this->ModelsImages->find()
            ->where([
                'model' => 'Articles',
                'foreign_key' => $article->id,
                'image_id' => $image->id,
            ])
            ->count();
        $this->assertEquals(1, $countBefore);

        // Set unlinkedImages property and save the article
        $article->unlinkedImages = [$image->id];
        $article->setDirty('modified', true); // Mark as dirty to trigger save
        $this->Articles->save($article);

        // Verify association was removed via afterSave callback
        $countAfter = $this->ModelsImages->find()
            ->where([
                'model' => 'Articles',
                'foreign_key' => $article->id,
                'image_id' => $image->id,
            ])
            ->count();
        $this->assertEquals(0, $countAfter, 'Association should be removed via afterSave');
    }

    /**
     * Test behavior works with different model
     */
    public function testBehaviorWithDifferentModel(): void
    {
        // Get the Tags table and add the behavior
        $tagsTable = TableRegistry::getTableLocator()->get('Tags');
        if (!$tagsTable->behaviors()->has('ImageAssociable')) {
            $tagsTable->addBehavior('ImageAssociable');
        }

        $this->assertTrue($tagsTable->hasAssociation('Images'));

        $association = $tagsTable->getAssociation('Images');
        $this->assertEquals('manyToMany', $association->type());
    }
}
