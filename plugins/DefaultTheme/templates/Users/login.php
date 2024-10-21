<div class="users login">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Login</h3>
                </div>
                <div class="card-body">
                    <?= $this->Flash->render() ?>
                    <?= $this->Form->create(null, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    <fieldset>
                        <legend class="text-center mb-4"><?= __('Please enter your email and password') ?></legend>
                        <div class="mb-3">
                            <?= $this->Form->control('email', [
                                'required' => true,
                                'class' => 'form-control',
                                'placeholder' => 'Enter your email',
                                'label' => false
                            ]) ?>
                        </div>
                        <div class="mb-3">
                            <?= $this->Form->control('password', [
                                'required' => true,
                                'class' => 'form-control',
                                'placeholder' => 'Enter your password',
                                'label' => false
                            ]) ?>
                        </div>
                    </fieldset>
                    <?= $this->Form->submit(__('Login'), ['class' => 'btn btn-primary w-100']) ?>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>