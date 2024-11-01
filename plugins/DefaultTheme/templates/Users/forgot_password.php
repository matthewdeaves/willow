<div class="users forgot-password">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= __('Forgot Password') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->Flash->render() ?>
                    <?= $this->Form->create(null, ['url' => ['_name' => 'forgot-password'], 'class' => 'needs-validation', 'novalidate' => true]) ?>
                    <fieldset>
                        <legend class="text-center mb-4"><?= __('Please enter your email to reset your password') ?></legend>
                        <div class="mb-3">
                            <?= $this->Form->control('email', [
                                'required' => true,
                                'class' => 'form-control',
                                'placeholder' => 'Enter your email',
                                'label' => false
                            ]) ?>
                        </div>
                    </fieldset>
                    <?= $this->Form->submit(__('Send Reset Link'), ['class' => 'btn btn-primary w-100']) ?>
                    <?= $this->Form->end() ?>
                </div>
                <div class="card-footer text-center">
                    <?= $this->Html->link(__('Back to Login'), ['action' => 'login'], ['class' => 'text-muted']) ?>
                </div>
            </div>
        </div>
    </div>
</div>