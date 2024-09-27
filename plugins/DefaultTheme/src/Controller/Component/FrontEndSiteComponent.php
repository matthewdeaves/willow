<?php
namespace DefaultTheme\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\EventInterface;

//TODO DESCRIPTION ABOUT HOS THIS COMPONENT IS FOR SETTING STUFF FOR USE IN THE THEME FOR FRONT END
class FrontEndSiteComponent extends Component
{
    protected array $_defaultConfig = [
        'implementedEvents' => [
            'Controller.beforeRender' => 'beforeRender'
        ]
    ];

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

    protected function getArticleTree()
    {
        // Fetch and return the tree of published articles with is_page = 1
        $articlesTable = $this->getController()->fetchTable('Articles');

        $conditions = [
            'Articles.is_published' => 1,
        ];

        return $articlesTable->getPageTree($conditions);
    }

    protected function getTags()
    {
        $tagsTable = $this->getController()->fetchTable('Tags');
        $query = $tagsTable->find()
            ->contain(['Articles' => function ($q) {
                return $q->select(['id', 'title', 'slug', 'user_id'])
                        ->order(['Articles.created' => 'DESC']);
            }])
            ->orderBy(['Tags.title' => 'ASC']);

        return $query->all();
    }
}
