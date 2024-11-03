<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\View\XmlView;
use Cake\Routing\Router;

class SitemapController extends AppController
{
    public function viewClasses(): array
    {
        return [XmlView::class];
    }

    public function index()
    {
        $articlesTable = $this->fetchTable('Articles');
        
        // Get published hierarchical pages
        $pages = $articlesTable->find('threaded')
            ->where([
                'is_page' => 1,
                'is_published' => 1
            ])
            ->orderAsc('lft')
            ->all();
            
        // Get published regular articles
        $articles = $articlesTable->find('all')
            ->where([
                'is_page' => 0,
                'is_published' => 1
            ])
            ->orderDesc('modified')
            ->all();
            
        // Get all tags
        $tagsTable = $this->fetchTable('Tags');
        $tags = $tagsTable->find('all')
            ->orderAsc('title')
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