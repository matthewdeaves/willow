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
    public function beforeRender(EventInterface $event): void
    {
        $cacheKey = $this->getController()->cacheKey;

        $articlesTable = $this->getController()->fetchTable('Articles');
        $tagsTable = $this->getController()->fetchTable('Tags');

        $menuPages = [];
        switch(SettingsManager::read('SitePages.mainMenuShow', 'root')) {
            case "root":
                $menuPages = $articlesTable->getRootPages($cacheKey);
            break;
            case "selected":
                $menuPages = $articlesTable->getMainMenuPages($cacheKey);
            break;
        }

        $rootTags = [];
        switch(SettingsManager::read('SitePages.mainTagMenuShow', 'root')) {
            case "root":
                $rootTags = $tagsTable->getRootTags($cacheKey);
            break;
            case "selected":
                $rootTags = $tagsTable->getMainMenuTags($cacheKey);
            break;
        }

        $featuredArticles = $articlesTable->getFeatured($cacheKey);
        
        $articleArchives = $articlesTable->getArchiveDates($cacheKey);

        $privacyPolicyId = SettingsManager::read('SitePages.privacyPolicy', null);
        if ($privacyPolicyId && $privacyPolicyId != 'None') {
            $sitePrivacyPolicy = $articlesTable->find()
                ->select(['id', 'title', 'slug'])
                ->where(['id' => $privacyPolicyId])
                ->cache($cacheKey . 'priv_page', 'articles')
                ->first()->toArray();
                $this->getController()->set('sitePrivacyPolicy', $sitePrivacyPolicy);
        }
        
        $this->getController()->set(compact(
            'menuPages',
            'rootTags',
            'featuredArticles',
            'articleArchives',
        ));
        
        $this->getController()->set('siteLanguages', I18nManager::getEnabledLanguages());
        $this->getController()->set('selectedSiteLanguage', $this->getController()->getRequest()->getParam('lang', 'en'));
    }
}
