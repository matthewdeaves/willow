<?php
use Cake\Utility\Inflector;
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Comment> $comments
 */
?>
<div class="comments index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><?= __('Comments') ?></h3>
    </div>
    <div class="mb-3">
        <input type="text" id="commentSearch" class="form-control" placeholder="<?= __('Search comments...') ?>">
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th><?= $this->Paginator->sort('model') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('display') ?></th>
                    <th><?= $this->Paginator->sort('is_inappropriate', 'Inappropriate?') ?></th> 
                    <th><?= $this->Paginator->sort('inappropriate_reason', 'Reason') ?></th>
                    <th><?= $this->Paginator->sort('is_analyzed', 'Analyzed?') ?></th>
                    <th><?= __('Content') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                <tr>
                    <td>
                        <?= $this->Html->link(
                            Inflector::singularize($comment->model),
                            [
                                'controller' => $comment->model,
                                'action' => 'view',
                                $comment->foreign_key
                            ]
                        ) ?>
                    </td>
                    <td><?= $comment->hasValue('user') ? $this->Html->link($comment->user->username, ['controller' => 'Users', 'action' => 'view', $comment->user->id]) : '' ?></td>
                    <td><?= h($comment->display ? __('Yes') : __('No')) ?></td>
                    <td><?= h($comment->is_inappropriate ? __('Yes') : __('No')) ?></td>
                    <td><?= h($comment->inappropriate_reason) ?></td>
                    <td><?= h($comment->is_analyzed ? __('Yes') : __('No')) ?></td>
                    <td><?= $this->Text->truncate($comment->content, 50, ['ellipsis' => '...', 'exact' => false]) ?></td>
                    <td><?= h($comment->created->format('Y-m-d H:i')) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $comment->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $comment->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $comment->id], ['confirm' => __('Are you sure you want to delete this comment?'), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('pagination', ['recordCount' => count($comments)]) ?>
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('commentSearch');
    const resultsContainer = document.querySelector('tbody');

    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const searchTerm = this.value.trim();

            if (searchTerm.length > 0) {
                fetch(`<?= $this->Url->build(['action' => 'index']) ?>?search=${encodeURIComponent(searchTerm)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    resultsContainer.innerHTML = html;
                })
                .catch(error => console.error('Error:', error));
            } else {
                location.reload();
            }
        }, 300); // Debounce for 300ms
    });
});
<?php $this->Html->scriptEnd(); ?>