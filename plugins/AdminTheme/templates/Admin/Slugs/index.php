<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Slug> $slugs
 */
?>
<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center slugs">
      <div class="d-flex align-items-center me-auto">
        <ul class="navbar-nav me-3">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false"><?= __('Filter') ?></a>
            <ul class="dropdown-menu">
              <?php $activeFilter = $this->request->getQuery('status');  ?>
              <li>
                <?= $this->Html->link(
                    __('All'), 
                    ['action' => 'index'], 
                    [
                      'class' => 'dropdown-item' . (null === $activeFilter ? ' active' : '')
                    ]
                ) ?>
              </li>
              <li>
                <?= $this->Html->link(
                    __('Filter 1'), 
                    ['action' => 'index', '?' => ['status' => 0]],
                    [
                      'class' => 'dropdown-item' . ('0' === $activeFilter ? ' active' : '')
                    ]
                ) ?>
              </li>
              <li>
                <?= $this->Html->link(
                    __('Filter 2'), 
                    ['action' => 'index', '?' => ['status' => 1]],
                    [
                      'class' => 'dropdown-item' . ('1' === $activeFilter ? ' active' : '')
                    ]
                ) ?>
              </li>
            </ul>
          </li>
        </ul>
        <form class="d-flex-grow-1 me-3" role="search">
          <input id="slugSearch" type="search" class="form-control" placeholder="<?= __('Search Slugs...') ?>" aria-label="Search" value="<?= $this->request->getQuery('search') ?>">
        </form>
      </div>
      <div class="flex-shrink-0">
        <?= $this->Html->link(__('New Slug'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
      </div>
    </div>
</header>
<div id="ajax-target">
  <table class="table table-striped">
    <thead>
      <tr>
              <th scope="col"><?= $this->Paginator->sort('model') ?></th>
              <th scope="col"><?= $this->Paginator->sort('foreign_key', __('Title')) ?></th>
              <th scope="col"><?= $this->Paginator->sort('slug') ?></th>
              <th scope="col"><?= $this->Paginator->sort('created') ?></th>
              <th scope="col"><?= __('Actions') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($slugs as $slug): ?>
      <tr>
          <td><?= h($slug->model) ?></td>
          <td>
              <?php if (isset($relatedData[$slug->id])): ?>
                  <?= $this->Html->link(
                      h($relatedData[$slug->id]['title']),
                      [
                          'controller' => $relatedData[$slug->id]['controller'],
                          'action' => 'view',
                          $relatedData[$slug->id]['id']
                      ],
                      [
                          'class' => 'text-decoration-none',
                          'escape' => false
                      ]
                  ) ?>
              <?php else: ?>
                  <?= h($slug->foreign_key) ?>
              <?php endif; ?>
          </td>
          <td><?= h($slug->slug) ?></td>
          <td><?= h($slug->created) ?></td>
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
                          <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $slug->id], ['confirm' => __('Are you sure you want to delete # {0}?', $slug->id), 'class' => 'dropdown-item text-danger']) ?>
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
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('slugSearch');
    const resultsContainer = document.querySelector('#ajax-target');

    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const searchTerm = this.value.trim();
            
            let url = `<?= $this->Url->build(['action' => 'index']) ?>`;

            <?php if (null !== $activeFilter): ?>
            url += `?status=<?= urlencode($activeFilter) ?>`;
            <?php endif; ?>

            if (searchTerm.length > 0) {
                url += (url.includes('?') ? '&' : '?') + `search=${encodeURIComponent(searchTerm)}`;
            }
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

        }, 300); // Debounce for 300ms
    });

    // Initialize popovers on page load
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
<?php $this->Html->scriptEnd(); ?>