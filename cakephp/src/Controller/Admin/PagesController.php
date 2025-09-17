<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Cache\Cache;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;
use Cake\ORM\Query;
use Exception;

/**
 * Admin Pages Controller
 *
 * Handles CRUD operations for static pages (articles with kind='page').
 * Enhanced admin interface for managing all site pages including connect pages.
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 */
class PagesController extends AppController
{
    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadModel('Articles');
    }

    /**
     * Clears the content cache
     *
     * @return void
     */
    private function clearContentCache(): void
    {
        Cache::clear('content');
    }

    /**
     * Index method - List all pages with enhanced filtering
     *
     * @return \Cake\Http\Response|null
     */
    public function index(): ?Response
    {
        $statusFilter = $this->request->getQuery('status');
        $menuFilter = $this->request->getQuery('menu');
        $search = $this->request->getQuery('search');

        $query = $this->Articles->find()
            ->select([
                'Articles.id',
                'Articles.title',
                'Articles.slug',
                'Articles.created',
                'Articles.modified',
                'Articles.is_published',
                'Articles.main_menu',
                'Articles.footer_menu',
                'Articles.meta_title',
                'Articles.meta_description',
                'Users.username',
            ])
            ->leftJoinWith('Users')
            ->where(['Articles.kind' => 'page'])
            ->orderBy(['Articles.title' => 'ASC']);

        // Apply status filter
        if ($statusFilter !== null) {
            $query->where(['Articles.is_published' => (int)$statusFilter]);
        }

        // Apply menu filter
        if ($menuFilter === 'header') {
            $query->where(['Articles.main_menu' => 1]);
        } elseif ($menuFilter === 'footer') {
            $query->where(['Articles.footer_menu' => 1]);
        } elseif ($menuFilter === 'both') {
            $query->where(['Articles.main_menu' => 1, 'Articles.footer_menu' => 1]);
        } elseif ($menuFilter === 'none') {
            $query->where(['Articles.main_menu' => 0, 'Articles.footer_menu' => 0]);
        }

        // Apply search filter
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Articles.title LIKE' => '%' . $search . '%',
                    'Articles.slug LIKE' => '%' . $search . '%',
                    'Articles.body LIKE' => '%' . $search . '%',
                    'Articles.meta_title LIKE' => '%' . $search . '%',
                    'Articles.meta_description LIKE' => '%' . $search . '%',
                ],
            ]);
        }

        $pages = $this->paginate($query);

        // Get statistics
        $totalPages = $this->Articles->find()->where(['kind' => 'page'])->count();
        $publishedPages = $this->Articles->find()->where(['kind' => 'page', 'is_published' => 1])->count();
        $unpublishedPages = $totalPages - $publishedPages;

        $this->set(compact('pages', 'search', 'statusFilter', 'menuFilter', 'totalPages', 'publishedPages', 'unpublishedPages'));

        return null;
    }

    /**
     * View method - Display a single page
     *
     * @param string|null $id Page id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null): ?Response
    {
        $page = $this->Articles->get($id, [
            'contain' => ['Users', 'Tags'],
            'conditions' => ['Articles.kind' => 'page']
        ]);

        $this->set(compact('page'));

        return null;
    }

    /**
     * Add method - Create a new page
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $page = $this->Articles->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['kind'] = 'page'; // Ensure this is a page
            $data['user_id'] = $this->request->getAttribute('identity')->id;
            
            $page = $this->Articles->patchEntity($page, $data);
            
            if ($this->Articles->save($page)) {
                $this->clearContentCache();
                $this->Flash->success(__('The page has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The page could not be saved. Please, try again.'));
        }

        // Get existing slugs for validation
        $existingSlugs = $this->Articles->find()
            ->select(['slug'])
            ->where(['kind' => 'page'])
            ->toArray();

        $this->set(compact('page', 'existingSlugs'));

        return null;
    }

    /**
     * Edit method - Update an existing page
     *
     * @param string|null $id Page id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null): ?Response
    {
        $page = $this->Articles->get($id, [
            'conditions' => ['Articles.kind' => 'page'],
            'contain' => ['Tags']
        ]);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $data['kind'] = 'page'; // Ensure this remains a page
            
            $page = $this->Articles->patchEntity($page, $data, [
                'associated' => ['Tags']
            ]);
            
            if ($this->Articles->save($page)) {
                $this->clearContentCache();
                $this->Flash->success(__('The page has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The page could not be saved. Please, try again.'));
        }

        // Get existing slugs for validation (excluding current page)
        $existingSlugs = $this->Articles->find()
            ->select(['slug'])
            ->where([
                'kind' => 'page',
                'id !=' => $id
            ])
            ->toArray();

        $this->set(compact('page', 'existingSlugs'));

        return null;
    }

    /**
     * Delete method - Remove a page
     *
     * @param string|null $id Page id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        
        $page = $this->Articles->get($id, [
            'conditions' => ['Articles.kind' => 'page']
        ]);
        
        if ($this->Articles->delete($page)) {
            $this->clearContentCache();
            $this->Flash->success(__('The page has been deleted.'));
        } else {
            $this->Flash->error(__('The page could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Bulk actions for pages
     *
     * @return \Cake\Http\Response|null
     */
    public function bulkActions(): ?Response
    {
        $this->request->allowMethod(['post']);
        
        $action = $this->request->getData('bulk_action');
        $selectedPages = $this->request->getData('selected_pages', []);
        
        if (empty($selectedPages)) {
            $this->Flash->error(__('No pages selected.'));
            return $this->redirect(['action' => 'index']);
        }

        $count = 0;
        
        switch ($action) {
            case 'publish':
                $count = $this->Articles->updateAll(
                    ['is_published' => 1], 
                    ['id IN' => $selectedPages, 'kind' => 'page']
                );
                $this->Flash->success(__('Published {0} pages.', $count));
                break;
                
            case 'unpublish':
                $count = $this->Articles->updateAll(
                    ['is_published' => 0], 
                    ['id IN' => $selectedPages, 'kind' => 'page']
                );
                $this->Flash->success(__('Unpublished {0} pages.', $count));
                break;
                
            case 'delete':
                $count = $this->Articles->deleteAll([
                    'id IN' => $selectedPages, 
                    'kind' => 'page'
                ]);
                $this->Flash->success(__('Deleted {0} pages.', $count));
                break;
                
            default:
                $this->Flash->error(__('Invalid bulk action.'));
        }
        
        if ($count > 0) {
            $this->clearContentCache();
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Create connect pages - Helper method to create standard connect pages
     *
     * @return \Cake\Http\Response|null
     */
    public function createConnectPages(): ?Response
    {
        $this->request->allowMethod(['post']);

        $connectPages = [
            [
                'title' => 'About the Author',
                'slug' => 'about-author',
                'body' => '<h1>About the Author</h1>
<p>Welcome to my personal page. I am a passionate developer working with modern web technologies.</p>
<p>My expertise includes:</p>
<ul>
<li>Full-stack web development</li>
<li>CakePHP and PHP development</li>
<li>Frontend technologies (Bootstrap, JavaScript)</li>
<li>Database design and optimization</li>
<li>DevOps and containerization</li>
</ul>
<p>Feel free to connect with me through the various channels available on this site.</p>',
                'meta_title' => 'About the Author - WhatIsMyAdapter',
                'meta_description' => 'Learn more about the author and developer behind WhatIsMyAdapter CMS.',
            ],
            [
                'title' => 'GitHub Repository',
                'slug' => 'github',
                'body' => '<h1>GitHub Repository</h1>
<p>This project is open source and available on GitHub. You can find the complete source code, documentation, and contribute to the project.</p>
<p><a href="https://github.com/garzarobm/willow" target="_blank" class="btn btn-primary">View on GitHub</a></p>
<p>The repository includes:</p>
<ul>
<li>Complete CakePHP 5.x application</li>
<li>Docker configuration for easy deployment</li>
<li>Comprehensive documentation</li>
<li>Test suites and CI/CD configuration</li>
</ul>',
                'meta_title' => 'GitHub Repository - WhatIsMyAdapter',
                'meta_description' => 'Access the source code and contribute to the WhatIsMyAdapter CMS project on GitHub.',
            ],
            [
                'title' => 'Hire Me',
                'slug' => 'hire-me',
                'body' => '<h1>Hire Me</h1>
<p>Are you looking for a skilled developer for your next project? I am available for freelance work and consulting.</p>
<h2>Services I Offer:</h2>
<ul>
<li>Custom web application development</li>
<li>CakePHP and PHP consulting</li>
<li>Database design and optimization</li>
<li>API development and integration</li>
<li>Performance optimization</li>
<li>Code review and refactoring</li>
</ul>
<h2>Contact Me:</h2>
<p>Please reach out through the contact form or email me directly to discuss your project requirements.</p>',
                'meta_title' => 'Hire Me - Professional Development Services',
                'meta_description' => 'Looking for a skilled PHP developer? Contact me for freelance work and consulting services.',
            ],
            [
                'title' => 'Follow Me',
                'slug' => 'follow-me',
                'body' => '<h1>Follow Me</h1>
<p>Stay connected and follow my work across various platforms:</p>
<h2>Social Media & Professional Networks:</h2>
<ul>
<li><strong>GitHub:</strong> Follow my open source contributions and projects</li>
<li><strong>LinkedIn:</strong> Connect for professional networking</li>
<li><strong>Twitter:</strong> Get updates on latest tech trends and insights</li>
</ul>
<h2>Subscribe for Updates:</h2>
<p>Sign up for notifications about new articles, project updates, and technical insights.</p>
<p>I regularly share content about:</p>
<ul>
<li>Web development best practices</li>
<li>CakePHP tutorials and tips</li>
<li>Technology trends and insights</li>
<li>Project showcases and case studies</li>
</ul>',
                'meta_title' => 'Follow Me - Stay Connected',
                'meta_description' => 'Follow me on social media and stay updated with the latest content and projects.',
            ],
        ];

        $created = 0;
        $currentUser = $this->request->getAttribute('identity');

        foreach ($connectPages as $pageData) {
            // Check if page already exists
            $existingPage = $this->Articles->find()
                ->where(['slug' => $pageData['slug'], 'kind' => 'page'])
                ->first();

            if (!$existingPage) {
                $page = $this->Articles->newEmptyEntity();
                $pageData['kind'] = 'page';
                $pageData['user_id'] = $currentUser->id;
                $pageData['is_published'] = 1;
                $pageData['created'] = new \DateTime();
                $pageData['modified'] = new \DateTime();

                $page = $this->Articles->patchEntity($page, $pageData);

                if ($this->Articles->save($page)) {
                    $created++;
                }
            }
        }

        if ($created > 0) {
            $this->clearContentCache();
            $this->Flash->success(__('Created {0} connect pages successfully.', $created));
        } else {
            $this->Flash->info(__('All connect pages already exist.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}