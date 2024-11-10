<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Slug> $slugs
 */
?>
<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center slugs">
      <div class="d-flex align-items-center me-auto">
        <form class="d-flex-grow-1 me-3" role="search">
          <input id="slugSearch" type="search" class="form-control" placeholder="<?= __('Search Slugs...') ?>" aria-label="Search">
        </form>
      </div>
      <div class="flex-shrink-0">
        <?= $this->Html->link(__('New Slug'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
      </div>
    </div>
</header>
<table class="table table-striped">
  <thead>
    <tr>
          <th scope="col"><?= $this->Paginator->sort('article_id') ?></th>
          <th scope="col"><?= $this->Paginator->sort('slug') ?></th>
          <th scope="col"><?= $this->Paginator->sort('created') ?></th>
          <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
          <th scope="col"><?= __('Actions') ?></th>
    </tr>
  </thead>
  <tbody>
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
  </tbody>
</table>

<?= $this->element('pagination', ['recordCount' => count($slugs), 'search' => $search ?? '']) ?>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('slugSearch');
    const resultsContainer = document.querySelector('tbody');

    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const searchTerm = this.value.trim();
            
            let url = `<?= $this->Url->build(['action' => 'index']) ?>`;

            if (searchTerm.length > 0) {
                url += (url.includes('?') ? '&' : '?') + `search=${encodeURIComponent(searchTerm)}`;
                fetch(url, {
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