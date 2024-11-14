<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\DateTime;
use App\Model\Table\ArticlesTable;
use Cake\ORM\TableRegistry;

class GenerateArticlesCommand extends Command
{
    private $adminUserId;

    public function initialize(): void
    {
        parent::initialize();
        $this->Articles = new ArticlesTable();
        $this->loadAdminUser();
    }

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->addArgument('count', [
            'help' => 'Number of articles to generate',
            'required' => true
        ]);
        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $count = $args->getArgument('count');
        $io->out("Generating {$count} articles...");

        $successCount = 0;
        $failCount = 0;

        for ($i = 0; $i < $count; $i++) {
            $article = $this->generateArticle();
            if ($this->Articles->save($article)) {
                $io->out("Generated article: {$article->title}");
                $successCount++;
            } else {
                $io->error("Failed to generate article: {$article->title}");
                $errors = $article->getErrors();
                foreach ($errors as $field => $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        $io->error("Error in {$field}: {$error}");
                    }
                }
                $failCount++;
            }
        }

        $io->success("Generated {$successCount} articles successfully.");
        if ($failCount > 0) {
            $io->warning("Failed to generate {$failCount} articles.");
        }
        return static::CODE_SUCCESS;
    }

    private function generateArticle()
    {
        // Here you would integrate with an AI service to generate content
        // For this example, we'll use placeholder content
        $title = "Generated Article " . uniqid();
        $content = "This is a generated article content. " . uniqid();
        
        // Generate a random date and time
        $year = rand(2000, 2023); // Random year between 2000 and 2023
        $month = rand(1, 12); // Random month
        $day = rand(1, 28); // Random day (to avoid issues with February)
        $hour = rand(0, 23); // Random hour
        $minute = rand(0, 59); // Random minute
        $second = rand(0, 59); // Random second

        $publishedDate = new DateTime("{$year}-{$month}-{$day} {$hour}:{$minute}:{$second}");

        $article = $this->Articles->newEmptyEntity();
        $article->title = $title;
        $article->slug = '';
        $article->user_id = $this->adminUserId;
        $article->kind = 'article';
        $article->body = $content;
        $article->is_published = true;
        $article->published = $publishedDate;

        return $article;
    }

    private function loadAdminUser()
    {
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $adminUser = $usersTable->find()
            ->where(['is_admin' => 1])
            ->first();

        if ($adminUser) {
            $this->adminUserId = $adminUser->id;
        } else {
            throw new \RuntimeException('No admin user found.');
        }
    }
}