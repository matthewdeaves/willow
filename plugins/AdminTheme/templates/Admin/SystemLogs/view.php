<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\SystemLog $systemLog
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <?php
            echo $this->element('actions_card', [
                'modelName' => 'SystemLog',
                'controllerName' => 'SystemLogs',
                'entity' => $systemLog,
                'entityDisplayName' => $systemLog->level . ' - ' . $systemLog->created,
                'hideNew' => true,
                'hideEdit' => true,
            ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= h($systemLog->level) ?> - <?= h($systemLog->created) ?></h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th class="w-25"><?= __('Level') ?></th>
                            <td><?= h($systemLog->level) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Group Name') ?></th>
                            <td><?= h($systemLog->group_name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($systemLog->created) ?></td>
                        </tr>
                    </table>
                    <div class="mt-4">
                        <h5><?= __('Message') ?></h5>
                        <div class="border p-3 bg-light">
                            <?= $this->Text->autoParagraph(h($systemLog->message)); ?>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h5><?= __('Context') ?></h5>
                        <div class="border p-3 bg-light">
                            <pre class="mb-0"><?= h($systemLog->context) ?></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>