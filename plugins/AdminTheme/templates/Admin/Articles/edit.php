<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 * @var string[]|\Cake\Collection\CollectionInterface $users
 * @var string[]|\Cake\Collection\CollectionInterface $tags
 */
?>
<?php use App\Utility\SettingsManager; ?>
<?= $this->element('trumbowyg'); ?>
<div class="container-fluid mt-4">
    <div class="row">
        <?php
            echo $this->element('actions_card', [
                'modelName' => 'Article',
                'controllerName' => 'Articles',
                'entity' => $article,
                'entityDisplayName' => $article->title
            ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= __('Edit Article') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($article, ['type' => 'file', 'class' => 'needs-validation', 'novalidate' => true, 'enctype' => 'multipart/form-data']) ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('user_id', [
                                'options' => $users,
                                'class' => 'form-control' . ($this->Form->isFieldError('user_id') ? ' is-invalid' : ''),
                                'required' => true,
                                'default' => $this->Identity->get('id')
                            ]) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('title', [
                                'class' => 'form-control' . ($this->Form->isFieldError('title') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('slug', [
                                'class' => 'form-control' . ($this->Form->isFieldError('slug') ? ' is-invalid' : ''),
                                'required' => true,
                                'label' => ['class' => 'form-label']
                            ]) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->label('tags._ids', 'Tags', ['class' => 'form-label']) ?>
                            <?= $this->Form->select('tags._ids', $tags, [
                                'class' => 'form-select' . ($this->Form->isFieldError('tags._ids') ? ' is-invalid' : ''),
                                'multiple' => true,
                                'data-live-search' => 'true',
                                'data-actions-box' => 'true',
                                'id' => 'tags-select'
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <?= $this->Form->checkbox('is_published', [
                                    'class' => 'form-check-input'
                                ]) ?>
                                <?= $this->Form->label('is_published', 'Published', [
                                    'class' => 'form-check-label',
                                    'style' => 'margin-left: 5px;'
                                ]) ?>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <?php
                            // Check if 'parent_id' is set in the URL parameters
                            $parentId = $this->request->getQuery('parent_id');
                            if ($this->request->getQuery('is_page') || $parentId) {
                                echo $this->Form->control('parent_id', [
                                    'type' => 'select',
                                    'options' => $parentArticles,
                                    'empty' => __('Select a parent'),
                                    'default' => $parentId,
                                    'class' => 'form-control' . ($this->Form->isFieldError('parent_id') ? ' is-invalid' : '')
                                ]);
                            }
                            ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <?= $this->Form->control('body', [
                                'type' => 'textarea',
                                'id' => 'article-body',
                                'rows' => '10',
                                'class' => 'form-control' . ($this->Form->isFieldError('body') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('image', [
                                'type' => 'file',
                                'class' => 'form-control-file' . ($this->Form->isFieldError('image') ? ' is-invalid' : ''),
                                'label' => __('Main Picture')
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?php if (!empty($article->image)): ?>
                                <?= $this->Html->image(SettingsManager::read('ImageSizes.teeny', '200') . '/' . $article->image, 
                                    [
                                        'pathPrefix' => 'files/Articles/image/',
                                        'alt' => $article->alt_text,
                                        'class' => 'img-thumbnail',
                                        'data-bs-toggle' => 'popover',
                                        'data-bs-trigger' => 'hover',
                                        'data-bs-html' => 'true',
                                        'data-bs-content' => $this->Html->image(SettingsManager::read('ImageSizes.extra-large', '400') . '/' . $article->image,
                                            ['pathPrefix' => 'files/Articles/image/',
                                            'alt' => $article->alt_text,
                                            'class' => 'img-fluid',
                                            'style' => 'max-width: 300px; max-height: 300px;'
                                    ])]) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (SettingsManager::read('PagesAndArticles.additionalImages')) : ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->element('associated_images_control', [
                                'images' => $article->images,
                                'carouselId' => 'articleImagesCarousel'
                            ]) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?= $this->element('seo_form_fields', ['model' => $article]) ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mt-4 mb-3">
                                <?= $this->Form->button(__('Update Article'), [
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">

<script>
$(document).ready(function() {
    $('#tags-select').selectpicker({
        liveSearch: true,
        actionsBox: true,
        selectedTextFormat: 'count > 3'
    });
});
</script>