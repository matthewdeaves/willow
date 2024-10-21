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
                    <h3 class="mb-0"><?= __('Add User') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($user, ['type' => 'file', 'class' => 'needs-validation']) ?>
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
                                'class' => 'form-control' . ($this->Form->isFieldError('password') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('confirm_password', [
                                'type' => 'password',
                                'class' => 'form-control' . ($this->Form->isFieldError('confirm_password') ? ' is-invalid' : ''),
                                'required' => true,
                                'label' => 'Confirm Password'
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('picture', [
                                'type' => 'file',
                                'class' => 'form-control-file' . ($this->Form->isFieldError('picture') ? ' is-invalid' : ''),
                                'label' => 'Upload Profile Picture'
                            ]) ?>
                        </div>
                    </div>
                    <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>