<!-- templates/Users/register.php -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Register</h3>
                </div>
                <div class="card-body">
                    <?= $this->Flash->render() ?>
                    <?= $this->Form->create($user, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    <fieldset>
                        <legend class="text-center mb-4"><?= __('Register New Account') ?></legend>
                        <div class="mb-3">
                            <?= $this->Form->control('email', [
                                'required' => true,
                                'class' => 'form-control',
                                'placeholder' => 'Enter your email'
                            ]) ?>
                        </div>
                        <div class="mb-3">
                            <?= $this->Form->control('password', [
                                'required' => true,
                                'class' => 'form-control',
                                'placeholder' => 'Enter your password'
                            ]) ?>
                        </div>
                        <div class="mb-3">
                            <?= $this->Form->control('confirm_password', [
                                'type' => 'password',
                                'required' => true,
                                'class' => 'form-control',
                                'placeholder' => 'Confirm your password'
                            ]) ?>
                        </div>
                    </fieldset>
                    <?= $this->Form->button(__('Register'), ['class' => 'btn btn-primary w-100']) ?>
                    <?= $this->Form->end() ?>
                    <p class="mt-3 text-center">
                        <?= $this->Html->link("Already have an account? Login", ['action' => 'login'], ['class' => 'text-primary']) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>