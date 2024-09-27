<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Setting $setting
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <?php
            echo $this->element('actions_card', [
                'modelName' => 'Setting',
                'controllerName' => 'Settings',
                'entity' => $setting,
                'entityDisplayName' => $setting->key_name,
                'hideNew' => true,
            ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= h($setting->key_name) ?></h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th class="w-25"><?= __('Key Name') ?></th>
                            <td><?= h($setting->key_name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Group Name') ?></th>
                            <td><?= h($setting->group_name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($setting->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($setting->modified) ?></td>
                        </tr>
                    </table>
                    <div class="mt-4">
                        <h5><?= __('Value') ?></h5>
                        <div class="border p-3 bg-light">
                            <?= $this->Text->autoParagraph(h($setting->value)); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>