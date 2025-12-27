<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<?php if (!$user->isNew()): ?>
<?php
    echo $this->element('actions_card', [
        'modelName' => 'User',
        'controllerName' => 'Users',
        'entity' => $user,
        'entityDisplayName' => $user->username
    ]);
?>
<?php endif; ?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Add User') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($user,
                    [
                        'type' => 'file',
                        'enctype' => 'multipart/form-data',
                        'class' => 'needs-validation', 'novalidate' => true
                    ]) ?>
                    <fieldset>
                        <?= $this->element('form/field', ['name' => 'email']) ?>
                        <?= $this->element('form/field', ['name' => 'username']) ?>
                        <?= $this->element('form/field', ['name' => 'password']) ?>
                        <?= $this->element('form/field', ['name' => 'confirm_password', 'type' => 'password']) ?>
                        <?= $this->element('form/field', [
                            'name' => 'image',
                            'type' => 'file',
                            'label' => ['text' => __('Image'), 'class' => 'form-label'],
                            'inputOptions' => ['id' => 'customFile']
                        ]) ?>
                        <?= $this->element('form/field', ['name' => 'is_admin', 'type' => 'checkbox', 'label' => __('Admin')]) ?>
                        <?= $this->element('form/field', ['name' => 'active', 'type' => 'checkbox', 'inputOptions' => ['checked' => true]]) ?>
                    </fieldset>
                    <div class="form-group">
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>
