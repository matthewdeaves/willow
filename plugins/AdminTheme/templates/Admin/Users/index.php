<?php use Cake\Core\Configure; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $users
 */
?>
<div class="users index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><?= __('Users') ?></h3>
        <?= $this->Html->link(__('New User'), ['prefix' => 'Admin', 'action' => 'add'], ['class' => 'btn btn-primary my-3 ms-2']) ?>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th><?= __('Picture') ?></th>
                    <th><?= $this->Paginator->sort('username') ?></th>
                    <th><?= $this->Paginator->sort('email') ?></th>
                    <th><?= $this->Paginator->sort('is_admin', 'Admin') ?></th>
                    <th><?= $this->Paginator->sort('is_disabled', 'Enabled') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <div class="position-relative">
                            <?= $this->Html->image($user->picture_file . '_' . Configure::read('SiteSettings.ImageSizes.small'), 
                                ['pathPrefix' => 'files/Users/picture_file/', 
                                'alt' => 'Profile Picture', 
                                'class' => 'img-thumbnail', 
                                'width' => '50',
                                'data-bs-toggle' => 'popover',
                                'data-bs-trigger' => 'hover',
                                'data-bs-html' => 'true',
                                'data-bs-content' => $this->Html->image($user->picture_file . '_' . Configure::read('SiteSettings.ImageSizes.large'), 
                                    ['pathPrefix' => 'files/Users/picture_file/', 
                                    'alt' => 'Profile Picture', 
                                    'class' => 'img-fluid', 
                                    'style' => 'max-width: 300px; max-height: 300px;'])
                                ]) 
                            ?>
                        </div>
                    </td>
                    <td><?= h($user->username) ?></td>
                    <td><?= h($user->email) ?></td>
                    <td><?= $user->is_admin ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                    <td><?= $user->is_disabled ? '<span class="badge bg-danger">No</span>' : '<span class="badge bg-success">Yes</span>' ?></td>
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
            </tbody>
        </table>
    </div>
    <?= $this->element('pagination', ['recordCount' => count($users)]) ?>
</div>