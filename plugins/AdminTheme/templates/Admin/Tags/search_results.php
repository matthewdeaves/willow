<?php use App\Utility\SettingsManager; ?>
<?php foreach ($tags as $tag): ?>
<tr>
    <td>
        <?php if (!empty($tag->image)) : ?>
            <div class="position-relative">
                <?= $this->Html->image(SettingsManager::read('ImageSizes.small', '200') . '/' . $tag->image, 
                    ['pathPrefix' => 'files/Tags/image/', 
                    'alt' => $tag->alt_text, 
                    'class' => 'img-thumbnail', 
                    'width' => '50',
                    'data-bs-toggle' => 'popover',
                    'data-bs-trigger' => 'hover',
                    'data-bs-html' => 'true',
                    'data-bs-content' => $this->Html->image(SettingsManager::read('ImageSizes.large', '400') . '/' . $tag->image, 
                        ['pathPrefix' => 'files/Tags/image/', 
                        'alt' => $tag->alt_text, 
                        'class' => 'img-fluid', 
                        'style' => 'max-width: 300px; max-height: 300px;'])
                    ]) 
                ?>
            </div>
        <?php endif; ?>
    </td>
    <td><?= h($tag->title) ?></td>
    <td><?= h($tag->slug) ?></td>
    <td class="actions">
        <?= $this->Html->link(__('View'), ['action' => 'view', $tag->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $tag->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $tag->id], ['confirm' => __('Are you sure you want to delete {0}?', $tag->title), 'class' => 'btn btn-sm btn-outline-danger']) ?>
    </td>
</tr>
<?php endforeach; ?>