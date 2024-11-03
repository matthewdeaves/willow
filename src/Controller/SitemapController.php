<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\ArticlesTable;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\View\XmlView;

/**
 * SitemapController
 *
 * Responsible for generating the XML sitemap for the application.
 */
class SitemapController extends AppController
{
    /**
     * @var \App\Model\Table\ArticlesTable The Articles table instance.
     */
    protected ArticlesTable $Articles;

    /**
     * Initialize method
     *
     * Initializes the Articles table instance.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Articles = TableRegistry::getTableLocator()->get('Articles');
    }

    /**
     * Performs actions before the controller's action is executed.
     *
     * This method is called before each action in the controller is executed.
     * It allows you to perform initialization logic, modify the request, or restrict access to certain actions.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return \Cake\Http\Response|null The response object or null to continue the normal flow.
     */
    public function beforeFilter(EventInterface $event): ?Response
    {
        parent::beforeFilter($event);

        $this->Authentication->addUnauthenticatedActions(['index']);

        return null;
    }

    /**
     * Index method
     *
     * Generates the sitemap XML and sets it as the response body.
     *
     * @return \Cake\Http\Response The response object containing the sitemap XML.
     */
    public function index(): Response
    {
        // Disable auto-rendering of a view template
        $this->autoRender = false;
    
        // Set the response type to XML
        $this->response = $this->response->withType('xml');
    
        // Fetch articles and pages
        $articles = $this->Articles->find('all')
            ->select(['id', 'slug', 'modified', 'published', 'is_page'])
            ->where(['is_page' => false, 'is_published' => true])
            ->orderBy(['created' => 'DESC']);
    
        $pages = $this->Articles->find('all')
            ->select(['id', 'slug', 'modified', 'published', 'is_page'])
            ->where(['is_page' => true, 'is_published' => true]);
    
        // Generate the sitemap XML content
        $sitemapContent = $this->generateSitemapXml($articles, $pages);
    
        // Set the response body with the sitemap content
        $this->response = $this->response->withStringBody($sitemapContent);
    
        // Return the response
        return $this->response;
    }

    /**
     * Generate sitemap XML
     *
     * Generates the sitemap XML content for articles and pages.
     *
     * @param \Cake\ORM\Query $articles The query object for articles.
     * @param \Cake\ORM\Query $pages The query object for pages.
     * @return string The generated sitemap XML content.
     */
    private function generateSitemapXml(SelectQuery $articles, SelectQuery $pages): string
    {
        $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xmlContent .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Add homepage
        $xmlContent .= $this->generateUrlXml(
            Router::url('/', true),
            DateTime::now(),
            DateTime::now(),
            'daily',
            '1.0'
        );

        // Add articles
        foreach ($articles as $article) {
            $xmlContent .= $this->generateUrlXml(
                Router::url(['controller' => 'Articles', 'action' => 'view', $article->slug], true),
                $article->published,
                $article->modified,
                'weekly',
                '0.8'
            );
        }

        // Add pages
        foreach ($pages as $page) {
            $xmlContent .= $this->generateUrlXml(
                Router::url(['controller' => 'Pages', 'action' => 'view', $page->slug], true),
                $page->published,
                $page->modified,
                'monthly',
                '0.6'
            );
        }

        // Add paginated article listings
        $totalArticles = $articles->count();
        $articlesPerPage = 10; // Adjust this to match your pagination settings
        $totalPages = ceil($totalArticles / $articlesPerPage);

        for ($i = 1; $i <= $totalPages; $i++) {
            $xmlContent .= $this->generateUrlXml(
                Router::url(['controller' => 'Articles', 'action' => 'index', 'page' => $i], true),
                DateTime::now(),
                DateTime::now(),
                'daily',
                $i === 1 ? '0.9' : '0.7'
            );
        }

        $xmlContent .= '</urlset>';

        return $xmlContent;
    }

    /**
     * Generate URL XML
     *
     * Generates a single URL entry for the sitemap XML.
     *
     * @param string $loc The URL location.
     * @param \Cake\I18n\DateTime $published The published date.
     * @param \Cake\I18n\DateTime $lastmod The last modified date.
     * @param string $changefreq The change frequency.
     * @param string $priority The priority of the URL.
     * @return string The generated URL XML entry.
     */
    private function generateUrlXml(
        string $loc,
        DateTime $published,
        DateTime $lastmod,
        string $changefreq,
        string $priority
    ): string {
        return sprintf(
            "  <url>\n" .
            "    <loc>%s</loc>\n" .
            "    <lastmod>%s</lastmod>\n" .
            "    <publication_date>%s</publication_date>\n" .
            "    <changefreq>%s</changefreq>\n" .
            "    <priority>%s</priority>\n" .
            "  </url>\n",
            htmlspecialchars($loc),
            $lastmod->format('Y-m-d'),
            $published->format('Y-m-d'),
            htmlspecialchars($changefreq),
            htmlspecialchars($priority)
        );
    }
}
