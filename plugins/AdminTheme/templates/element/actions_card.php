<?php
use Cake\Core\Configure;
use Cake\Utility\Inflector;

$modelNamePlural = Inflector::pluralize($modelName);
$debugOnlyOptions = $debugOnlyOptions ?? [];
?>
<div class="col-md-2">
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><?= __('Actions') ?></h5>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                <?= $this->Html->link(__('List {0}', 
                    [$modelNamePlural]),
                    [
                        'controller' => $controllerName,
                        'action' => 'index',
                        '?' => isset($urlParams) ? $urlParams : []
                    ],
                    ['class' => 'list-group-item list-group-item-action']
                ) ?>

                <?php if (!isset($hideNew)) : ?>
                    <?php if (in_array('add', $debugOnlyOptions) && Configure::read('debug')) : ?>
                        <?= $this->Html->link(__('New {0}', [$modelName]), ['controller' => $controllerName, 'action' => 'add'], ['class' => 'list-group-item list-group-item-action']) ?>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!in_array($this->request->getParam('action'), ['add', 'edit', 'bulkUpload'])): ?>

                    <?php if (!isset($hideEdit)) : ?>
                    <?= $this->Html->link(__('Edit {0}', [$modelName]), ['controller' => $controllerName, 'action' => 'edit', $entity->id], ['class' => 'list-group-item list-group-item-action']) ?>
                    <?php endif; ?>

                    <?php if (!isset($hideDelete)) : ?>
                        <?php
                            $customConfirm = __('Are you sure you want to delete {0}?', $entityDisplayName);
                            if (isset($confirm)) {
                                $customConfirm = $confirm;
                            }
                        ?>

                        <?php if (in_array('delete', $debugOnlyOptions) && Configure::read('debug')) : ?>
                        <?= $this->Form->postLink(
                            __('Delete {0}', [$modelName]),
                            ['controller' => $controllerName, 'action' => 'delete', $entity->id],
                            ['confirm' => $customConfirm, 'class' => 'list-group-item list-group-item-action text-danger']
                        ) ?>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>