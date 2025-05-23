<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.3.4
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\View\JsonView;

/**
 * Error Handling Controller
 *
 * Controller used by ExceptionRenderer to render error responses.
 */
class ErrorController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        // Only add parent::initialize() if you are confident your appcontroller is safe.
    }

    /**
     * Get alternate view classes that can be used in
     * content-type negotiation.
     *
     * @return array<string>
     */
    public function viewClasses(): array
    {
        return [JsonView::class];
    }

    /**
     * beforeFilter callback.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event.
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(EventInterface $event): void
    {
    }

    /**
     * beforeRender callback.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event.
     * @return \Cake\Http\Response|null|void
     */
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);

        $builder = $this->viewBuilder();
        $templatePath = 'Error';

        if (
            $this->request->getParam('prefix') &&
            in_array($builder->getTemplate(), ['error400', 'error500'], true)
        ) {
            $parts = explode(DIRECTORY_SEPARATOR, (string)$builder->getTemplatePath(), -1);
            $templatePath = implode(DIRECTORY_SEPARATOR, $parts) . DIRECTORY_SEPARATOR . 'Error';
        }

        $this->viewBuilder()->setTemplatePath($templatePath);
    }

    /**
     * afterFilter callback.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event.
     * @return void
     */
    public function afterFilter(EventInterface $event): void
    {
    }
}
