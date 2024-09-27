<?php use Cake\Core\Configure; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Image $image
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Image'), ['action' => 'edit', $image->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Image'), ['action' => 'delete', $image->id], ['confirm' => __('Are you sure you want to delete # {0}?', $image->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Images'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Image'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="images view content">
            <h3><?= h($image->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Image') ?></th>
                    <td>
                        <?= $this->Html->image($image->path . '_' . Configure::read('ImageSizes.large'), 
                        ['pathPrefix' => 'files/Images/path/', 'alt' => 'Picture']) ?>
                    </td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($image->id) ?></td>
                </tr>
                <tr>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($image->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($image->modified) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
