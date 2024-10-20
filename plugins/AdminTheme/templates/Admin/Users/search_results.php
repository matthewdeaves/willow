<?php use App\Utility\SettingsManager; ?>
<?php foreach ($users as $user): ?>
<tr>
    <td>
        <div class="position-relative">
            <?= $this->Html->image(SettingsManager::read('ImageSizes.small', '200') . '/' . $user->picture, 
                ['pathPrefix' => 'files/Users/picture/', 
                'alt' => $user->alt_text, 
                'class' => 'img-thumbnail', 
                'width' => '50',
                'data-bs-toggle' => 'popover',
                'data-bs-trigger' => 'hover',
                'data-bs-html' => 'true',
                'data-bs-content' => $this->Html->image(SettingsManager::read('ImageSizes.large', '400') . '/' . $user->picture, 
                    ['pathPrefix' => 'files/Users/picture/', 
                    'alt' => $user->alt_text, 
                    'class' => 'img-fluid', 
                    'style' => 'max-width: 300px; max-height: 300px;'])
                ]) 
            ?>
        </div>
    </td>
    <td><?= h($user->username) ?></td>
    <td><?= h($user->email) ?></td>
    <td><?= $user->is_admin ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-secondary">' . __('No') . '</span>' ?></td>
    <td><?= $user->is_disabled ? '<span class="badge bg-danger">' . __('No') . '</span>' : '<span class="badge bg-success">' . __('Yes') . '</span>' ?></td>
    <td><?= h($user->created->format('Y-m-d H:i')) ?></td>
    <td><?= h($user->modified->format('Y-m-d H:i')) ?></td>
    <td class="actions">
        <?= $this->Html->link(__('View'), ['action' => 'view', $user->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $user->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?php if ($this->Identity->get('id') != $user->id) : ?>
            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $user->id], ['confirm' => __('Are you sure you want to delete {0}?', $user->username), 'class' => 'btn btn-sm btn-outline-danger']) ?>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>