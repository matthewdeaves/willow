<!-- templates/Users/register.php -->
<div class="users form content">
    <?= $this->Form->create($user) ?>
    <fieldset>
        <legend><?= __('Register New Account') ?></legend>
        <?= $this->Form->control('email', ['required' => true]) ?>
        <?= $this->Form->control('password', ['required' => true]) ?>
        <?= $this->Form->control('confirm_password', ['type' => 'password', 'required' => true]) ?>
    </fieldset>
    <?= $this->Form->button(__('Register')); ?>
    <?= $this->Form->end() ?>
    <p><?= $this->Html->link("Already have an account? Login", ['action' => 'login']) ?></p>
</div>