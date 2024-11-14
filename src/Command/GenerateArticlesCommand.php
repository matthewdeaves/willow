<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Entity\Article;
use App\Model\Table\ArticlesTable;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use RuntimeException;

/**
 * GenerateArticles command.
 */
class GenerateArticlesCommand extends Command
{
    /**
     * @var string UUID of the admin user
     */
    private string $adminUserId;

    /**
     * @var \App\Model\Table\ArticlesTable
     */
    private ArticlesTable $Articles;

    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Articles = TableRegistry::getTableLocator()->get('Articles');
        $this->loadAdminUser();
    }

    /**
     * Build option parser method.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->addArgument('count', [
            'help' => __('Number of articles to generate'),
            'required' => true,
        ]);

        return $parser;
    }

    /**
     * Execute the command
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $count = (int)$args->getArgument('count');
        $io->out(__('Generating {0} articles...', $count));

        $successCount = 0;
        $failCount = 0;

        for ($i = 0; $i < $count; $i++) {
            $article = $this->generateArticle();

            if ($this->Articles->save($article, ['associated' => ['Tags']])) {
                $io->out(__('Generated article: {0}', $article->title));
                $successCount++;
            } else {
                $io->error(__('Failed to generate article: {0}', $article->title));
                $errors = $article->getErrors();
                foreach ($errors as $field => $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        $io->error(__('Error in {0}: {1}', $field, $error));
                    }
                }
                $failCount++;
            }
        }

        $io->success(__('Generated {0} articles successfully.', $successCount));
        if ($failCount > 0) {
            $io->warning(__('Failed to generate {0} articles.', $failCount));
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Generate a single article with random tags
     *
     * @return \App\Model\Entity\Article
     */
    private function generateArticle(): Article
    {
        $title = __('Generated Article {0}', uniqid());
        $content = __('This is a generated article content. {0}', uniqid());

        // Generate a random date between 2000 and now
        $year = rand(2000, (int)date('Y'));
        $month = str_pad((string)rand(1, 12), 2, '0', STR_PAD_LEFT);
        $day = str_pad((string)rand(1, 28), 2, '0', STR_PAD_LEFT);
        $hour = str_pad((string)rand(0, 23), 2, '0', STR_PAD_LEFT);
        $minute = str_pad((string)rand(0, 59), 2, '0', STR_PAD_LEFT);
        $second = str_pad((string)rand(0, 59), 2, '0', STR_PAD_LEFT);

        $publishedDate = new DateTime("{$year}-{$month}-{$day} {$hour}:{$minute}:{$second}");

        // Create new article entity
        $article = $this->Articles->newEmptyEntity();
        $article->title = $title;
        $article->slug = ''; // Will be auto-generated
        $article->user_id = $this->adminUserId;
        $article->kind = 'article';
        $article->body = $content;
        $article->is_published = true;
        $article->published = $publishedDate;

        // Get all available tags
        $tagsTable = TableRegistry::getTableLocator()->get('Tags');
        $allTags = $tagsTable->find()
            ->select(['id', 'title'])
            ->toArray();

        if (!empty($allTags)) {
            // Randomly select between 1 and 3 tags
            $numTags = min(rand(1, 3), count($allTags));
            $selectedIndices = array_rand($allTags, $numTags);

            // Convert to array if only one tag selected
            if (!is_array($selectedIndices)) {
                $selectedIndices = [$selectedIndices];
            }

            // Create array of tag entities
            $tags = [];
            foreach ($selectedIndices as $index) {
                $tags[] = $allTags[$index];
            }

            // Set the tags
            $article->tags = $tags;
        }

        return $article;
    }

    /**
     * Load admin user ID
     *
     * @throws \RuntimeException When no admin user is found
     * @return void
     */
    private function loadAdminUser(): void
    {
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $adminUser = $usersTable->find()
            ->select(['id'])
            ->where(['is_admin' => true])
            ->first();

        if ($adminUser) {
            $this->adminUserId = $adminUser->id;
        } else {
            throw new RuntimeException(__('No admin user found.'));
        }
    }
}
