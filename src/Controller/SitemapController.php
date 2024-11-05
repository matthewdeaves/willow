<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Response;
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
     * @return \Cake\Http\Response|null Returns null to continue with the request,
     *   or a Response object to short-circuit the request
     */
    public function beforeFilter(EventInterface $event): ?Response
    {
        parent::beforeFilter($event);

        $this->Authentication->allowUnauthenticated(
            [
                'index',
            ]
        );

        return null;
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
        $articlesTable = $this->fetchTable('Articles');

        // Get published hierarchical pages
        $pages = $articlesTable->find('threaded')
            ->where([
                'kind' => 'page',
                'is_published' => 1,
            ])
            ->orderAsc('lft')
            ->all();

        // Get published regular articles
        $articles = $articlesTable->find('all')
            ->where([
                'kind' => 'article',
                'is_published' => 1,
            ])
            ->orderDesc('modified')
            ->all();

        // Get all tags with translations
        $tagsTable = $this->fetchTable('Tags');
        $tags = $tagsTable->find('translations')
            ->select(['Tags.id', 'Tags.title', 'Tags.slug', 'Tags.modified'])
            ->orderAsc('Tags.title')
            ->all();

        $urls = [];

        // Add pages with higher priority since they're structural
        foreach ($pages as $page) {
            $urls[] = [
                'loc' => Router::url([
                    '_name' => 'page-by-slug',
                    $page->slug,
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
                    $article->slug,
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
                    $tag->slug,
                    '_full' => true,
                ]),
                'lastmod' => $tag->modified->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.4',
            ];
        }

        // Add homepage with highest priority
        array_unshift($urls, [
            'loc' => Router::url('/', true),
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
    }
}
