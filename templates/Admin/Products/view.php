<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 */
?>
<?php
echo $this->element('actions_card', [
    'modelName' => 'Product',
    'controllerName' => 'Products',
    'entity' => $product,
    'entityDisplayName' => $product->name
]);
?>
<div class="container my-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($product->name) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($product->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Category Rating') ?></th>
                            <td><?= h($product->category_rating) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Product Id') ?></th>
                            <td><?= $this->Number->format($product->product_id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Price Usd') ?></th>
                            <td><?= $product->price_usd === null ? '' : $this->Number->format($product->price_usd) ?></td>
                        </tr>
                    </table>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Comments') ?></h5>
                            <p class="card-text"><?= html_entity_decode($product->comments); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>