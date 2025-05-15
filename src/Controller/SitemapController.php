<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Routing\Router;
use Cake\View\XmlView;

/**
 * SitemapController handles the generation of XML sitemaps for the application.
 *
 * This controller generates a sitemap.xml file that includes all published pages,
 * articles, and tags with their respective priorities and change frequencies.
 * The sitemap supports internationalization (i18n) and works across different locales.
 */
class SitemapController extends AppController
{
    /**
     * Configures authentication requirements before controller actions are executed.
     *
     * This method is called before each action in the controller. It configures which
     * actions can be accessed without authentication. In this case, the 'index' action
     * is allowed to be accessed by unauthenticated users, enabling public access to
     * the sitemap.xml file.
     *
     * @param \Cake\Event\EventInterface $event The event object
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['index']);
    }

    /**
     * Specifies the view classes that this controller can use.
     *
     * @return array<class-string> Array containing XmlView class for sitemap generation
     */
    public function viewClasses(): array
    {
        return [XmlView::class];
    }

    /**
     * Generates the XML sitemap for the application.
     *
     * This method fetches all published pages, articles, and tags from the database
     * and generates a sitemap.xml file according to the sitemap protocol specifications.
     * Different content types are assigned different priorities and change frequencies:
     * - Homepage: Priority 1.0, daily changes
     * - Pages: Priority 0.8, weekly changes
     * - Articles: Priority 0.6, daily changes
     * - Tags: Priority 0.4, weekly changes
     *
     * The method handles translations through the Translate behavior and works
     * across different locales (e.g., /en/sitemap.xml, /fr/sitemap.xml).
     *
     * @return void
     * @link https://www.sitemaps.org/protocol.html Sitemap protocol reference
     */
    public function index(): void
    {
        // Get the current language or default to 'en'
        $currentLang = $this->request->getParam('lang', 'en');

        $articlesTable = $this->fetchTable('Articles');

        // Get published hierarchical pages
        $pages = $articlesTable->find('threaded')
            ->where([
                'kind' => 'page',
                'is_published' => 1,
            ])
            ->orderByAsc('lft')
            ->all();

        // Get published regular articles
        $articles = $articlesTable->find('all')
            ->where([
                'kind' => 'article',
                'is_published' => 1,
            ])
            ->orderByDesc('modified')
            ->all();

        // Get all tags with translations
        $tagsTable = $this->fetchTable('Tags');
        $tags = $tagsTable->find('translations')
            ->select(['Tags.id', 'Tags.title', 'Tags.slug', 'Tags.modified'])
            ->orderByAsc('Tags.title')
            ->all();

        $urls = [];

        // Add pages with higher priority
        foreach ($pages as $page) {
            $urls[] = [
                'loc' => Router::url([
                    '_name' => 'page-by-slug',
                    'slug' => $page->slug,
                    'lang' => $currentLang,
                    '_full' => true,
                ]),
                'lastmod' => $page->modified->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // Add articles with medium priority
        foreach ($articles as $article) {
            $urls[] = [
                'loc' => Router::url([
                    '_name' => 'article-by-slug',
                    'slug' => $article->slug,
                    'lang' => $currentLang,
                    '_full' => true,
                ]),
                'lastmod' => $article->modified->format('Y-m-d'),
                'changefreq' => 'daily',
                'priority' => '0.6',
            ];
        }

        // Add tags with lower priority
        foreach ($tags as $tag) {
            $urls[] = [
                'loc' => Router::url([
                    '_name' => 'tag-by-slug',
                    'slug' => $tag->slug,
                    'lang' => $currentLang,
                    '_full' => true,
                ]),
                'lastmod' => $tag->modified->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.4',
            ];
        }

        // Add homepage with highest priority
        array_unshift($urls, [
            'loc' => Router::url([
                '_name' => 'home',
                'lang' => $currentLang,
                '_full' => true,
            ]),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ]);

        $this->viewBuilder()
            ->setOption('rootNode', 'urlset')
            ->setOption('serialize', ['@xmlns', 'url']);

        $this->set([
            '@xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
            'url' => $urls,
        ]);

        // Set response type and cache headers
        $this->response = $this->response
            ->withType('xml')
            ->withHeader('Content-Type', 'application/xml')
            ->withCache('-1 minute', '+1 day');
    }
}
