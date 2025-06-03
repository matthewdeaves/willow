<?php
/**
 * @var \App\View\AppView $this
 */
?>
<?= $this->Html->css('https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/basic.min.css') ?>
<?= $this->Html->script('https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js') ?>
<?php // CSRF Token will be passed via data-attribute on the form ?>

<?php
    echo $this->element('actions_card', [
        'modelName' => 'Image',
        'controllerName' => 'Images',
        'entity' => null,
        'entityDisplayName' => __('Bulk Upload Images')
    ]);
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= __('Bulk Upload Images') ?></h3>
                </div>
                <div class="card-body">
                    <div id="upload-notifications" class="mb-3"></div>
                    <form action="<?= $this->Url->build(['controller' => 'Images', 'action' => 'bulkUpload']) ?>"
                          class="dropzone"
                          id="imageUploadDropzone"
                          data-upload-url="<?= $this->Url->build(['controller' => 'Images', 'action' => 'bulkUpload']) ?>"
                          data-delete-url="<?= $this->Url->build(['controller' => 'Images', 'action' => 'deleteUploadedImage']) ?>"
                          data-csrf-token="<?= $this->request->getAttribute('csrfToken') ?>">
                        <div class="fallback">
                            <input name="file" type="file" multiple />
                        </div>
                    </form>
                    <div class="mt-3">
                        <button id="refreshPageButton" class="btn btn-info" style="display:none;"><?= __('Done - Refresh Page') ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->Html->script('AdminTheme.image_bulk_upload') // Reference the script within the plugin ?>