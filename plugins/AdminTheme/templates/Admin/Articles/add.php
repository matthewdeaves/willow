<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 * @var \Cake\Collection\CollectionInterface|string[] $users
 * @var \Cake\Collection\CollectionInterface|string[] $images
 * @var \Cake\Collection\CollectionInterface|string[] $tags
 */
?>
<?= $this->element('editors/chooser') ?>
<?php $kind = $this->request->getQuery('kind'); ?>
<div class="container mt-4">
    <div class="row">
        <?php
            echo $this->element('actions_card', [
                'modelName' => ($kind == 'page') ? 'Page' : 'Article',
                'controllerName' => 'Articles',
                'controllerIndexAction' => ($kind == 'page') ? 'tree-index' : 'index',
                'entity' => $article,
                'urlParams' => ($kind == 'page') ? ['kind' => 'page'] : [],
            ]);
        ?>

        <div class="col-md-9">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= ($kind == 'page') ? __('Add Page') : __('Add Post') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($article,
                    [
                        'type' => 'file',
                        'enctype' => 'multipart/form-data',
                        'class' => 'needs-validation', 'novalidate' => true
                    ]) ?>
                    <fieldset>
                        <?= $this->element('form/article', ['kind' => $kind]) ?>
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