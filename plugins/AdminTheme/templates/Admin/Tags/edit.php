<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Tag $tag
 */
?>
<?php use App\Utility\SettingsManager; ?>
<div class="container-fluid mt-4">
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
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= __('Edit Tag') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($tag, ['type' => 'file', 'class' => 'needs-validation', 'novalidate' => true, 'enctype' => 'multipart/form-data']) ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('title', [
                                'class' => 'form-control' . ($this->Form->isFieldError('title') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('slug', [
                                'class' => 'form-control' . ($this->Form->isFieldError('slug') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <?= $this->Form->control('description', [
                                'type' => 'textarea',
                                'rows' => '3',
                                'class' => 'form-control' . ($this->Form->isFieldError('description') ? ' is-invalid' : ''),
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
                            <?php if (!empty($tag->image)): ?>
                                <?= $this->Html->image(SettingsManager::read('ImageSizes.teeny', '200') . '/' . $tag->image, 
                                    [
                                        'pathPrefix' => 'files/Tags/image/',
                                        'alt' => $tag->alt_text,
                                        'class' => 'img-thumbnail',
                                        'data-bs-toggle' => 'popover',
                                        'data-bs-trigger' => 'hover',
                                        'data-bs-html' => 'true',
                                        'data-bs-content' => $this->Html->image(SettingsManager::read('ImageSizes.extra-large', '400') . '/' . $tag->image,
                                            ['pathPrefix' => 'files/Tags/image/',
                                            'alt' => $tag->alt_text,
                                            'class' => 'img-fluid',
                                            'style' => 'max-width: 300px; max-height: 300px;'
                                    ])]) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?= $this->element('seo_form_fields', ['hideWordCount' => true]) ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mt-4 mb-3">
                                <?= $this->Form->button(__('Update Tag'), [
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
<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize popovers on page load
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
<?php $this->Html->scriptEnd(); ?>