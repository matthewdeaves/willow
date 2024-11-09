<?php
use Cake\Core\Configure;
use Cake\Utility\Inflector;

$modelNamePlural = Inflector::pluralize($modelName);
$debugOnlyOptions = $debugOnlyOptions ?? [];
?>

<aside class="col-lg-3">
    <div class="card mb-4">
        <div class="card-body">
            <h4 class="card-title"><?= __('Actions') ?></h4>
            <ul class="list-group list-group-flush">

                <li class="list-group-item">
                    <?= $this->Html->link(__('List {0}', 
                        [$modelNamePlural]),
                        [
                            'controller' => $controllerName,
                            'action' => 'index',
                            '?' => isset($urlParams) ? $urlParams : []
                        ],
                        ['class' => 'list-group-item list-group-item-action']
                    ) ?>
                </li>

                <?php if (!isset($hideNew)) : ?>
                    <?php if (
                        (Configure::read('debug') && in_array('add', $debugOnlyOptions))
                        || !in_array('add', $debugOnlyOptions)
                        ) : ?>
                    <li class="list-group-item">
                        <?= $this->Html->link(__('New {0}', [$modelName]), ['controller' => $controllerName, 'action' => 'add'], ['class' => 'list-group-item list-group-item-action']) ?>
                    </li>
                    <?php endif; ?>
                <?php endif; ?>


                <?php if (!in_array($this->request->getParam('action'), ['add', 'edit', 'bulkUpload'])): ?>
                    <?php if (!isset($hideEdit)) : ?>
                    <li class="list-group-item">
                        <?= $this->Html->link(__('Edit {0}', [$modelName]), ['controller' => $controllerName, 'action' => 'edit', $entity->id], ['class' => 'list-group-item list-group-item-action']) ?>
                    </li>
                    <?php endif; ?>
                <?php endif; ?>


                <?php if (!in_array($this->request->getParam('action'), ['add', 'bulkUpload'])): ?>
                    <?php if (!isset($hideDelete)) : ?>
                        <?php
                            $customConfirm = __('Are you sure you want to delete {0}?', $entityDisplayName);
                            if (isset($confirm)) {
                                $customConfirm = $confirm;
                            }
                        ?>

                        <?php if (empty($debugOnlyOptions) || in_array('delete', $debugOnlyOptions) && Configure::read('debug')) : ?>
                        <li class="list-group-item">
                            <?= $this->Form->postLink(
                                __('Delete {0}', [$modelName]),
                                ['controller' => $controllerName, 'action' => 'delete', $entity->id],
                                ['confirm' => $customConfirm, 'class' => 'list-group-item list-group-item-action text-danger']
                            ) ?>
                        </li>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</aside>