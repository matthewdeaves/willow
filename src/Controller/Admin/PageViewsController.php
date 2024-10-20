<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Table\ArticlesTable;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use DateTime;
use Exception;
use Cake\Log\LogTrait;
use Cake\Core\Configure;

/**
 * PageViews Controller
 *
 * @property \App\Model\Table\PageViewsTable $PageViews
 */
class PageViewsController extends AppController
{
    use LogTrait;

    /**
     * Articles Table
     *
     * @var \App\Model\Table\ArticlesTable $Articles
     *
     * This property holds an instance of the ArticlesTable class.
     * It is used to interact with the articles table in the database.
     * The ArticlesTable class provides methods for querying and manipulating
     * article data, such as finding, saving, and deleting articles.
     */
    protected ArticlesTable $Articles;

    /**
     * Retrieves page view statistics for a specific article.
     *
     * This method fetches an article by its ID and retrieves the number of page views
     * grouped by date. It then sets the data to be used in the view.
     *
     * @param int $articleId The ID of the article to retrieve statistics for
     * @return void
     * @throws \Cake\Http\Exception\NotFoundException If the article is not found
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Articles = $this->fetchTable('Articles');
    }

    /**
     * Retrieves page view statistics for a specific article.
     *
     * This method fetches an article by its ID and retrieves the number of page views
     * grouped by date. It then sets the data to be used in the view.
     *
     * @param string $articleId The ID of the article to retrieve statistics for
     * @return void
     * @throws \Cake\Http\Exception\NotFoundException If the article is not found
     */
    public function pageViewStats(string $articleId): void
    {
        $article = $this->Articles->find()
            ->select(['id', 'title', 'slug'])
            ->where(['id' => $articleId])
            ->first();

        if (!$article) {
            throw new NotFoundException(__('Article not found'));
        }

        $viewsOverTime = $this->PageViews->find()
            ->where(['article_id' => $articleId])
            ->select([
                'date' => 'DATE(created)',
                'count' => $this->PageViews->find()->func()->count('*'),
            ])
            ->groupBy('DATE(created)')
            ->orderBy(['DATE(created)' => 'DESC'])
            ->all();

        $this->set(compact('viewsOverTime', 'article'));
    }

    /**
     * Retrieves and sets view records for a specific article.
     *
     * This method fetches an article by its ID and retrieves all associated page view records.
     * If a date query parameter is provided, it filters the page views by that date.
     * The results are then set to be available in the view.
     *
     * @param string $articleId The ID of the article to retrieve view records for.
     * @throws \Cake\Http\Exception\NotFoundException If the article is not found.
     * @return void
     */
    public function viewRecords(string $articleId): void
    {
        $article = $this->Articles->find()
            ->select(['id', 'title', 'slug'])
            ->where(['id' => $articleId])
            ->first();

        if (!$article) {
            throw new NotFoundException(__('Article not found'));
        }

        $query = $this->PageViews->find()
            ->where(['article_id' => $articleId])
            ->orderBy(['created' => 'DESC']);

        if ($this->request->getQuery('date')) {
            $date = new DateTime($this->request->getQuery('date'));
            $query->where([
                'DATE(created)' => $date->format('Y-m-d'),
            ]);
        }

        $viewRecords = $query->all();

        $this->set(compact('viewRecords', 'article'));
    }

    /**
     * Filters page view statistics for a specific article based on date range.
     *
     * @param string $articleId The ID of the article to retrieve statistics for
     * @return \Cake\Http\Response
     * @throws \Cake\Http\Exception\NotFoundException If the article is not found
     */
    public function filterStats(string $articleId): ?Response
    {
        if (Configure::read('debug')) {
            $this->log('Filter request received for article ID: ' . $articleId, 'debug');
            $this->log('Start date: ' . $this->request->getQuery('start'), 'debug');
            $this->log('End date: ' . $this->request->getQuery('end'), 'debug');
        }
        
        try {
            $article = $this->Articles->find()
                ->select(['id', 'title', 'slug'])
                ->where(['id' => $articleId])
                ->first();

            if (!$article) {
                throw new NotFoundException(__('Article not found'));
            }

            $startDate = new DateTime($this->request->getQuery('start'));
            $endDate = new DateTime($this->request->getQuery('end'));

            $viewsOverTime = $this->PageViews->find()
                ->where([
                    'article_id' => $articleId,
                    'created >=' => $startDate->format('Y-m-d'),
                    'created <=' => $endDate->format('Y-m-d 23:59:59'),
                ])
                ->select([
                    'date' => 'DATE(created)',
                    'count' => $this->PageViews->find()->func()->count('*'),
                ])
                ->groupBy('DATE(created)')
                ->orderBy(['DATE(created)' => 'ASC'])
                ->all();

            $totalViews = array_sum(array_column($viewsOverTime->toArray(), 'count'));

            $filteredData = [
                'viewsOverTime' => $viewsOverTime,
                'totalViews' => $totalViews,
            ];

            if (Configure::read('debug')) {
                $this->log('Filtered data: ' . json_encode($filteredData), 'debug');
            }

            return $this->response->withType('application/json')->withStringBody(json_encode($filteredData));
        } catch (Exception $e) {
            $this->log('Error in filterStats: ' . $e->getMessage(), 'error');

            $errorMsg = __('An error occurred while processing your request.');

            return $this->response->withStatus(500)
                ->withType('application/json')
                ->withStringBody(json_encode(['error' => $errorMsg]));
        }
    }
}
