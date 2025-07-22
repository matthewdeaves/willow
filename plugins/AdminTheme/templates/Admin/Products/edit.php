<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 * @var string[]|\Cake\Collection\CollectionInterface $users
 * @var string[]|\Cake\Collection\CollectionInterface $images
 * @var string[]|\Cake\Collection\CollectionInterface $tags
 */
?>
<?php use App\Utility\SettingsManager; ?>

<?php if(SettingsManager::read('Editing.editor') == 'trumbowyg') : ?>
<?= $this->Html->css('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/ui/trumbowyg.min.css'); ?>
<?= $this->Html->css('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/plugins/table/ui/trumbowyg.table.min.css'); ?>
<?= $this->Html->css('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/ui/trumbowyg.min.css'); ?>
<?= $this->Html->css('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/plugins/table/ui/trumbowyg.table.min.css'); ?>
<?= $this->Html->css('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/plugins/colors/ui/trumbowyg.colors.min.css'); ?>

<?= $this->Html->script('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/trumbowyg.min.js'); ?>
<?= $this->Html->script('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/plugins/colors/trumbowyg.colors.min.js'); ?>
<?= $this->Html->script('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/plugins/upload/trumbowyg.upload.min.js'); ?>
<?= $this->Html->script('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/plugins/table/trumbowyg.table.min.js'); ?>
<?= $this->Html->script('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/plugins/preformatted/trumbowyg.preformatted.min.js'); ?>

<?= $this->Html->script('AdminTheme.trumbowyg-edit') ?>
<?php endif; ?>

<?php if(SettingsManager::read('Editing.editor') == 'markdownit') : ?>
<?= $this->Html->script('AdminTheme.markdown-it-edit') ?>
<?php endif; ?>

<meta name="csrfToken" content="<?= $this->request->getAttribute('csrfToken') ?>">

<?php $kind = $this->request->getQuery('kind'); ?>
<?php
    echo $this->element('actions_card', [
<<<<<<< HEAD
        'modelName' => ($kind == 'page') ? 'Page' : 'Product',
        'controllerName' => 'Products',
        'controllerIndexAction' => ($kind == 'page') ? 'tree-index' : 'index',
        'entity' => $product,
        'entityDisplayName' => $product->title,
        'urlParams' => ($kind == 'page') ? ['kind' => 'page'] : [],
=======
        'modelName' => ($kind == 'product') ? 'Product' : 'Product',
        'controllerName' => 'Products',
        'controllerIndexAction' => ($kind == 'product') ? 'tree-index' : 'index',
        'entity' => $product,
        'entityDisplayName' => $product->title,
        'urlParams' => ($kind == 'product') ? ['kind' => 'product'] : [],
>>>>>>> e7397e3034035101febf4710cb40815e58d61f8e
    ]);
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= ($kind == 'page') ? __('Edit Page') : __('Edit Post') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($product,
                    [
                        'type' => 'file',
                        'enctype' => 'multipart/form-data',
                        'class' => 'needs-validation', 'novalidate' => true
                    ]) ?>
                    <fieldset>
                        <?= $this->element('form/product', ['kind' => $product->kind]) ?>
                        <?= $this->element('form/seo') ?>                                          
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
<?= $this->element('js/semanticui/dropdown', ['selector' => '#tags-select']); ?>