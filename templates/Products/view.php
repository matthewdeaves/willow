<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $product
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Product'), ['action' => 'edit', $product->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Product'), ['action' => 'delete', $product->id], ['confirm' => __('Are you sure you want to delete # {0}?', $product->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Products'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Product'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="products view content">
            <h3><?= h($product->title) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($product->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('User Id') ?></th>
                    <td><?= h($product->user_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Kind') ?></th>
                    <td><?= h($product->kind) ?></td>
                </tr>
                <tr>
                    <th><?= __('Title') ?></th>
                    <td><?= h($product->title) ?></td>
                </tr>
                <tr>
                    <th><?= __('Lede') ?></th>
                    <td><?= h($product->lede) ?></td>
                </tr>
                <tr>
                    <th><?= __('Slug') ?></th>
                    <td><?= h($product->slug) ?></td>
                </tr>
                <tr>
                    <th><?= __('Image') ?></th>
                    <td><?= h($product->image) ?></td>
                </tr>
                <tr>
                    <th><?= __('Alt Text') ?></th>
                    <td><?= h($product->alt_text) ?></td>
                </tr>
                <tr>
                    <th><?= __('Keywords') ?></th>
                    <td><?= h($product->keywords) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($product->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Dir') ?></th>
                    <td><?= h($product->dir) ?></td>
                </tr>
                <tr>
                    <th><?= __('Mime') ?></th>
                    <td><?= h($product->mime) ?></td>
                </tr>
                <tr>
                    <th><?= __('Meta Title') ?></th>
                    <td><?= h($product->meta_title) ?></td>
                </tr>
                <tr>
                    <th><?= __('Parent Id') ?></th>
                    <td><?= h($product->parent_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Size') ?></th>
                    <td><?= $product->size === null ? '' : $this->Number->format($product->size) ?></td>
                </tr>
                <tr>
                    <th><?= __('Word Count') ?></th>
                    <td><?= $product->word_count === null ? '' : $this->Number->format($product->word_count) ?></td>
                </tr>
                <tr>
                    <th><?= __('Lft') ?></th>
                    <td><?= $this->Number->format($product->lft) ?></td>
                </tr>
                <tr>
                    <th><?= __('Rght') ?></th>
                    <td><?= $this->Number->format($product->rght) ?></td>
                </tr>
                <tr>
                    <th><?= __('View Count') ?></th>
                    <td><?= $this->Number->format($product->view_count) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($product->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($product->modified) ?></td>
                </tr>
                <tr>
                    <th><?= __('Published') ?></th>
                    <td><?= h($product->published) ?></td>
                </tr>
                <tr>
                    <th><?= __('Featured') ?></th>
                    <td><?= $product->featured ? __('Yes') : __('No'); ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Published') ?></th>
                    <td><?= $product->is_published ? __('Yes') : __('No'); ?></td>
                </tr>
                <tr>
                    <th><?= __('Main Menu') ?></th>
                    <td><?= $product->main_menu ? __('Yes') : __('No'); ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Body') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($product->body)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Markdown') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($product->markdown)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Summary') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($product->summary)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Meta Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($product->meta_description)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Meta Keywords') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($product->meta_keywords)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Facebook Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($product->facebook_description)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Linkedin Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($product->linkedin_description)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Instagram Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($product->instagram_description)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Twitter Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($product->twitter_description)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>