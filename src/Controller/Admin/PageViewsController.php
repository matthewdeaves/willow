<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Table\ArticlesTable;
use App\Model\Table\PageViewsTable;
use Cake\Http\Exception\NotFoundException;

/**
 * PageViews Controller
 *
 * @property \App\Model\Table\PageViewsTable $PageViews
 */
class PageViewsController extends AppController
{
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
     * @param int $articleId The ID of the article to retrieve statistics for
     * @return void
     * @throws \Cake\Http\Exception\NotFoundException If the article is not found
     */
    public function pageViewStats($articleId)
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
                'count' => $this->PageViews->find()->func()->count('*')
            ])
            ->group('DATE(created)')
            ->orderBy(['DATE(created)' => 'ASC'])
            ->all();

        $this->set(compact('viewsOverTime', 'article'));
    }

    public function viewRecords($articleId)
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
            $date = new \DateTime($this->request->getQuery('date'));
            $query->where([
                'DATE(created)' => $date->format('Y-m-d')
            ]);
        }

        $viewRecords = $query->all();

        $this->set(compact('viewRecords', 'article'));
    }
}
