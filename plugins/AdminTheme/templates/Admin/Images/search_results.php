<?php use Cake\Core\Configure; ?>
<?php foreach ($images as $image): ?>
<tr>
    <td>
        <div class="position-relative">
            <?= $this->Html->image($image->image_file . '_' . Configure::read('SiteSettings.ImageSizes.small'), 
                ['pathPrefix' => 'files/Images/image_file/', 'alt' => 'Picture', 'class' => 'img-thumbnail', 'width' => '50', 'data-bs-toggle' => 'popover', 'data-bs-trigger' => 'hover', 'data-bs-html' => 'true', 'data-bs-content' => $this->Html->image($image->image_file . '_' . Configure::read('SiteSettings.ImageSizes.large'), ['pathPrefix' => 'files/Images/image_file/', 'alt' => 'Picture', 'class' => 'img-fluid', 'style' => 'max-width: 300px; max-height: 300px;'])]) ?>
        </div>
    </td>
    <td><?= h($image->name) ?></td>
    <td><?= h($image->created->format('Y-m-d H:i')) ?></td>
    <td><?= h($image->modified->format('Y-m-d H:i')) ?></td>
    <td class="actions">
        <?= $this->Html->link(__('View'), ['action' => 'view', $image->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $image->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $image->id], ['confirm' => __('Are you sure you want to delete {0}?', $image->name), 'class' => 'btn btn-sm btn-outline-danger']) ?>
    </td>
</tr>
<?php endforeach; ?>