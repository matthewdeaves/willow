<?php
declare(strict_types=1);

namespace App\Model\Table\Trait;

use Cake\Cache\Cache;

trait ArticleCacheTrait
{
    /**
     * Generates a cache key for an article.
     *
     * @param string $slug The slug of the article.
     * @return string The cache key.
     */
    protected function getCacheKey(string $slug): string
    {
        return "article_{$slug}";
    }

    /**
     * Retrieves an article from the cache.
     *
     * @param string $slug The slug of the article.
     * @return mixed The cached data or false if not found.
     */
    public function getFromCache(string $slug): mixed
    {
        return Cache::read($this->getCacheKey($slug), 'articles');
    }

    /**
     * Stores an article in the cache.
     *
     * @param string $slug The slug of the article.
     * @param mixed $data The data to cache.
     * @return void
     */
    public function setToCache(string $slug, mixed $data): void
    {
        Cache::write($this->getCacheKey($slug), $data, 'articles');
    }

    /**
     * Clears an article from the cache.
     *
     * @param string $slug The slug of the article.
     * @return void
     */
    public function clearFromCache(string $slug): void
    {
        Cache::delete($this->getCacheKey($slug), 'articles');
        Cache::delete('articles_index', 'articles_index');
    }

    /**
     * Clears all articles from the cache.
     *
     * @return void
     */
    public function clearAllArticlesCache(): void
    {
        Cache::clear('articles');
        Cache::delete('articles_index', 'articles_index');
    }
}
