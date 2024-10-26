<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Internationalisation> $internationalisations
 */
?>
<?php foreach ($internationalisations as $internationalisation): ?>
<tr>
    <td><?= h($internationalisation->locale) ?></td>
    <td><?= h($internationalisation->message_id) ?></td>
    <td><?= h($internationalisation->message_str) ?></td>
    <td><?= h($internationalisation->created_at) ?></td>
    <td><?= h($internationalisation->updated_at) ?></td>
    <td class="actions">
        <?= $this->Html->link(__('View'), ['action' => 'view', $internationalisation->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $internationalisation->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $internationalisation->id], ['confirm' => __('Are you sure you want to delete {0}?', [$internationalisation->message_id]), 'class' => 'btn btn-sm btn-outline-danger']) ?>
    </td>
</tr>
<?php endforeach; ?>