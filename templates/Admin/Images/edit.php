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
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $image->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $image->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Images'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        
            <div class="images form content">
            <?= $this->Form->create($image, ['type' => 'file']) ?>
            <fieldset>
                <legend><?= __('Edit Image') ?></legend>
                <?= $this->Form->control('name'); ?>
                <?php
                    echo $this->Form->control('path', ['type' => 'file']);
                ?>
                <?= $this->Html->image($image->path . '_' . Configure::read('ImageSizes.large'), 
                        ['pathPrefix' => 'files/Images/path/', 'alt' => 'Picture']) ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
