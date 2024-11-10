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
            'inputContainer' => '<div class="{{type}}{{required}}">{{content}}</div>',
            'inputContainerError' => '<div class="{{type}}{{required}} has-validation">{{content}}{{error}}</div>',
            'error' => '<div class="invalid-feedback">{{content}}</div>',
            'label' => '<label{{attrs}} class="form-label">{{text}}</label>',
            'input' => '<input type="{{type}}" name="{{name}}" class="form-control {{attrs.class}}" {{attrs}}>',
            'textarea' => '<textarea name="{{name}}" class="form-control {{attrs.class}}" {{attrs}}>{{value}}</textarea>',
            'select' => '<select name="{{name}}" class="form-select {{attrs.class}}" {{attrs}}>{{content}}</select>',
            'checkbox' => '<input type="checkbox" name="{{name}}" class="form-check-input {{attrs.class}}" {{attrs}}>',
            'nestingLabel' => '{{hidden}}{{input}}<label{{attrs}} class="form-check-label">{{text}}</label>',
            'radioContainer' => '<div class="form-check">{{content}}</div>',
            'radio' => '<input type="radio" name="{{name}}" class="form-check-input"{{attrs}}>',
            'formGroup' => '{{label}}{{input}}',
            'inputGroupContainer' => '<div class="input-group {{type}}{{required}}">{{content}}</div>',
            'inputGroupText' => '<span class="input-group-text">{{content}}</span>',
            'inputGroupTextError' => '<span class="input-group-text">{{content}}</span>',
        ]);

        // Add any other view initialization code here
    }
}