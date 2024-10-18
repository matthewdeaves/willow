<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\EmailTemplate> $emailTemplates
 */
?>
<div class="emailTemplates index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><?= __('Email Templates') ?></h3>
        <div>
            <?= $this->Html->link(__('New Email Template'), ['action' => 'add'], ['class' => 'btn btn-primary my-3 ms-2']) ?>
            <?= $this->Html->link(__('Send Email'), ['action' => 'sendEmail'], ['class' => 'btn btn-secondary my-3 ms-2']) ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('subject') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($emailTemplates as $emailTemplate): ?>
                <tr>
                    <td><?= h($emailTemplate->name) ?></td>
                    <td><?= h($emailTemplate->subject) ?></td>
                    <td><?= h($emailTemplate->created->format('Y-m-d H:i')) ?></td>
                    <td><?= h($emailTemplate->modified->format('Y-m-d H:i')) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $emailTemplate->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $emailTemplate->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $emailTemplate->id], ['confirm' => __('Are you sure you want to delete {0}?', $emailTemplate->subject), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('pagination', ['recordCount' => count($emailTemplates)]) ?>
</div>