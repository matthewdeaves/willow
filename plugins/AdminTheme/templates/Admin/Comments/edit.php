<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Comment $comment
 * @var string[]|\Cake\Collection\CollectionInterface $users
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <?php
            echo $this->element('actions_card', [
                'modelName' => 'Comment',
                'controllerName' => 'Comments',
                'entity' => $comment
            ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= __('Edit Comment') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($comment, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                        <?= $this->Form->control('display', [
                            'type' => 'select',
                            'options' => [
                                1 => 'Yes',
                                0 => 'No'
                            ],
                            'class' => 'form-control' . ($this->Form->isFieldError('display') ? ' is-invalid' : ''),
                            'required' => true,
                        ]) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('user_id', [
                                'options' => $users,
                                'class' => 'form-control' . ($this->Form->isFieldError('user_id') ? ' is-invalid' : ''),
                                'disabled' => true,
                                'readonly' => true
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <?= $this->Form->control('content', [
                                'type' => 'textarea',
                                'rows' => '5',
                                'class' => 'form-control' . ($this->Form->isFieldError('content') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mt-4 mb-3">
                                <?= $this->Form->button(__('Update Comment'), [
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