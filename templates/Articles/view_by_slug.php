<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 */
?>
<div class="row">
    <div class="column column-80">
        <div class="articles view content">
            <h3><?= h($article->title) ?></h3>
            <table>
                <tr>
                    <th><?= __('User') ?></th>
                    <td><?= h($article->user->username) ?></td>
                </tr>
                <tr>
                    <th><?= __('Title') ?></th>
                    <td><?= h($article->title) ?></td>
                </tr>
                <tr>
                    <th><?= __('Slug') ?></th>
                    <td><?= $this->Html->link(substr($article->slug, 0, 10) . '...', ['controller' => 'Articles', 'action' => 'viewBySlug', $article->slug]) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($article->id) ?></td>
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
            <?= $this->element('seo_fields', ['article' => $article]) ?>
            <div class="text">
                <strong><?= __('Body') ?></strong>
                <blockquote>
                    <?= $article->body; ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Tags') ?></h4>
                <?php if (!empty($article->tags)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Title') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($article->tags as $tag) : ?>
                        <tr>
                            <td><?= h($tag->id) ?></td>
                            <td><?= h($tag->title) ?></td>
                            <td><?= h($tag->created) ?></td>
                            <td><?= h($tag->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Tags', 'action' => 'view', $tag->id]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="comments view content">
            <h3>Comments</h3>
            <?php if (!$comments->isEmpty()): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <p><?= h($comment->content) ?></p>
                        <small>Posted on: <?= $comment->created->format('Y-m-d H:i:s') ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No comments yet.</p>
            <?php endif; ?>           
        </div>
        <div class="comments add content">
            <!-- After the article content -->
            <h3>Add a Comment</h3>
            <?= $this->Form->create(null, ['url' => ['controller' => 'Articles', 'action' => 'addComment', $article->id]]) ?>
                <?= $this->Form->control('content', ['label' => 'Comment', 'type' => 'textarea']) ?>
                <?= $this->Form->button('Submit Comment') ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
