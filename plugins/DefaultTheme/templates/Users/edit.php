<?php use App\Utility\SettingsManager; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="users d-flex justify-content-center align-items-center">
    <div class="card mb-4 shadow-sm">
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
                    <?= $this->Form->control('image', ['type' => 'file', 'class' => 'form-control', 'label' => ['text' => 'Upload Profile Picture', 'class' => 'form-label']]) ?>
                </div>
                <?php if (!empty($user->image)): ?>
                    <div class="mb-3">
                        <?= $this->Html->image(SettingsManager::read('ImageSizes.large', '400') . '/' . $user->image, 
                            ['pathPrefix' => 'files/Users/image/',
                            'alt' => $user->alt_text,
                            'class' => 'img-thumbnail'
                        ]) ?>
                    </div>
                <?php endif; ?>
            </fieldset>
            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>