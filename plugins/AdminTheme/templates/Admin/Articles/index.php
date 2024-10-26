<?php use App\Utility\SettingsManager; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Article> $articles
 */
?>
<div class="articles index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><?= __('Articles') ?></h3>
        <?= $this->Html->link(__('New Article'), ['prefix' => 'Admin', 'action' => 'add'], ['class' => 'btn btn-primary my-3 ms-2']) ?>
    </div>
    <div class="mb-3">
        <input type="text" id="articleSearch" class="form-control" placeholder="<?= __('Search articles...') ?>">
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th><?= __('Picture') ?></th>
                    <th><?= $this->Paginator->sort('user_id', 'Author') ?></th>
                    <th><?= $this->Paginator->sort('is_published', 'Published') ?></th>
                    <th><?= $this->Paginator->sort('title') ?></th>
                    <th><?= $this->Paginator->sort('slug') ?></th>
                    <th><?= $this->Paginator->sort('pageview_count', 'Views') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $article): ?>
                <tr>
                    <td>
                        <?php if (!empty($article->image)) : ?>
                        <div class="position-relative">
                            <?= $this->Html->image(SettingsManager::read('ImageSizes.small', '200') . '/' . $article->image, 
                                ['pathPrefix' => 'files/Articles/image/', 
                                'alt' => $article->alt_text, 
                                'class' => 'img-thumbnail', 
                                'width' => '50',
                                'data-bs-toggle' => 'popover',
                                'data-bs-trigger' => 'hover',
                                'data-bs-html' => 'true',
                                'data-bs-content' => $this->Html->image(SettingsManager::read('ImageSizes.large', '400') . '/' . $article->image, 
                                    ['pathPrefix' => 'files/Articles/image/', 
                                    'alt' => $article->alt_text, 
                                    'class' => 'img-fluid', 
                                    'style' => 'max-width: 300px; max-height: 300px;'])
                                ]) 
                            ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (isset($article->_matchingData['Users']) && $article->_matchingData['Users']->username): ?>
                            <?= $this->Html->link(
                                h($article->_matchingData['Users']->username),
                                ['controller' => 'Users', 'action' => 'view', $article->_matchingData['Users']->id]
                            ) ?>
                        <?php else: ?>
                            <?= h(__('Unknown Author')) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= $article->is_published ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-secondary">' . __('No') . '</span>' ?></td>
                    <td><?= h($article->title) ?></td>
                    <td>
                        <?php if ($article->is_published == true): ?>
                            <?= $this->Html->link(
                                substr($article->slug, 0, 15) . '...',
                                '/' . $article->slug,
                                ['escape' => false]
                            ) ?>
                        <?php else: ?>
                            <?= $this->Html->link(
                                substr($article->slug, 0, 15) . '...',
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
                        <?= $this->Html->link(
                            h($article->pageview_count), 
                            [
                                'prefix' => 'Admin', 
                                'controller' => 'PageViews', 
                                'action' => 'pageViewStats', 
                                $article->id
                            ],
                            ['class' => 'btn btn-sm btn-outline-info']
                        ) ?>
                    </td>
                    <td><?= h($article->created->format('Y-m-d H:i')) ?></td>
                    <td><?= h($article->modified->format('Y-m-d H:i')) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['prefix' => 'Admin', 'action' => 'view', $article->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= $this->Html->link(__('Edit'), ['prefix' => 'Admin', 'action' => 'edit', $article->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?= $this->Form->postLink(__('Delete'), ['prefix' => 'Admin', 'action' => 'delete', $article->id], ['confirm' => __('Are you sure you want to delete {0}?', $article->title), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('pagination', ['recordCount' => count($articles)]) ?>
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('articleSearch');
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
                    // Re-initialize popovers after updating the content
                    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
                    popoverTriggerList.map(function (popoverTriggerEl) {
                        return new bootstrap.Popover(popoverTriggerEl);
                    });
                })
                .catch(error => console.error('Error:', error));
            } else {
                // If search is empty, you might want to reload all results or clear the table
                location.reload();
            }
        }, 300); // Debounce for 300ms
    });

    // Initialize popovers on page load
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
<?php $this->Html->scriptEnd(); ?>