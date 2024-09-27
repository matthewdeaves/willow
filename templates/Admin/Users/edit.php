<?php use Cake\Core\Configure; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="row">
    <div class="column column-80">
        <div class="users form content">
            <?= $this->Form->create($user, ['type' => 'file']) ?>
                <fieldset>
                    <legend><?= __('Edit User') ?></legend>
                    <?php
                        echo $this->Form->control('username');
                        echo $this->Form->control('password', [
                            'type' => 'password',
                            'value' => '', // Set the password field to be blank by default
                            'autocomplete' => 'new-password' // Optional: Prevent browsers from autofilling the password
                        ]);
                        echo $this->Form->control('email');
                        if ($currentUserID && $currentUserID != $user->id) {
                            echo $this->Form->control('is_admin', [
                                'type' => 'checkbox',
                                'label' => 'Administrator'
                            ]);
                            echo $this->Form->control('is_disabled', [
                                'type' => 'checkbox',
                                'label' => 'Disabled'
                            ]);
                        }
                        echo $this->Form->control('profile', ['type' => 'file', 'label' => 'Upload Profile Picture']);
                    ?>
                        <?php if (!empty($user->profile)): ?>
                            <?= $this->Html->image($user->profile . '_' . Configure::read('ImageSizes.large'), ['pathPrefix' => 'files/Users/profile/', 'alt' => 'Profile Picture']) ?>
                        <?php endif; ?>
                </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
