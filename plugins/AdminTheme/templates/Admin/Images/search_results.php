<?php use App\Utility\SettingsManager; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 * @var string $viewType
 */
?>

<?php if ($viewType === 'grid'): ?>
    <?php foreach ($images as $image): ?>
        <div class="col">
            <div class="card h-100">
                <?= $this->Html->image(SettingsManager::read('ImageSizes.small', '200') . '/' . $image->file, 
                    ['pathPrefix' => 'files/Images/file/', 'alt' => $image->alt_text, 'class' => 'card-img-top']) ?>
                <div class="card-body">
                    <h6 class="card-title"><?= h($image->name) ?></h6>
                </div>
                <div class="card-footer">
                    <div class="btn-group btn-group-sm" role="group">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $image->id], ['class' => 'btn btn-outline-primary']) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $image->id], ['class' => 'btn btn-outline-secondary']) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $image->id], ['confirm' => __('Are you sure you want to delete {0}?', $image->name), 'class' => 'btn btn-outline-danger']) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <?php foreach ($images as $image): ?>
    <tr>
        <td>
            <div class="position-relative">
                <?= $this->Html->image(SettingsManager::read('ImageSizes.small', '200') . '/' . $image->file, 
                    [
                        'pathPrefix' => 'files/Images/file/',
                        'alt' => $image->alt_text,
                        'class' => 'img-thumbnail',
                        'width' => '50',
                        'data-bs-toggle' => 'popover',
                        'data-bs-trigger' => 'hover',
                        'data-bs-html' => 'true',
                        'data-bs-content' => $this->Html->image(SettingsManager::read('ImageSizes.large', '400') . '/' . $image->file,
                         [
                            'pathPrefix' => 'files/Images/file/',
                            'alt' => $image->alt_text,
                            'class' => 'img-fluid',
                            'style' => 'max-width: 300px; max-height: 300px;'
                        ])]) ?>
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
<?php endif; ?>