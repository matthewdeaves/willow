<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<?php use App\Utility\SettingsManager; ?>
<div class="container my-4">
    <div class="row">
        <?php
        echo $this->element('actions_card', [
            'modelName' => 'User',
            'controllerName' => 'Users',
            'entity' => $user,
            'entityDisplayName' => $user->username
        ]);
        ?>
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($user->username) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Email') ?></th>
                            <td><?= h($user->email) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Username') ?></th>
                            <td><?= h($user->username) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Image') ?></th>
                            <td>
                                <?php if (!empty($user->picture)): ?>
                                    <div class="mb-3">
                                    <?= $this->Html->image(SettingsManager::read('ImageSizes.teeny', '200') . '/' . $user->picture, 
                                        [
                                            'pathPrefix' => 'files/Users/picture/',
                                            'alt' => $user->alt_text,
                                            'class' => 'img-thumbnail',
                                            'data-bs-toggle' => 'popover',
                                            'data-bs-trigger' => 'hover',
                                            'data-bs-html' => 'true',
                                            'data-bs-content' => $this->Html->image(SettingsManager::read('ImageSizes.extraLarge', '400') . '/' . $user->picture,
                                                ['pathPrefix' => 'files/Users/picture/',
                                                'alt' => $user->alt_text,
                                                'class' => 'img-fluid',
                                                'style' => 'max-width: 300px; max-height: 300px;'
                                        ])]) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($user->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($user->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is Admin') ?></th>
                            <td><?= $user->is_admin ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-warning">' . __('No') . '</span>'; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Active') ?></th>
                            <td><?= $user->active ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                    </table>

                    <?= $this->element('related/articles', ['articles' => $user->articles, 'hideColumns' => ['User']]) ?>

                    <?= $this->element('related/comments', ['comments' => $user->comments, 'model' => $user, 'hideColumns' => ['User']]) ?>

                </div>
            </div>
        </div>
    </div>
</div>