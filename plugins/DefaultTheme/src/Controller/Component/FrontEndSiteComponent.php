<?php
namespace DefaultTheme\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\EventInterface;
use App\Utility\I18nManager;

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
            $articlesTable = $this->getController()->fetchTable('Articles');
            $tagsTable = $this->getController()->fetchTable('Tags');

            $rootPages = $articlesTable->getRootPages();
            $featuredArticles = $articlesTable->getFeatured();
            $rootTags = $tagsTable->getRootTags();
            $articleArchives = $articlesTable->getArchiveDates();

            $controller->set(compact('rootPages', 'rootTags', 'featuredArticles', 'articleArchives'));
        }

        $controller->set('siteLanguages', I18nManager::getEnabledLanguages());
        $controller->set('selectedSiteLanguage', $controller->getRequest()->getParam('lang', 'en'));
    }
}
