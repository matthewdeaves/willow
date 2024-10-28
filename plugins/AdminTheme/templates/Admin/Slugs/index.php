<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Slug> $slugs
 */
?>
<div class="slugs index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><?= __('Slugs') ?></h3>
        <div>
            <?= $this->Html->link(__('New Slug'), ['action' => 'add'], ['class' => 'btn btn-primary me-2']) ?>
        </div>
    </div>
    <div class="mb-3">
        <input type="text" id="slugSearch" class="form-control" placeholder="<?= __('Search slugs...') ?>">
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th><?= __('Slug') ?></th>
                    <th><?= __('Article/Page') ?></th>
                    <th><?= __('Modified') ?></th>
                    <th><?= __('Created') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody id="slugResults">
                <?php foreach ($slugs as $slug): ?>
                <tr>
                    <td>
                        <?php $ruleName = ($slug->article->is_page == 0) ? 'article-by-slug' : 'page-by-slug';?>
                        <?php if ($slug->article->is_published == true): ?>
      
                            <?= $this->Html->link(
                                $slug->slug,
                                [
                                    'controller' => 'Articles',
                                    'action' => 'view-by-slug',
                                    'slug' => $slug->slug,
                                    '_name' => $ruleName,
                                ],
                                ['escape' => false]
                            );
                            ?>
                        <?php else: ?>
                            <?= $this->Html->link(
                                $slug->slug,
                                [
                                    'prefix' => 'Admin',
                                    'controller' => 'Slugs',
                                    'action' => 'view',
                                    $slug->id,
                                ],
                                ['escape' => false]
                            ) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= $slug->hasValue('article') ? $this->Html->link($slug->article->title, ['controller' => 'Articles', 'action' => 'view', $slug->article->id]) : '' ?></td>
                    <td><?= h($slug->modified->format('Y-m-d H:i')) ?></td>
                    <td><?= h($slug->created->format('Y-m-d H:i')) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $slug->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $slug->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $slug->id], ['confirm' => __('Are you sure you want to delete # {0}?', $slug->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('pagination', ['recordCount' => count($slugs)]) ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('slugSearch');
    const resultsContainer = document.getElementById('slugResults');

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
        }, 300);
    });
});
</script>