<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

/**
 * SitemapController
 *
 * This controller is responsible for generating the XML sitemap for the application.
 */
class SitemapController extends AppController
{
    /**
     * @var \Cake\ORM\Table The Articles table instance.
     */
    protected Table $Articles;

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
     * Index method
     *
     * Generates the sitemap XML and sets it as the response body.
     *
     * @return \Cake\Http\Response The response object containing the sitemap XML.
     */
    public function index(): Response
    {
        $this->autoRender = false;
        $this->response = $this->response->withDisabledCache();
        $this->response = $this->response->withType('xml');

        $articles = $this->Articles->find('all')
            ->select(['id', 'slug', 'modified', 'published', 'is_page'])
            ->where(['is_page' => false, 'is_published' => true])
            ->order(['created' => 'DESC']);

        $pages = $this->Articles->find('all')
            ->select(['id', 'slug', 'modified', 'published', 'is_page'])
            ->where(['is_page' => true, 'is_published' => true]);

        $sitemapContent = $this->generateSitemapXml($articles, $pages);

        $this->response = $this->response->withStringBody($sitemapContent);

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
    private function generateSitemapXml(Query $articles, Query $pages): string
    {
        $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xmlContent .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Add homepage
        $xmlContent .= $this->generateUrlXml(
            Router::url('/', true),
            FrozenTime::now(),
            FrozenTime::now(),
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
                FrozenTime::now(),
                FrozenTime::now(),
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
     * @param \Cake\I18n\FrozenTime $published The published date.
     * @param \Cake\I18n\FrozenTime $lastmod The last modified date.
     * @param string $changefreq The change frequency.
     * @param string $priority The priority of the URL.
     * @return string The generated URL XML entry.
     */
    private function generateUrlXml(
        string $loc,
        FrozenTime $published,
        FrozenTime $lastmod,
        string $changefreq,
        string $priority
    ): string {
        return sprintf(
            "  <url>\n" .
            "    <loc>%s</loc>\n" .
            "    <lastmod>%s</lastmod>\n" .
            "    <changefreq>%s</changefreq>\n" .
            "    <priority>%s</priority>\n" .
            "  </url>\n",
            htmlspecialchars($loc),
            $lastmod->format('Y-m-d'),
            htmlspecialchars($changefreq),
            htmlspecialchars($priority)
        );
    }
}
