<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 * @var string[]|\Cake\Collection\CollectionInterface $users
 * @var string[]|\Cake\Collection\CollectionInterface $images
 * @var string[]|\Cake\Collection\CollectionInterface $tags
 */
?>
<?= $this->element('trumbowyg'); ?>
<div class="container mt-4">
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
            <div class="card mb-4">
                <div class="card-header">
                    <?php
                        $isPage = $this->request->getQuery('kind') == 'page';
                        $headerText = $isPage ? __('Edit Page') : __('Edit Article');
                    ?>
                    <h5 class="card-title"><?= $headerText ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($article,
                    [
                        'type' => 'file',
                        'enctype' => 'multipart/form-data',
                        'class' => 'needs-validation', 'novalidate' => true
                    ]) ?>
                    <fieldset>
                        <?= $this->element('article_form_fields') ?>
                        <?= $this->element('seo_form_fields') ?>                                          
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