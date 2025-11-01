<?php
declare(strict_types=1);

namespace DefaultTheme\View;

use Cake\View\View;

/**
 * Application View
 *
 * Your application's default view class
 *
 * @link https://book.cakephp.org/5/en/views.html#the-app-view
 */
class AppView extends View
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading helpers.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        // Load DefaultTheme helpers
        $this->loadHelper('DefaultTheme.MetaTags');
        $this->loadHelper('DefaultTheme.Navigation');
        $this->loadHelper('DefaultTheme.SiteConfig');
    }
}
