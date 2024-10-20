<?php use App\Utility\SettingsManager; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 * @var string $viewType
 */
?>
<div class="images index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><?= __('Images') ?></h3>
        <div>
            <?= $this->Html->link(__('New Image'), ['action' => 'add'], ['class' => 'btn btn-primary me-2']) ?>
            <?= $this->Html->link(__('Bulk Upload'), ['action' => 'bulkUpload'], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <div class="mb-3">
        <?= $this->Html->link(__('List View'), ['action' => 'index', '?' => ['view' => 'list']], ['class' => 'btn btn-outline-secondary']) ?>
        <?= $this->Html->link(__('Grid View'), ['action' => 'index', '?' => ['view' => 'grid']], ['class' => 'btn btn-secondary']) ?>
    </div>
    <div class="mb-3">
        <input type="text" id="imageSearch" class="form-control" placeholder="Search images...">
    </div>
    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-4" id="imageResults">
        <?php foreach ($images as $image): ?>
            <div class="col">
                <div class="card h-100">
                    <?= $this->Html->image(SettingsManager::read('ImageSizes.small', '200') . '/' . $image->file, 
                        ['pathPrefix' => 'files/Images/file/', 'alt' => $image->alt_text, 'class' => 'card-img-top']) ?>
                    <div class="card-body">
                        <h6 class="card-title"><?= h($image->name) ?></h6>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group btn-group-sm" role="group">
                            <?= $this->Html->link(__('View'), ['action' => 'view', $image->id], ['class' => 'btn btn-outline-primary']) ?>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $image->id], ['class' => 'btn btn-outline-secondary']) ?>
                            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $image->id], ['confirm' => __('Are you sure you want to delete {0}?', $image->name), 'class' => 'btn btn-outline-danger']) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?= $this->element('pagination', ['recordCount' => count($images)]) ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('imageSearch');
    const resultsContainer = document.getElementById('imageResults');

    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const searchTerm = this.value.trim();

            if (searchTerm.length > 0) {
                fetch(`<?= $this->Url->build(['action' => 'index']) ?>?search=${encodeURIComponent(searchTerm)}&view=grid`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    resultsContainer.innerHTML = html;
                })
                .catch(error => console.error('Error:', error));
            } else {
                location.reload();
            }
        }, 300);
    });
});
</script>