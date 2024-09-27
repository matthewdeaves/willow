<div class="container mt-5">
    <h2 class="mb-4">Send Email</h2>
    <?= $this->Form->create(null, ['url' => ['action' => 'sendEmail'], 'class' => 'mb-4']) ?>
    <div class="form-group mb-4">
        <?= $this->Form->control('email_template_id', [
            'options' => $emailTemplates,
            'empty' => 'Select an email template',
            'class' => 'form-control',
            'label' => ['class' => 'mb-2', 'text' => 'Email Template']
        ]) ?>
    </div>
    <div class="form-group mb-4">
        <?= $this->Form->control('user_id', [
            'options' => $users,
            'empty' => 'Select a user',
            'class' => 'form-control',
            'label' => ['class' => 'mb-2', 'text' => 'Recipient']
        ]) ?>
    </div>
    <?= $this->Form->button(__('Send Email'), ['class' => 'btn btn-primary btn-lg']) ?>
    <?= $this->Form->end() ?>
</div>

<?= $this->Html->script('https://code.jquery.com/jquery-3.5.1.slim.min.js') ?>
<?= $this->Html->script('https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js') ?>
<?= $this->Html->script('https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js') ?>