<?= $this->Html->script('https://code.jquery.com/jquery-3.6.0.min.js'); ?>
<?= $this->Html->css('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/ui/trumbowyg.min.css'); ?>
<?= $this->Html->css('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/plugins/table/ui/trumbowyg.table.min.css'); ?>
<?= $this->Html->script('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/trumbowyg.min.js'); ?>
<?= $this->Html->script('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/plugins/upload/trumbowyg.upload.min.js'); ?>
<?= $this->Html->script('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/plugins/table/trumbowyg.table.min.js'); ?>
<meta name="csrfToken" content="<?= $this->request->getAttribute('csrfToken') ?>">
<style>
.trumbowyg-modal-box {
    width: 1000px; /* Set your desired width */
    height: 700px; /* Set your desired height */
    overflow: auto; /* Add scroll if content exceeds modal size */
}
.images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); /* Adjust minmax values as needed */
    gap: 5px; /* Space between grid items */
}

.image-item {
    border: 1px solid #ccc; /* Optional: Add border */
    padding: 5px; /* Optional: Add padding */
    text-align: center; /* Center text */
}

.image-item img {
    max-width: 100%; /* Ensure images are responsive */
    height: auto; /* Maintain aspect ratio */
}
</style>
<SCRIPT>
    $(document).ready(function() {
        $('#article-body').trumbowyg({
            btnsDef: {
                // Create a new dropdown
                image: {
                    dropdown: ['insertImage', 'upload', 'selectImage'],
                    title: 'Insert, upload or select image',
                    ico: 'insertImage',
                    hasIcon: true
                },
                selectImage: {
                    fn: function() {
                        var $modal = $('#article-body').trumbowyg('openModal', {
                            title: 'Choose image',
                            content: '<div id="selectImageWindow"></div>',
                            fields: {}
                        });

                        $modal.on('tbwcancel', function(e){
                            $('#article-body').trumbowyg('closeModal');
                        });

                        $.ajax({
                            url: '/admin/images/trumbowygSelect',
                            method: 'GET',
                            success: function(data) {
                                $('#selectImageWindow').html(data);
                            },
                            error: function() {
                                alert('Failed to load content.');
                            }
                        });
                    },
                    title: 'Select image',
                    ico: 'insertImage',
                    hasIcon: true,
                    text: 'Select image',
                }
            },
            btns: [
                ['viewHTML'],
                ['formatting'],
                ['bold', 'italic', 'underline', 'strikethrough'],
                ['superscript', 'subscript'],
                ['link'],
                ['image'],
                ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                ['unorderedList', 'orderedList'],
                ['table'],
                ['tableCellBackgroundColor', 'tableBorderColor'],
                ['removeformat'],
                ['fullscreen'],
            ],
            plugins: {
                // Add imagur parameters to upload plugin for demo purposes
                upload: {
                    serverPath: '<?php echo $this->Url->build('/admin/images/trumbowygAdd',['fullBase' => true]); ?>',
                    fileFieldName: 'path',
                    headers: {
                        'X-CSRF-Token': $('meta[name="csrfToken"]').attr('content') // Include CSRF token
                    }
                }
            },

        });
    });
</SCRIPT>