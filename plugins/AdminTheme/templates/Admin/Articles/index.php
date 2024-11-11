<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Article> $articles
 */
?>
<?php use App\Utility\SettingsManager; ?>
<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center articles">
      <div class="d-flex align-items-center me-auto">
        <ul class="navbar-nav me-3">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false"><?= __('Status') ?></a>
            <ul class="dropdown-menu">
              <?php $activeFilter = $this->request->getQuery('status'); ?>
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
                    __('Un-Published'), 
                    ['action' => 'index', '?' => ['status' => 0]],
                    [
                      'class' => 'dropdown-item' . ('0' === $activeFilter ? ' active' : '')
                    ]
                ) ?>
              </li>
              <li>
                <?= $this->Html->link(
                    __('Published'), 
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
          <input id="articleSearch" type="search" class="form-control" placeholder="<?= __('Search Articles...') ?>" aria-label="Search" value="<?= $this->request->getQuery('search') ?>">
        </form>
      </div>
      <div class="flex-shrink-0">
        <?= $this->Html->link(__('New Article'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
      </div>
    </div>
</header>
<div id="ajax-target">
  <table class="table table-striped">
    <thead>
      <tr>
        <th scope="col"><?= __('Picture') ?></th>
        <th scope="col"><?= $this->Paginator->sort('user_id', 'Author') ?></th>
        <th scope="col"><?= $this->Paginator->sort('title') ?></th>

        <?php if (null === $activeFilter) :?>
        <th scope="col"><?= $this->Paginator->sort('is_published', 'Status') ?></th>
        <?php elseif ('1' === $activeFilter) :?>
        <th scope="col"><?= $this->Paginator->sort('published') ?></th>
        <?php elseif ('0' === $activeFilter) :?>
        <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
        <?php endif; ?>

        <th scope="col"><?= __('Actions') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($articles as $article): ?>
      <tr>
        <td>
          <?php if (!empty($article->image)) : ?>
          <div class="position-relative">
            <?= $this->Html->image(SettingsManager::read('ImageSizes.small', '200') . '/' . $article->image, 
              [
                'pathPrefix' => 'files/Articles/image/', 
                'alt' => $article->alt_text, 
                'class' => 'img-thumbnail', 
                'width' => '50',
                'data-bs-toggle' => 'popover',
                'data-bs-trigger' => 'hover',
                'data-bs-html' => 'true',
                'data-bs-content' => $this->Html->image(
                  SettingsManager::read('ImageSizes.large', '400') . '/' . $article->image, 
                  [
                    'pathPrefix' => 'files/Articles/image/', 
                    'alt' => $article->alt_text, 
                    'class' => 'img-fluid', 
                    'style' => 'max-width: 300px; max-height: 300px;'
                  ])
              ])?>
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
        <td>
          <?php if ($article->is_published == true): ?>
              <?= $this->Html->link(
                  html_entity_decode($article->title),
                  [
                      'controller' => 'Articles',
                      'action' => 'view-by-slug',
                      'slug' => $article->slug,
                      '_name' => 'article-by-slug'
                  ],
                  ['escape' => false]
              );
              ?>
          <?php else: ?>
              <?= $this->Html->link(
                  html_entity_decode($article->title),
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
        <?php if (null === $activeFilter) :?>
        <td><?= $article->is_published ? '<span class="badge bg-success">' . __('Published') . '</span>' : '<span class="badge bg-warning">' . __('Un-Published') . '</span>'; ?></td>
        <?php elseif ('1' === $activeFilter) :?>
        <td><?= h($article->published) ?></td>
        <?php elseif ('0' === $activeFilter) :?>
        <td><?= h($article->modified) ?></td>
        <?php endif; ?>
        <td>
          <?= $this->element('evd_dropdown', ['model' => $article, 'display' => 'title']); ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?= $this->element('pagination', ['recordCount' => count($articles), 'search' => $search ?? '']) ?>
</div>
<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('articleSearch');
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