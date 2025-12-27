<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Behavior;

use Cake\ORM\ResultSet;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * CommentableBehavior Test Case
 */
class CommentableBehaviorTest extends TestCase
{
    protected array $fixtures = [
        'app.Articles',
        'app.Comments',
        'app.Users',
    ];

    protected Table $Articles;
    protected Table $Comments;

    public function setUp(): void
    {
        parent::setUp();
        $this->Articles = TableRegistry::getTableLocator()->get('Articles');
        $this->Comments = TableRegistry::getTableLocator()->get('Comments');

        // Ensure the behavior is attached
        if (!$this->Articles->behaviors()->has('Commentable')) {
            $this->Articles->addBehavior('Commentable');
        }
    }

    public function tearDown(): void
    {
        unset($this->Articles, $this->Comments);
        TableRegistry::getTableLocator()->clear();
        parent::tearDown();
    }

    /**
     * Test behavior initialization sets up hasMany association
     */
    public function testInitializationCreatesAssociation(): void
    {
        $this->assertTrue($this->Articles->hasAssociation('Comments'));

        $association = $this->Articles->getAssociation('Comments');
        $this->assertEquals('oneToMany', $association->type());
        $this->assertEquals('foreign_key', $association->getForeignKey());
    }

    /**
     * Test default configuration values
     */
    public function testDefaultConfiguration(): void
    {
        $behavior = $this->Articles->behaviors()->get('Commentable');

        $this->assertEquals('Comments', $behavior->getConfig('commentsTable'));
        $this->assertEquals('foreign_key', $behavior->getConfig('foreignKey'));
        $this->assertEquals('model', $behavior->getConfig('modelField'));
        $this->assertEquals('user_id', $behavior->getConfig('userField'));
        $this->assertEquals('content', $behavior->getConfig('contentField'));
    }

    /**
     * Test addComment creates a comment successfully
     */
    public function testAddCommentSuccess(): void
    {
        // Get an article from fixtures
        $article = $this->Articles->find()->first();
        $this->assertNotNull($article);

        // Use a known user ID from the fixtures
        $userId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f'; // admin user

        $initialCount = $this->Comments->find()
            ->where([
                'foreign_key' => $article->id,
                'model' => 'Articles',
            ])
            ->count();

        // Add a comment
        $result = $this->Articles->addComment(
            $article->id,
            $userId,
            'Test comment content',
        );

        $this->assertNotFalse($result);
        $this->assertEquals('Test comment content', $result->content);
        $this->assertEquals($article->id, $result->foreign_key);
        $this->assertEquals('Articles', $result->model);
        $this->assertEquals($userId, $result->user_id);

        // Verify comment was saved
        $newCount = $this->Comments->find()
            ->where([
                'foreign_key' => $article->id,
                'model' => 'Articles',
            ])
            ->count();

        $this->assertEquals($initialCount + 1, $newCount);
    }

    /**
     * Test getComments retrieves comments for an entity
     */
    public function testGetCommentsRetrievesComments(): void
    {
        // First, create a comment
        $article = $this->Articles->find()->first();
        $this->assertNotNull($article, 'Article should exist in fixtures');

        // Use a known user ID from the fixtures (admin user)
        $userId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';

        // Create a displayed comment directly
        $comment = $this->Comments->newEntity([
            'foreign_key' => $article->id,
            'model' => 'Articles',
            'user_id' => $userId,
            'content' => 'Visible comment',
            'display' => true,
        ]);

        $savedComment = $this->Comments->save($comment);
        $this->assertNotFalse($savedComment, 'Comment should be saved successfully');
        $this->assertTrue($savedComment->display, 'Comment display should be true');

        // Get comments
        $comments = $this->Articles->getComments($article->id);

        $this->assertInstanceOf(ResultSet::class, $comments);

        // Find our comment in the results
        $found = false;
        foreach ($comments as $c) {
            if ($c->content === 'Visible comment') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'The created comment should be in the results');
    }

