<?php use Cake\Core\Configure; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= __('Edit Your Account') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($user, ['type' => 'file', 'class' => 'needs-validation', 'novalidate']) ?>
                    <fieldset>
                        <div class="mb-3">
                            <?= $this->Form->control('username', ['class' => 'form-control', 'label' => ['class' => 'form-label']]) ?>
                        </div>
                        <div class="mb-3">
                            <?= $this->Form->control('password', [
                                'type' => 'password',
                                'value' => '',
                                'autocomplete' => 'new-password',
                                'class' => 'form-control',
                                'label' => ['class' => 'form-label']
                            ]) ?>
                        </div>
                        <div class="mb-3">
                            <?= $this->Form->control('email', ['class' => 'form-control', 'label' => ['class' => 'form-label']]) ?>
                        </div>
                        <div class="mb-3">
                            <?= $this->Form->control('profile', ['type' => 'file', 'class' => 'form-control', 'label' => ['text' => 'Upload Profile Picture', 'class' => 'form-label']]) ?>
                        </div>
                        <?php if (!empty($user->profile)): ?>
                            <div class="mb-3">
                                <?= $this->Html->image($user->profile . '_' . Configure::read('ImageSizes.large'), ['pathPrefix' => 'files/Users/profile/', 'alt' => 'Profile Picture', 'class' => 'img-thumbnail']) ?>
                            </div>
                        <?php endif; ?>
                    </fieldset>
                    <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>