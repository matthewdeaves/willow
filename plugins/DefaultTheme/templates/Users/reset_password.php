<div class="users reset-password">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= __('Reset Password') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->Flash->render() ?>
                    <?= $this->Form->create($user, ['url' => ['action' => 'resetPassword', $confirmationCode], 'class' => 'needs-validation', 'novalidate' => true]) ?>
                    <fieldset>
                        <legend class="text-center mb-4"><?= __('Please enter your new password') ?></legend>
                        <div class="mb-3">
                            <?= $this->Form->control('password', [
                                'required' => true,
                                'class' => 'form-control',
                                'placeholder' => 'Enter your new password',
                                'label' => false,
                                'value' => '',
                            ]) ?>
                        </div>
                        <div class="mb-3">
                            <?= $this->Form->control('password_confirm', [
                                'type' => 'password',
                                'required' => true,
                                'class' => 'form-control',
                                'placeholder' => 'Confirm your new password',
                                'label' => false,
                                'value' => '',
                            ]) ?>
                        </div>
                    </fieldset>
                    <?= $this->Form->submit(__('Reset Password'), ['class' => 'btn btn-primary w-100']) ?>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>