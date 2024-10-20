<?php
declare(strict_types=1);

namespace App\View;

use Cake\View\View;

class AppView extends View
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading helpers.
     *
     * e.g. `$this->loadHelper('Html');`
     *
     * @return void
     */
    public function initialize(): void
    {
        // Don't forget to call parent::initialize()
        parent::initialize();

        // Set Bootstrap-friendly form templates
        $this->Form->setTemplates([
            'inputContainer' => '<div class="form-group {{type}}{{required}}">{{content}}</div>',
            'inputContainerError' => '<div class="form-group {{type}}{{required}} has-error">{{content}}{{error}}</div>',
            'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
        ]);

        // Add any other view initialization code here
    }
}