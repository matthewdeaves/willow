<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Tag $tag
 */
?>
<div class="container my-4">
    <div class="row">
        <?php
        echo $this->element('actions_card', [
            'modelName' => 'Tag',
            'controllerName' => 'Tags',
            'entity' => $tag,
            'entityDisplayName' => $tag->title
        ]);
        ?>
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($tag->title) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Title') ?></th>
                            <td><?= h($tag->title) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Slug') ?></th>
                            <td><?= h($tag->slug) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($tag->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($tag->modified) ?></td>
                        </tr>
                    </table>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Description') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($tag->description)); ?></p>
                        </div>
                    </div>

                    <div class="mt-4">
                    <?= $this->element('seo_display_fields', ['model' => $tag, 'hideWordCount' => true]); ?>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title"><?= __('Related Articles/Pages') ?></h4>
                            <?php if (!empty($tag->articles)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('User') ?></th>
                                            <th><?= __('Kind') ?></th>
                                            <th><?= __('Title') ?></th>
                                            <th><?= __('Published') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tag->articles as $article) : ?>
                                        <tr>
                                            <?php $ruleName = ($article->kind == 'article') ? 'article-by-slug' : 'page-by-slug';?>
                                            <td>
                                                <?= $article->hasValue('user') ? $this->Html->link($article->user->username, ['controller' => 'Users', 'action' => 'view', $article->user->id], ['class' => 'btn btn-link']) : '' ?>
                                            </td>
                                            <td><?= h($article->kind) ?></td>
                                            <td>
                                                <?php if ($article->is_published == true): ?>
                                                    <?= $this->Html->link(
                                                        $article->title,
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
                                                        $article->title,
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
                                            <td>
                                                <?= $article->is_published ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?>
                                            </td>
                                            <td class="actions">
                                                <?= $this->element('evd_dropdown', ['controller' => 'Articles', 'model' => $article, 'display' => 'title']); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>