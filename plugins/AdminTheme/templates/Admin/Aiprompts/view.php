<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Aiprompt $aiprompt
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <?php
            echo $this->element('actions_card', [
                'modelName' => 'Aiprompt',
                'controllerName' => 'Aiprompts',
                'entity' => $aiprompt,
                'entityDisplayName' => $aiprompt->task_type,
                'hideDelete' => true,
            ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= h($aiprompt->task_type) ?></h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th><?= __('Task Type') ?></th>
                            <td><?= h($aiprompt->task_type) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Model') ?></th>
                            <td><?= h($aiprompt->model) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Max Tokens') ?></th>
                            <td><?= $this->Number->format($aiprompt->max_tokens) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Temperature') ?></th>
                            <td><?= $this->Number->format($aiprompt->temperature) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created At') ?></th>
                            <td><?= h($aiprompt->created_at) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified At') ?></th>
                            <td><?= h($aiprompt->modified_at) ?></td>
                        </tr>
                    </table>
                    <div class="mt-4">
                        <h5><?= __('System Prompt') ?></h5>
                        <div class="border p-3 bg-light">
                            <?= $this->Text->autoParagraph(h($aiprompt->system_prompt)); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>