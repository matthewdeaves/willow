<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Aiprompt> $aiprompts
 */
?>
<?php foreach ($aiprompts as $aiprompt): ?>
    <tr>
        <td><?= h($aiprompt->task_type) ?></td>
        <td><?= h($aiprompt->model) ?></td>
        <td><?= $this->Number->format($aiprompt->max_tokens) ?></td>
        <td><?= $this->Number->format($aiprompt->temperature) ?></td>
        <td><?= h($aiprompt->created_at) ?></td>
        <td><?= h($aiprompt->modified_at) ?></td>
        <td>
            <div class="btn-group w-100 align-items-center justify-content-between flex-wrap">
                <div class="dropdown">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <?= __('Actions') ?>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <?= $this->Html->link(__('View'), ['action' => 'view', $aiprompt->id], ['class' => 'dropdown-item']) ?>
                    </li>
                    <li>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $aiprompt->id], ['class' => 'dropdown-item']) ?>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $aiprompt->id], ['confirm' => __('Are you sure you want to delete {0}?', $aiprompt->task_type), 'class' => 'dropdown-item text-danger']) ?>
                    </li>
                </ul>
                </div>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>