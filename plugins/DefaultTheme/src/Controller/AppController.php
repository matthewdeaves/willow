<?php
declare(strict_types=1);

namespace DefaultTheme\Controller;

use App\Controller\AppController as BaseController;

class AppController extends BaseController
{
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        // Use the plugin's AppView to ensure helpers are loaded
        $this->viewBuilder()->setClassName('DefaultTheme.AppView');
    }
}
