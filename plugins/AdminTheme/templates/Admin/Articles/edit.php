<?= $this->element('trumbowyg'); ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 * @var string[]|\Cake\Collection\CollectionInterface $users
 * @var string[]|\Cake\Collection\CollectionInterface $tags
 */
?>
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
                    <?= $this->Form->create($article, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('user_id', [
                                'options' => $users,
                                'class' => 'form-control' . ($this->Form->isFieldError('user_id') ? ' is-invalid' : ''),
                                'required' => true
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
                                'required' => true
                            ]) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('tags._ids', [
                                'options' => $tags,
                                'class' => 'form-select' . ($this->Form->isFieldError('tags._ids') ? ' is-invalid' : ''),
                                'multiple' => true,
                                'data-live-search' => 'true',
                                'data-actions-box' => 'true',
                                'label' => 'Tags',
                                'id' => 'tags-select'
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('is_published', [
                                'type' => 'checkbox',
                                'label' => 'Published',
                                'class' => 'form-check-input'
                            ]) ?>
                        </div>
                        <div class="col-md-12 mb-3">
                            <?= $this->Form->control('images[]', [
                                'type' => 'file',
                                'label' => 'Upload Images',
                                'multiple' => true,
                                'class' => 'form-control-file',
                            ]) ?>
                        </div>
                    </div>
                    <?php if (isset($article) && !$article->isNew()): ?>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <h4>Current Images</h4>
                                <?php if (!empty($article->images)): ?>
                                    <?php foreach ($article->images as $image): ?>
                                        <div class="mb-2">
                                            <?= $this->Html->image('/files/Images/image_file/' . $image->image_file, ['width' => 100]) ?>
                                            <?= $this->Form->control('unlink_images[]', [
                                                'type' => 'checkbox',
                                                'label' => 'Unlink this image',
                                                'value' => $image->id,
                                            ]) ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No images associated with this article.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
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
                    <?php
                    // Check if 'parent_id' is set in the URL parameters or if the article already has a parent
                    $parentId = $this->request->getQuery('parent_id') ?? $article->parent_id;
                    if ($this->request->getQuery('is_page') || $parentId) {
                        echo '<div class="row"><div class="col-md-6 mb-3">';
                        echo $this->Form->control('parent_id', [
                            'type' => 'select',
                            'options' => $parentArticles,
                            'empty' => __('Select a parent'),
                            'default' => $parentId,
                            'class' => 'form-control' . ($this->Form->isFieldError('parent_id') ? ' is-invalid' : '')
                        ]);
                        echo '</div></div>';
                    }
                    ?>
                    <?= $this->element('seo_form_fields') ?>
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