<?php use App\Utility\SettingsManager; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Product> $products
 * @var array $tags
 * @var string|null $selectedTag
 */
?>


<!-- * Template for displaying a list of products with their titles, metadata, and summaries.
* Each product is linked to its detailed view.
* 
* @var \App\View\AppView $this
* @var iterable<\App\Model\Entity\Product> $products
* @var array $tags
* @var string|null $selectedTag -->

<?php foreach ($products as $product): ?>
<product class="product-list-item mb-4">
    <a class="text-decoration-none" href="<?= $this->Url->build(['_name' => $product->kind . '-by-slug', 'slug' => $product->slug]) ?>">
        <h2 class="product-title h4 link-body-emphasis mb-2"><?= htmlspecialchars_decode($product->title) ?></h2>
    </a>
    
    <div class="product-meta mb-3">
        <span class="date"><?= $product->published->format('F j, Y') ?></span> • 
        <span class="author"><?= h($product->user->username) ?></span>
    </div>
    
    <?php $displayMode = SettingsManager::read('Blog.productDisplayMode', 'summary') ?>
    <div class="product-wrap-container">
        <?php if (!empty($product->image)): ?>
        <div class="product-image-container">
            <a href="<?= $this->Url->build(['_name' => $product->kind . '-by-slug', 'slug' => $product->slug]) ?>">
                <?= $this->element('image/icon', [
                    'model' => $product, 
                    'icon' => $product->extraLargeImageUrl, 
                    'preview' => false,
                    'class' => 'product-wrap-image'
                ]); ?>
            </a>
        </div>
        <?php endif; ?>
        <div class="product-text-wrap">
            <?php if ($displayMode == 'lede') : ?>
                <p><?= htmlspecialchars_decode($product->lede) ?></p>
            <?php elseif ($displayMode == 'summary') : ?>
                <p><?= htmlspecialchars_decode($product->summary) ?></p>
            <?php elseif ($displayMode == 'body') : ?>
                <div><?php
                    $content = $product->body;
                    $content = $this->Video->processVideoPlaceholders($content);
                    $content = $this->Gallery->processGalleryPlaceholders($content);
                    echo htmlspecialchars_decode($content);
                ?></div>
            <?php endif; ?>
            <div class="read-more-container">
                <a href="<?= $this->Url->build(['_name' => $product->kind . '-by-slug', 'slug' => $product->slug]) ?>" class="read-more-link">
                    <?= __('Read more') ?>
                </a>
            </div>
        </div>
    </div>
    
    <hr class="product-separator" />
</product>
<?php endforeach; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl, {
            container: 'body'
        })
    })
});
</script>