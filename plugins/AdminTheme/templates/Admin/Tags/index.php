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
        <form class="d-flex-grow-1 me-3" role="search">
          <input id="tagSearch" type="search" class="form-control" placeholder="<?= __('Search Tags...') ?>" aria-label="Search">
        </form>
      </div>
      <div class="flex-shrink-0">
        <?= $this->Html->link(__('New Tag'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
      </div>
    </div>
</header>
<table class="table table-striped">
  <thead>
    <tr>
          <th><?= __('Picture') ?></th>
          <th scope="col"><?= $this->Paginator->sort('title') ?></th>
          <th scope="col"><?= $this->Paginator->sort('slug') ?></th>
          <th scope="col"><?= __('Actions') ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($tags as $tag): ?>
    <tr>
        <td>
          <?php if (!empty($tag->image)) : ?>
              <div class="position-relative">
                  <?= $this->Html->image(SettingsManager::read('ImageSizes.small', '200') . '/' . $tag->image, 
                      ['pathPrefix' => 'files/Tags/image/', 
                      'alt' => $tag->alt_text, 
                      'class' => 'img-thumbnail', 
                      'width' => '50',
                      'data-bs-toggle' => 'popover',
                      'data-bs-trigger' => 'hover',
                      'data-bs-html' => 'true',
                      'data-bs-content' => $this->Html->image(SettingsManager::read('ImageSizes.large', '400') . '/' . $tag->image, 
                          ['pathPrefix' => 'files/Tags/image/', 
                          'alt' => $tag->alt_text, 
                          'class' => 'img-fluid', 
                          'style' => 'max-width: 300px; max-height: 300px;'])
                      ]) 
                  ?>
              </div>
          <?php endif; ?>
        </td>
            <td><?= html_entity_decode($tag->title) ?></td>
            <td><?= h($tag->slug) ?></td>
        <td>
          <?= $this->element('evd_dropdown', ['model' => $tag, 'display' => 'title']); ?>
        </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?= $this->element('pagination', ['recordCount' => count($tags)]) ?>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('tagSearch');
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