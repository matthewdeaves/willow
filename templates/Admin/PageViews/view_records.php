<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 * @var \Cake\Collection\CollectionInterface $viewRecords
 */
?>
<div class="row">
    <div class="column">
        <div class="articles view content">
            <h1><?= __('Page Views for {0}', h($article->title)) ?></h1>
            <div class="article-details">
                <p><strong><?= __('Article ID:') ?></strong> <?= h($article->id) ?></p>
                <p><strong><?= __('Slug:') ?></strong> <?= h($article->slug) ?></p>
            </div>
            <h2><?= __('Page Views') ?></h2>
            <?php if (!$viewRecords->isEmpty()): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?= __('Date') ?></th>
                                <th><?= __('IP Address') ?></th>
                                <th><?= __('User Agent') ?></th>
                                <th><?= __('Referer') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($viewRecords as $record): ?>
                                <tr>
                                    <td><?= $record->created->format('Y-m-d H:i:s') ?></td>
                                    <td><?= h($record->ip_address) ?></td>
                                    <td><?= h($record->user_agent) ?></td>
                                    <td><?= h($record->referer) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p><?= __('No view records found for this article.') ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>