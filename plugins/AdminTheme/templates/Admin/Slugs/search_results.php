<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Slug> $slugs
 */
?>
<?php foreach ($slugs as $slug): ?>
    <tr>
        <td><?= $slug->hasValue('article') ? $this->Html->link($slug->article->title, ['controller' => 'Articles', 'action' => 'view', $slug->article->id]) : '' ?></td>
        <td><?= h($slug->slug) ?></td>
        <td><?= h($slug->created) ?></td>
        <td><?= h($slug->modified) ?></td>
        <td>
            <div class="btn-group w-100 align-items-center justify-content-between flex-wrap">
                <div class="dropdown">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <?= __('Actions') ?>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <?= $this->Html->link(__('View'), ['action' => 'view', $slug->id], ['class' => 'dropdown-item']) ?>
                    </li>
                    <li>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $slug->id], ['class' => 'dropdown-item']) ?>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $slug->id], ['confirm' => __('Are you sure you want to delete {0}?', $slug->slug), 'class' => 'dropdown-item text-danger']) ?>
                    </li>
                </ul>
                </div>
            </div>
        </td>
    </tr>
<?php endforeach; ?>