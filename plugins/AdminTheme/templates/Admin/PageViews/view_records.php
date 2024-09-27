<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 * @var \Cake\Collection\CollectionInterface $viewRecords
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= __('Page Views for {0}', h($article->title)) ?></h3>
                </div>
                <div class="card-body">
                    <div class="article-details mb-4">
                        <p><strong><?= __('Article ID:') ?></strong> <?= h($article->id) ?></p>
                        <p><strong><?= __('Slug:') ?></strong> <?= h($article->slug) ?></p>
                    </div>
                    <h4 class="mb-3"><?= __('Page Views') ?></h4>
                    <?php if (!$viewRecords->isEmpty()): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="thead-light">
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
                        <div class="alert alert-info" role="alert">
                            <?= __('No view records found for this article.') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>