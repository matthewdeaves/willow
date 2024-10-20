<?php use App\Utility\SettingsManager; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <?php
            echo $this->element('actions_card', [
                'modelName' => 'User',
                'controllerName' => 'Users',
                'entity' => $user
            ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= __('Edit User') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($user, ['type' => 'file', 'class' => 'needs-validation', 'novalidate' => true]) ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('username', [
                                'class' => 'form-control' . ($this->Form->isFieldError('username') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('email', [
                                'class' => 'form-control' . ($this->Form->isFieldError('email') ? ' is-invalid' : ''),
                                'required' => true,
                                'type' => 'email'
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('password', [
                                'type' => 'password',
                                'class' => 'form-control' . ($this->Form->isFieldError('password') ? ' is-invalid' : ''),
                                'value' => '',
                                'autocomplete' => 'new-password',
                                'required' => true
                            ]) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('confirm_password', [
                                'type' => 'password',
                                'class' => 'form-control' . ($this->Form->isFieldError('confirm_password') ? ' is-invalid' : ''),
                                'value' => '',
                                'autocomplete' => 'new-password',
                                'required' => true,
                                'label' => __('Confirm Password')
                            ]) ?>
                        </div>
                        <?php if ($this->Identity->get('id') != $user->id): ?>
                            <div class="col-md-6 mb-3">
                                <?= $this->Form->control('is_admin', [
                                    'type' => 'checkbox',
                                    'label' => 'Administrator',
                                    'class' => 'form-check-input'
                                ]) ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <?= $this->Form->control('is_disabled', [
                                    'type' => 'checkbox',
                                    'label' => 'Disabled',
                                    'class' => 'form-check-input'
                                ]) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('picture', [
                                'type' => 'file',
                                'class' => 'form-control' . ($this->Form->isFieldError('picture') ? ' is-invalid' : ''),
                                'label' => 'Upload Profile Picture'
                            ]) ?>
                        </div>
                    </div>
                    <?php if (!empty($user->picture)): ?>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <?= $this->Html->image(SettingsManager::read('ImageSizes.large', '400') . '/' . $user->picture, 
                                    ['pathPrefix' => 'files/Users/picture/', 'alt' => $user->alt_text, 'class' => 'img-fluid']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mt-4 mb-3">
                                <?= $this->Form->button(__('Update User'), [
                                    'class' => 'btn btn-primary'
                                ]) ?>
                            </div>
                        </div>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>