<?php
namespace DefaultTheme\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\EventInterface;
use App\Utility\I18nManager;
use App\Utility\SettingsManager;

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
     * using the DefaultTheme. It prepares and sets data
     * for use in the views.
     *
     * @param \Cake\Event\EventInterface $event The beforeRender event that was fired.
     * @return void
     */
    public function beforeRender(EventInterface $event)
    {
        /** @var \Cake\Controller\Controller $controller */
        $controller = $event->getSubject();

        $articlesTable = $this->getController()->fetchTable('Articles');
        $tagsTable = $this->getController()->fetchTable('Tags');

        $menuPages = [];
        switch(SettingsManager::read('SitePages.mainMenuShow', 'root')) {
            case "root":
                $menuPages = $articlesTable->getRootPages();
            break;
            case "selected":
                $menuPages = $articlesTable->getMainMenuPages();
            break;
        }

        $featuredArticles = $articlesTable->getFeatured();
        $rootTags = $tagsTable->getRootTags();
        $articleArchives = $articlesTable->getArchiveDates();

        $privacyPolicyId = SettingsManager::read('SitePages.privacyPolicy', null);
        if ($privacyPolicyId && $privacyPolicyId != 'None') {
            $sitePrivacyPolicy = $articlesTable->find()
                ->select(['id', 'title', 'slug'])
                ->where(['id' => $privacyPolicyId])
                ->cache('priv_page', 'articles')
                ->first()->toArray();
            $controller->set('sitePrivacyPolicy', $sitePrivacyPolicy);
        }
        
        $controller->set(compact(
            'menuPages',
            'rootTags',
            'featuredArticles',
            'articleArchives',
        ));
        
        $controller->set('siteLanguages', I18nManager::getEnabledLanguages());
        $controller->set('selectedSiteLanguage', $controller->getRequest()->getParam('lang', 'en'));
    }
}
