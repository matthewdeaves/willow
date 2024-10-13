<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Slug $slug
 * @var \Cake\Collection\CollectionInterface|string[] $articles
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <?php
        echo $this->element('actions_card', [
            'modelName' => 'Slug',
            'controllerName' => 'Slugs',
            'entity' => $slug,
            'entityDisplayName' => __('Add Slug')
        ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= __('Add Slug') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($slug, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('article_id', [
                                'options' => $articles,
                                'class' => 'form-control' . ($this->Form->isFieldError('article_id') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                            <?php if ($this->Form->isFieldError('article_id')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('article_id') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('slug', [
                                'class' => 'form-control' . ($this->Form->isFieldError('slug') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                            <?php if ($this->Form->isFieldError('slug')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('slug') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>