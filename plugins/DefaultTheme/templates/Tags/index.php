<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Tag> $tags
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4 text-primary"><?= __('Tags') ?></h2>
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th><?= $this->Paginator->sort('title', null, ['class' => 'text-decoration-none text-dark']) ?></th>
                                    <th><?= $this->Paginator->sort('created', null, ['class' => 'text-decoration-none text-dark']) ?></th>
                                    <th><?= $this->Paginator->sort('modified', null, ['class' => 'text-decoration-none text-dark']) ?></th>
                                    <th class="actions"><?= __('Actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tags as $tag): ?>
                                <tr>
                                    <td><?= h($tag->title) ?></td>
                                    <td><?= h($tag->created->format('Y-m-d H:i')) ?></td>
                                    <td><?= h($tag->modified->format('Y-m-d H:i')) ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__('View'), ['action' => 'view', $tag->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?= $this->element('pagination', ['recordCount' => count($tags)]) ?>
</div>