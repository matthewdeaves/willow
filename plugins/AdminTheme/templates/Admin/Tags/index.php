<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Tag> $tags
 */
?>
<?php use App\Utility\SettingsManager; ?>
<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center tags">
      <div class="d-flex align-items-center me-auto">
      <ul class="navbar-nav me-3">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false"><?= __('Level') ?></a>
            <ul class="dropdown-menu">
              <?php $activeFilter = $this->request->getQuery('level'); ?>
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
                    __('Root'), 
                    ['action' => 'index', '?' => ['level' => 0]],
                    [
                      'class' => 'dropdown-item' . ('0' === $activeFilter ? ' active' : '')
                    ]
                ) ?>
              </li>
              <li>
                <?= $this->Html->link(
                    __('Child'), 
                    ['action' => 'index', '?' => ['level' => 1]],
                    [
                      'class' => 'dropdown-item' . ('1' === $activeFilter ? ' active' : '')
                    ]
                ) ?>
              </li>
            </ul>
          </li>
        </ul>
        <form class="d-flex-grow-1 me-3" role="search">
          <input id="tagSearch" type="search" class="form-control" placeholder="<?= __('Search Tags...') ?>" aria-label="Search" value="<?= $this->request->getQuery('search') ?>">
        </form>
      </div>
      <div class="flex-shrink-0">
        <?= $this->Html->link(__('New Tag'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
      </div>
    </div>
</header>
<div id="ajax-target">
<table class="table table-striped">
  <thead>
    <tr>
          <th><?= __('Picture') ?></th>
          <th scope="col"><?= $this->Paginator->sort('title') ?></th>
          <th scope="col"><?= $this->Paginator->sort('slug') ?></th>
          <th scope="col"><?= $this->Paginator->sort('parent_id', __('Parent')) ?></th>
          <th scope="col"><?= __('Actions') ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($tags as $tag): ?>
    <tr>
        <td>
          <?php if (!empty($tag->image)) : ?>
              <div class="position-relative">
                <?= $this->element('image/icon', ['model' => $tag, 'icon' => $tag->smallImageUrl, 'preview' => $tag->largeImageUrl]); ?>
              </div>
          <?php endif; ?>
        </td>
            <td><?= html_entity_decode($tag->title) ?></td>
            <td><?= h($tag->slug) ?></td>
            <td>
              <?php if (!empty($tag->parent_tag)) : ?>
                  <?= $this->Html->link(
                      h($tag->parent_tag->title), 
                      ['controller' => 'Tags', 'action' => 'view', $tag->parent_tag->id]
                  ); ?>
              <?php endif; ?>
            </td>
        <td>
          <?= $this->element('evd_dropdown', ['model' => $tag, 'display' => 'title']); ?>
        </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?= $this->element('pagination', ['recordCount' => count($tags), 'search' => $search ?? '']) ?>
</div>
<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('tagSearch');
    const resultsContainer = document.querySelector('#ajax-target');

    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const searchTerm = this.value.trim();
            
            let url = `<?= $this->Url->build(['action' => 'index']) ?>`;

            <?php if (null !== $activeFilter): ?>
            url += `?level=<?= urlencode($activeFilter) ?>`;
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