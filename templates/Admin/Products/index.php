<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Product> $products
 */

// Load search utility scripts
$this->Html->script('AdminTheme.utils/search-handler', ['block' => true]);
$this->Html->script('AdminTheme.utils/popover-manager', ['block' => true]); 
?>

<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <!-- Status Filter -->
            <?= $this->element('status_filter', [
                'filters' => [
                    'all' => ['label' => __('All'), 'params' => []],
                    'filter1' => ['label' => __('Filter 1'), 'params' => ['status' => '0']],
                    'filter2' => ['label' => __('Filter 2'), 'params' => ['status' => '1']],
                ]
            ]) ?>
            
            <!-- Search Form -->
            <?= $this->element('search_form', [
                'id' => 'product-search-form',
                'inputId' => 'productSearch',
                'placeholder' => __('Search Products...'),
                'class' => 'd-flex me-3 flex-grow-1'
            ]) ?>
        </div>
        
        <div class="flex-shrink-0">
            <?= $this->Html->link(
                '<i class="fas fa-plus"></i> ' . __('New Product'),
                ['action' => 'add'],
                ['class' => 'btn btn-success', 'escape' => false]
            ) ?>
        </div>
    </div>
</header>
<div id="ajax-target">
  <table class="table table-striped">
    <thead>
        <tr>
                  <th scope="col"><?= $this->Paginator->sort('product_id') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('name') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('price_usd') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('category_rating') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('comments') ?></th>
                  <th scope="col"><?= __('Actions') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $product): ?>
        <tr>
                                                <td><?= $this->Number->format($product->product_id) ?></td>
                                                            <td><?= h($product->name) ?></td>
                                                            <td><?= $product->price_usd === null ? '' : $this->Number->format($product->price_usd) ?></td>
                                                            <td><?= h($product->category_rating) ?></td>
                                                            <td><?= h($product->comments) ?></td>
                                    <td>
              <?= $this->element('evd_dropdown', ['model' => $product, 'display' => 'name']); ?>
            </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  
  <?= $this->element('pagination') ?>
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
// Initialize search functionality using AdminTheme utility
AdminTheme.SearchHandler.init({
    searchInputId: 'productSearch',
    resultsContainerId: '#ajax-target',
    baseUrl: '<?= $this->Url->build(['action' => 'index']) ?>',
    debounceDelay: 300
});
<?php $this->Html->scriptEnd(); ?>

