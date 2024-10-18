<?= $this->element('trumbowyg'); ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\EmailTemplate $emailTemplate
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <aside class="col-md-3">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h4 class="mb-0"><?= __('Actions') ?></h4>
                </div>
                <div class="card-body">
                    <?= $this->Html->link(__('List Email Templates'), ['action' => 'index'], ['class' => 'btn btn-secondary btn-block']) ?>
                </div>
            </div>
        </aside>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= __('Add Email Template') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($emailTemplate, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('name', [
                                'class' => 'form-control' . ($this->Form->isFieldError('name') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('subject', [
                                'class' => 'form-control' . ($this->Form->isFieldError('subject') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <?= $this->Form->control('body_html', [
                                'type' => 'textarea',
                                'id' => 'email-body-html',
                                'rows' => '10',
                                'class' => 'form-control trumbowyg-editor' . ($this->Form->isFieldError('body_html') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mt-4 mb-3">
                                <?= $this->Form->button(__('Submit'), [
                                    'class' => 'btn btn-primary'
                                ]) ?>
                            </div>
                        </div>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#email-body-html').trumbowyg();
});
</script>