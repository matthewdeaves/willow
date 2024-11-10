<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Tag $tag
 * @var \Cake\Collection\CollectionInterface|string[] $articles
 */
?>
<?= $this->Html->script('https://code.jquery.com/jquery-3.6.0.min.js'); ?>
<div class="container mt-4">
    <div class="row">
        <?php
        echo $this->element('actions_card', [
            'modelName' => 'Tag',
            'controllerName' => 'Tags',
            'entity' => $tag,
            'entityDisplayName' => $tag->title
        ]);
        ?>
        <div class="col-md-9">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Add Tag') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($tag, ['type' => 'file', 'class' => 'needs-validation', 'novalidate' => true, 'enctype' => 'multipart/form-data']) ?>
                    <fieldset>
                        <?= $this->element('tag_form_fields') ?>
                        <?= $this->element('seo_form_fields', ['hideWordCount' => true]) ?>                                              
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
    $('#articles-select').selectpicker({
        liveSearch: true,
        actionsBox: true,
        selectedTextFormat: 'count > 3'
    });
});
</script>