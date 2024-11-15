<div id="imagePickerModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Select an Image</h2>
        <div class="image-gallery">
            <?php foreach ($images as $image): ?>
                <div class="image-item">
                    <img src="<?= $this->Html->image($image->image) ?>" alt="<?= $image->alt_text ?>" />
                    <button class="image-select" data-url="<?= $this->Url->build($image->image, true) ?>">Select</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>