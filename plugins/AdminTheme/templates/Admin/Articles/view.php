<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <?php
        echo $this->element('actions_card', [
            'modelName' => 'Article',
            'controllerName' => 'Articles',
            'entity' => $article,
            'entityDisplayName' => $article->title
        ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= h($article->title) ?></h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th class="w-25"><?= __('User') ?></th>
                            <td>
                                <?php if (isset($article->user->username)): ?>
                                    <?= $this->Html->link(h($article->user->username), 
                                        ['controller' => 'Users',
                                        'action' => 'view',
                                        $article->user->id],
                                        ['class' => 'text-primary']) 
                                    ?>
                                <?php else: ?>
                                    <?= __('Unknown User') ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Title') ?></th>
                            <td><?= h($article->title) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Slug') ?></th>
                            <td>
                                <?php $ruleName = ($article->kind == 'article') ? 'article-by-slug' : 'page-by-slug';?>
                                <?php if ($article->is_published == true): ?>
                                    
                                    <?= $this->Html->link(
                                        $article->slug,
                                        [
                                            'controller' => 'Articles',
                                            'action' => 'view-by-slug',
                                            'slug' => $article->slug,
                                            '_name' => $ruleName,
                                        ],
                                        ['escape' => false]
                                    );
                                    ?>
                                <?php else: ?>
                                    <?= $this->Html->link(
                                        $article->slug,
                                        [
                                            'prefix' => 'Admin',
                                            'controller' => 'Articles',
                                            'action' => 'view',
                                            $article->id
                                        ],
                                        ['escape' => false]
                                    ) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Is Published') ?></th>
                            <td><?= $article->is_published ? __('Yes') : __('No') ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Published') ?></th>
                            <td><?= h($article->published) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($article->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($article->modified) ?></td>
                        </tr>
                    </table>
                    <div class="mt-4">
                        <h5><?= __('Summary') ?></h5>
                        <div class="border p-3 bg-light">
                            <?= $article->summary; ?>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h5><?= __('Body') ?></h5>
                        <div class="border p-3 bg-light">
                            <?= $article->body; ?>
                        </div>
                    </div>
                    <div class="mt-4">
                        <?= $this->element('seo_fields', ['model' => $article]) ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($article->tags)) : ?>
            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    <h4 class="mb-0"><?= __('Related Tags') ?></h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?= __('Title') ?></th>
                                    <th><?= __('Created') ?></th>
                                    <th><?= __('Modified') ?></th>
                                    <th class="actions"><?= __('Actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($article->tags as $tag) : ?>
                                <tr>
                                    <td><?= h($tag->title) ?></td>
                                    <td><?= h($tag->created) ?></td>
                                    <td><?= h($tag->modified) ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__('Live'), ['_name' => 'tag-by-slug', 'slug' => $tag->slug], ['class' => 'btn btn-sm btn-info']) ?>
                                        <?= $this->Html->link(__('View'), ['controller' => 'Tags', 'action' => 'view', $tag->id], ['class' => 'btn btn-sm btn-info']) ?>
                                        <?= $this->Html->link(__('Edit'), ['controller' => 'Tags', 'action' => 'edit', $tag->id], ['class' => 'btn btn-sm btn-primary']) ?>
                                        <?= $this->Form->postLink(__('Delete'), ['controller' => 'Tags', 'action' => 'delete', $tag->id], ['confirm' => __('Are you sure you want to delete {0}?', $tag->title), 'class' => 'btn btn-sm btn-danger']) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>