    /**
     * Test getComments respects display flag
     */
    public function testGetCommentsRespectsDisplayFlag(): void
    {
        $article = $this->Articles->find()->first();
        $this->assertNotNull($article, 'Article should exist in fixtures');

        // Use a known user ID from the fixtures (admin user)
        $userId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';

        // Create a hidden comment
        $hiddenComment = $this->Comments->newEntity([
            'foreign_key' => $article->id,
            'model' => 'Articles',
            'user_id' => $userId,
            'content' => 'Hidden comment',
            'display' => false,
        ]);
        $savedHidden = $this->Comments->save($hiddenComment);
        $this->assertNotFalse($savedHidden, 'Hidden comment should be saved');

        // Create a visible comment
        $visibleComment = $this->Comments->newEntity([
            'foreign_key' => $article->id,
            'model' => 'Articles',
            'user_id' => $userId,
            'content' => 'Visible comment for display test',
            'display' => true,
        ]);
        $savedVisible = $this->Comments->save($visibleComment);
        $this->assertNotFalse($savedVisible, 'Visible comment should be saved');

        // Get comments - should only return visible ones
        $comments = $this->Articles->getComments($article->id);

        $hasHidden = false;
        $hasVisible = false;
        foreach ($comments as $c) {
            if ($c->content === 'Hidden comment') {
                $hasHidden = true;
            }
            if ($c->content === 'Visible comment for display test') {
                $hasVisible = true;
            }
        }

        $this->assertFalse($hasHidden, 'Hidden comments should not be returned');
        $this->assertTrue($hasVisible, 'Visible comments should be returned');
    }

    /**
     * Test getComments with custom order
     */
    public function testGetCommentsWithCustomOrder(): void
    {
        $article = $this->Articles->find()->first();
        $this->assertNotNull($article);

        // Use a known user ID from the fixtures (admin user)
        $userId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';

        // Create two comments
        $comment1 = $this->Comments->newEntity([
            'foreign_key' => $article->id,
            'model' => 'Articles',
            'user_id' => $userId,
            'content' => 'First comment',
            'display' => true,
        ]);
        $this->Comments->save($comment1);

        $comment2 = $this->Comments->newEntity([
            'foreign_key' => $article->id,
            'model' => 'Articles',
            'user_id' => $userId,
            'content' => 'Second comment',
            'display' => true,
        ]);
        $this->Comments->save($comment2);

        // Get comments ordered by created ASC
        $comments = $this->Articles->getComments($article->id, [
            'order' => ['created' => 'ASC'],
        ]);

        $this->assertInstanceOf(ResultSet::class, $comments);
    }

    /**
     * Test getComments with limit
     */
    public function testGetCommentsWithLimit(): void
    {
        $article = $this->Articles->find()->first();
        $this->assertNotNull($article);

        // Use a known user ID from the fixtures (admin user)
        $userId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';

        // Create multiple comments
        for ($i = 0; $i < 5; $i++) {
            $comment = $this->Comments->newEntity([
                'foreign_key' => $article->id,
                'model' => 'Articles',
                'user_id' => $userId,
                'content' => "Comment {$i}",
                'display' => true,
            ]);
            $this->Comments->save($comment);
        }

        // Get comments with limit
        $comments = $this->Articles->getComments($article->id, ['limit' => 2]);

        $this->assertCount(2, $comments);
    }

    /**
     * Test that behavior works with different model
     */
    public function testBehaviorWithDifferentModel(): void
    {
        // Get the Tags table and add the behavior
        $tagsTable = TableRegistry::getTableLocator()->get('Tags');
        if (!$tagsTable->behaviors()->has('Commentable')) {
            $tagsTable->addBehavior('Commentable');
        }

        $this->assertTrue($tagsTable->hasAssociation('Comments'));

        $behavior = $tagsTable->behaviors()->get('Commentable');
        $this->assertEquals('Comments', $behavior->getConfig('commentsTable'));
    }
}
