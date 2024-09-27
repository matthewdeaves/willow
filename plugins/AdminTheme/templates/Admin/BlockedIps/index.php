<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\BlockedIp> $blockedIps
 */
?>
<div class="blockedIps index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><?= __('Blocked IPs') ?></h3>
        <?= $this->Html->link(__('New Blocked IP'), ['action' => 'add'], ['class' => 'btn btn-primary my-3 ms-2']) ?>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th><?= $this->Paginator->sort('ip_address') ?></th>
                    <th><?= $this->Paginator->sort('blocked_at') ?></th>
                    <th><?= $this->Paginator->sort('expires_at') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blockedIps as $blockedIp): ?>
                <tr>
                    <td><?= h($blockedIp->ip_address) ?></td>
                    <td><?= $blockedIp->blocked_at ? h($blockedIp->blocked_at->format('Y-m-d H:i')) : '' ?></td>
                    <td><?= $blockedIp->expires_at ? h($blockedIp->expires_at->format('Y-m-d H:i')) : '' ?></td>
                    <td><?= $blockedIp->created ? h($blockedIp->created->format('Y-m-d H:i')) : '' ?></td>
                    <td><?= $blockedIp->modified ? h($blockedIp->modified->format('Y-m-d H:i')) : '' ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $blockedIp->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $blockedIp->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $blockedIp->id], ['confirm' => __('Are you sure you want to delete IP {0}?', $blockedIp->ip_address), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('pagination', ['recordCount' => count($blockedIps)]) ?>
</div>