<?php
/**
 * @var \App\View\AppView $this
 */
?>
<?= $this->Html->css('https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/basic.min.css') ?>
<?= $this->Html->script('https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js') ?>
<meta name="csrfToken" content="<?= $this->request->getAttribute('csrfToken') ?>">

<div class="container-fluid mt-4">
    <div class="row">
        <?php
        echo $this->element('actions_card', [
            'modelName' => 'Image',
            'controllerName' => 'Images',
            'entity' => null,
            'entityDisplayName' => __('Bulk Upload Images')
        ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= __('Bulk Upload Images') ?></h3>
                </div>
                <div class="card-body">
                    <form action="<?= $this->Url->build(['action' => 'bulkUpload']) ?>" class="dropzone" id="imageUploadDropzone">
                        <div class="fallback">
                            <input name="file" type="file" multiple />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
Dropzone.options.imageUploadDropzone = {
    paramName: "image",
    maxFilesize: 5,
    maxFiles: 30,
    acceptedFiles: `image/*`,
    headers: {
        'X-CSRF-Token': document.querySelector('meta[name="csrfToken"]').getAttribute('content')
    },
    init: function() {
        this.on("complete", function(file) {
            if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                location.reload();
            }
        });
        this.on("error", function(file, errorMessage) {
            console.error("Upload error:", errorMessage);
            alert("Error uploading file: " + errorMessage);
        });
    },
    success: function(file, response) {
        if (response.success) {
            console.log("Upload success:", response.message);
        } else {
            console.error("Upload failed:", response.message);
            if (response.errors) {
                console.error("Validation errors:", response.errors);
            }
        }
    }
};
</script>