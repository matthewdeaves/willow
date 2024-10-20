<?php foreach ($aiprompts as $aiprompt): ?>
<tr>
    <td><?= h($aiprompt->task_type) ?></td>
    <td><?= h($aiprompt->model) ?></td>
    <td><?= $this->Number->format($aiprompt->max_tokens) ?></td>
    <td><?= $this->Number->format($aiprompt->temperature) ?></td>
    <td><?= h($aiprompt->created_at->format('Y-m-d H:i')) ?></td>
    <td><?= h($aiprompt->modified_at->format('Y-m-d H:i')) ?></td>
    <td class="actions">
        <?= $this->Html->link(__('View'), ['action' => 'view', $aiprompt->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $aiprompt->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $aiprompt->id], ['confirm' => __('Are you sure you want to delete this prompt?'), 'class' => 'btn btn-sm btn-outline-danger']) ?>
    </td>
</tr>
<?php endforeach; ?>