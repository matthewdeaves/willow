<?php
namespace DefaultTheme\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\EventInterface;

/**
 * FrontEndSiteComponent
 *
 * This component is responsible for preparing and setting up data
 * for the front-end of the site, specifically the article tree and tag list.
 * It automatically runs before rendering non-admin pages.
 */
class FrontEndSiteComponent extends Component
{
    /**
     * Default configuration.
     *
     * Defines the events this component listens to.
     *
     * @var array
     */
    protected array $_defaultConfig = [
        'implementedEvents' => [
            'Controller.beforeRender' => 'beforeRender'
        ]
    ];

    /**
     * Before render callback.
     *
     * This method is automatically called before the view is rendered
     * for non-admin pages. It prepares and sets the article tree and tag list
     * for use in the non admin themed views.
     *
     * @param \Cake\Event\EventInterface $event The beforeRender event that was fired.
     * @return void
     */
    public function beforeRender(EventInterface $event)
    {
        /** @var \Cake\Controller\Controller $controller */
        $controller = $event->getSubject();

        // Check if we're not in the admin area
        if ($controller->getRequest()->getParam('prefix') !== 'Admin') {
            $articleTree = $this->getArticleTree();
            $tagTree = $this->getTags();

            $controller->set('articleTreeMenu', $articleTree);
            $controller->set('tagTreeMenu', $tagTree);
        }
    }

    /**
     * Retrieves the tree of published articles that are marked as pages.
     *
     * This method fetches all published articles with is_page = 1
     * and organizes them into a hierarchical tree structure.
     *
     * @return \Cake\ORM\Query The query result containing the article tree.
     */
    protected function getArticleTree()
    {
        // Fetch and return the tree of published articles with is_page = 1
        $articlesTable = $this->getController()->fetchTable('Articles');

        $conditions = [
            'Articles.is_published' => 1,
        ];

        return $articlesTable->getPageTree($conditions);
    }

    /**
     * Retrieves all tags with their associated articles.
     *
     * This method fetches all tags, ordered alphabetically, along with
     * their associated articles. For each article, it selects only
     * specific fields and orders them by creation date.
     *
     * @return \Cake\ORM\ResultSet The result set containing all tags with their articles.
     */
    protected function getTags()
    {
        $tagsTable = $this->getController()->fetchTable('Tags');
        $query = $tagsTable->find()
            ->select([
                'Tags.id',
                'Tags.title',
                'Tags.slug',
                'Tags.description',
                'Tags.created',
                'Tags.modified',
                'Tags.meta_title',
                'Tags.meta_description',
                'Tags.meta_keywords',
                'Tags.facebook_description',
                'Tags.linkedin_description',
                'Tags.instagram_description',
                'Tags.twitter_description'
            ])
            ->innerJoinWith('Articles', function ($q) {
                return $q->where(['Articles.is_published' => true]);
            })
            ->groupBy([
                'Tags.id',
                'Tags.title',
                'Tags.slug',
                'Tags.description',
                'Tags.created',
                'Tags.modified',
                'Tags.meta_title',
                'Tags.meta_description',
                'Tags.meta_keywords',
                'Tags.facebook_description',
                'Tags.linkedin_description',
                'Tags.instagram_description',
                'Tags.twitter_description'
            ])
            ->orderBy(['Tags.title' => 'ASC']);
    
        return $query->all();
    }
}
