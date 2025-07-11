<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Product> $products
 */
?>
<?php if (empty($products)): ?>
    <?= $this->element('empty_state', [
        'type' => 'search',
        'title' => __('No Products found'),
        'message' => __('Try adjusting your search terms or filters.')
    ]) ?>
<?php else: ?>
    <table class="table table-striped table-hover">
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
<?php endif; ?>

