<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 */
?>
<?php
// Only show actions if we have an entity (edit mode)
if (!$product->isNew()) {
    echo $this->element('actions_card', [
        'modelName' => 'Product',
        'controllerName' => 'Products',
        'entity' => $product,
        'entityDisplayName' => $product->name
    ]);
}
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Add Product') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($product,
                    [
                        'type' => 'file',
                        'enctype' => 'multipart/form-data',
                        'class' => 'needs-validation', 'novalidate' => true
                    ]) ?>
                    <fieldset>
                    <div class="mb-3">
                            <?php echo $this->Form->control('name', ['class' => 'form-control' . ($this->Form->isFieldError('name') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('name')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('name') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('price_usd', ['class' => 'form-control' . ($this->Form->isFieldError('price_usd') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('price_usd')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('price_usd') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('category_rating', ['class' => 'form-control' . ($this->Form->isFieldError('category_rating') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('category_rating')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('category_rating') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('comments', ['class' => 'form-control' . ($this->Form->isFieldError('comments') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('comments')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('comments') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                                                                                    
                    </fieldset>
                    <div class="form-group">
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>