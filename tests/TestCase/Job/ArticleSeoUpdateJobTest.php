<?php
declare(strict_types=1);

namespace App\Test\TestCase\Job;

use App\Job\ArticleSeoUpdateJob;
use App\Model\Entity\Article;
use App\Service\Api\AnthropicApiService;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;
use Cake\Queue\Queue\NullQueue;

/**
 * Test case for ArticleSeoUpdateJob
 */
class ArticleSeoUpdateJobTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = ['app.Users', 'app.Articles'];

    /**
     * @var ArticleSeoUpdateJob
     */
    private ArticleSeoUpdateJob $job;

    /**
     * @var \Cake\ORM\Table
     */
    private $articlesTable;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\App\Service\Api\AnthropicApiService
     */
    private $anthropicService;

    /**
     * Set up the test case
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->articlesTable = TableRegistry::getTableLocator()->get('Articles');
        $this->anthropicService = $this->createMock(AnthropicApiService::class);
        $this->job = new ArticleSeoUpdateJob($this->anthropicService);
    }

    /**
     * Test successful execution of the job
     */
    public function testExecuteSuccess(): void
    {
        $article = $this->articlesTable->newEmptyEntity();
        $article->title = 'Test Article';
        $article->body = 'This is a test article body.';
        $article->user_id = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->articlesTable->saveOrFail($article);

        $seoData = [
            'meta_title' => 'SEO Title',
            'meta_description' => 'SEO Description',
            'meta_keywords' => 'SEO Keywords',
            'facebook_description' => 'Facebook Description',
            'linkedin_description' => 'LinkedIn Description',
            'twitter_description' => 'Twitter Description',
            'instagram_description' => 'Instagram Description',
        ];

        $this->anthropicService->expects($this->once())
            ->method('generateArticleSeo')
            ->with($article->title, strip_tags($article->body))
            ->willReturn($seoData);

        $message = new Message(
            ['id' => $article->id, 'title' => 'Test Article'],
            new NullQueue()
        );

        $result = $this->job->execute($message);

        $this->assertEquals(Processor::ACK, $result);

        $updatedArticle = $this->articlesTable->get(1);
        foreach ($seoData as $key => $value) {
            $this->assertEquals($value, $updatedArticle->$key);
        }
    }

    /**
     * Test execution failure when no API result is returned
     */
    public function testExecuteFailureNoApiResult(): void
    {
        $article = $this->articlesTable->newEmptyEntity();
        $article->title = 'Test Article';
        $article->body = 'This is a test article body.';
        $article->user_id = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->articlesTable->saveOrFail($article);

        $this->anthropicService->expects($this->once())
            ->method('generateArticleSeo')
            ->willReturn(null);

        $message = new Message(['id' => $article->id, 'title' => 'Test Article']);

        $result = $this->job->execute($message);

        $this->assertEquals(Processor::REJECT, $result);
    }

    /**
     * Test execution failure when saving the article fails
     */
    public function testExecuteFailureSaveError(): void
    {
        $seoData = [
            'meta_title' => 'SEO Title',
            'meta_description' => 'SEO Description',
            'meta_keywords' => 'SEO Keywords',
            'facebook_description' => 'Facebook Description',
            'linkedin_description' => 'LinkedIn Description',
            'twitter_description' => 'Twitter Description',
            'instagram_description' => 'Instagram Description',
        ];

        $this->anthropicService->expects($this->once())
            ->method('generateArticleSeo')
            ->willReturn($seoData);

        $mockArticle = $this->createMock(Article::class);
        $mockArticle->method('set')->willReturnSelf();

        $mockArticlesTable = $this->createMock('Cake\ORM\Table');
        $mockArticlesTable->method('get')->willReturn($mockArticle);
        $mockArticlesTable->method('save')->willReturn(false);

        TableRegistry::getTableLocator()->set('Articles', $mockArticlesTable);

        $message = new Message(['id' => 1, 'title' => 'Test Article']);

        $result = $this->job->execute($message);

        $this->assertEquals(Processor::REJECT, $result);
    }

    /**
     * Test the maxAttempts property
     */
    public function testMaxAttemptsProperty(): void
    {
        $this->assertEquals(3, ArticleSeoUpdateJob::$maxAttempts);
    }

    /**
     * Test the shouldBeUnique property
     */
    public function testShouldBeUniqueProperty(): void
    {
        $this->assertFalse(ArticleSeoUpdateJob::$shouldBeUnique);
    }
}