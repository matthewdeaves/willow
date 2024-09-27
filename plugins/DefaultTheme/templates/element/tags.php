<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 */
?>
<?php if (!empty($article->tags)) : ?>
<div class="related-tags mb-4">
    <h4><?= __('Related Tags') ?></h4>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead class="table-light">
                <tr>
                    <th><?= __('Title') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($article->tags as $tag) : ?>
                <tr>
                    <td><?= h($tag->title) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['controller' => 'Tags', 'action' => 'view', $tag->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>