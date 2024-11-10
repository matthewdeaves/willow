<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\BlockedIp> $blockedIps
 */
?>
<?php foreach ($blockedIps as $blockedIp): ?>
    <tr>
        <td><?= h($blockedIp->ip_address) ?></td>
        <td><?= h($blockedIp->reason) ?></td>
        <td><?= h($blockedIp->blocked_at) ?></td>
        <td><?= h($blockedIp->expires_at) ?></td>
        <td>
            <div class="btn-group w-100 align-items-center justify-content-between flex-wrap">
                <div class="dropdown">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <?= __('Actions') ?>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <?= $this->Html->link(__('View'), ['action' => 'view', $blockedIp->id], ['class' => 'dropdown-item']) ?>
                    </li>
                    <li>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $blockedIp->id], ['class' => 'dropdown-item']) ?>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $blockedIp->id], ['confirm' => __('Are you sure you want to delete # {0}?', $blockedIp->id), 'class' => 'dropdown-item text-danger']) ?>
                    </li>
                </ul>
                </div>
            </div>
        </td>
    </tr>
<?php endforeach; ?>