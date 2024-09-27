<?= $this->element('trumbowyg'); ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 * @var string[]|\Cake\Collection\CollectionInterface $users
 * @var string[]|\Cake\Collection\CollectionInterface $tags
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $article->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $article->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Articles'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="articles form content">
            <?= $this->Form->create($article) ?>
            <fieldset>
                <legend><?= __('Edit Article') ?></legend>
                <?php
                    echo $this->Form->control('user_id', ['options' => $users]);
                    echo $this->Form->control('title');
                    echo $this->Form->control('slug');
                    echo $this->Form->control('body', [
                        'type' => 'textarea',
                        'id' => 'article-body',
                        'rows' => '10'
                    ]);
                    // Check if 'parent_id' is set in the URL parameters
                    $parentId = $this->request->getQuery('parent_id');
                    if ($this->request->getQuery('is_page') || $parentId) {
                        echo $this->Form->control('parent_id', [
                            'type' => 'select',
                            'options' => $parentArticles,
                            'empty' => __('Select a parent'),
                            'default' => $parentId
                        ]);
                    }
                    echo $this->Form->control('tags._ids', ['options' => $tags]);
                    echo $this->element('seo_form_fields');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
