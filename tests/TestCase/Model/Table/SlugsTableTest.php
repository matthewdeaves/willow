<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SlugsTable;
use Cake\TestSuite\TestCase;
use Cake\Utility\Text;

/**
 * App\Model\Table\SlugsTable Test Case
 */
class SlugsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SlugsTable
     */
    protected $Slugs;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Slugs',
        'app.Articles',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Slugs') ? [] : ['className' => SlugsTable::class];
        $this->Slugs = $this->getTableLocator()->get('Slugs', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Slugs);

        parent::tearDown();
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\SlugsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $slug = $this->Slugs->newEntity([
            'article_id' => Text::uuid(),
            'slug' => 'test-slug',
        ]);

        $result = $this->Slugs->save($slug);
        $this->assertFalse($result, 'Save should fail due to non-existent article_id');

        $errors = $slug->getErrors();
        $this->assertArrayHasKey('article_id', $errors);
    }

    /**
     * Test ensureSlugExists method
     *
     * @return void
     * @uses \App\Model\Table\SlugsTable::ensureSlugExists()
     */
    public function testEnsureSlugExists(): void
    {
        $articleId = '224310b4-96ad-4d58-a0a9-af6dc7253c4f';
        $slug = 'test-slug-for-article-six';

        // Ensure the slug doesn't exist initially
        $existingSlug = $this->Slugs->find()
            ->where(['article_id' => $articleId, 'slug' => $slug])
            ->first();
        $this->assertNull($existingSlug);

        // Call ensureSlugExists
        $this->Slugs->ensureSlugExists($articleId, $slug);

        // Check if the slug was created
        $createdSlug = $this->Slugs->find()
            ->where(['article_id' => $articleId, 'slug' => $slug])
            ->first();
        $this->assertNotNull($createdSlug);

        // Call ensureSlugExists again with the same data
        $this->Slugs->ensureSlugExists($articleId, $slug);

        // Check that no duplicate was created
        $slugCount = $this->Slugs->find()
            ->where(['article_id' => $articleId, 'slug' => $slug])
            ->count();
        $this->assertEquals(1, $slugCount);
    }

    /**
     * Test unique constraint on slug and article_id combination
     *
     * @return void
     */
    public function testUniqueSlugArticleIdCombination(): void
    {
        $articleId = '42655115-cb43-4ba5-bae7-292443b9ce21'; // article 3
        $slug = 'unique-test-slug-for-article-3';

        $slug1 = $this->Slugs->newEntity([
            'article_id' => $articleId,
            'slug' => $slug,
        ]);

        $this->Slugs->save($slug1);
        $errors = $slug1->getErrors();
        $this->assertEmpty($errors, 'The $errors array should be empty');

        $slug2 = $this->Slugs->newEntity([
            'article_id' => $articleId,
            'slug' => $slug,
        ]);

        $this->Slugs->save($slug2);
        $errors = $slug2->getErrors();
        $this->assertArrayHasKey('slug', $errors);
    }
}
