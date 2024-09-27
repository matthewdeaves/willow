<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\EmailTemplate $emailTemplate
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <?php
            echo $this->element('actions_card', [
                'modelName' => 'EmailTemplate',
                'controllerName' => 'EmailTemplates',
                'entity' => $emailTemplate,
                'entityDisplayName' => $emailTemplate->name
            ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= h($emailTemplate->name) ?></h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th class="w-25"><?= __('Name') ?></th>
                            <td><?= h($emailTemplate->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Subject') ?></th>
                            <td><?= h($emailTemplate->subject) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($emailTemplate->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($emailTemplate->modified) ?></td>
                        </tr>
                    </table>
                    <div class="mt-4">
                        <h5><?= __('Body HTML') ?></h5>
                        <div class="border p-3 bg-light">
                            <?= $emailTemplate->body_html; ?>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h5><?= __('Body Text') ?></h5>
                        <div class="border p-3 bg-light">
                            <?= nl2br(h($emailTemplate->body_text)); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>