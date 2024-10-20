<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\BlockedIp $blockedIp
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0"><?= __('Actions') ?></h4>
                </div>
                <div class="list-group list-group-flush">
                    <?= $this->Form->postLink(
                        __('Delete'),
                        ['action' => 'delete', $blockedIp->id],
                        ['confirm' => __('Are you sure you want to delete {0}?', $blockedIp->ip_address), 'class' => 'list-group-item list-group-item-action text-danger']
                    ) ?>
                    <?= $this->Html->link(__('List Blocked IPs'), ['action' => 'index'], ['class' => 'list-group-item list-group-item-action']) ?>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0"><?= __('Edit Blocked IP') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($blockedIp, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    <fieldset>
                        <div class="mb-3">
                            <?= $this->Form->control('ip_address', ['class' => 'form-control']) ?>
                        </div>
                        <div class="mb-3">
                            <?= $this->Form->control('reason', ['class' => 'form-control']) ?>
                        </div>
                        <div class="mb-3">
                            <?= $this->Form->control('blocked_at', ['class' => 'form-control']) ?>
                        </div>
                        <div class="mb-3">
                            <?= $this->Form->control('expires_at', ['class' => 'form-control']) ?>
                        </div>
                    </fieldset>
                    <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>