<?php
$activeFilter = $this->request->getQuery('status');
if ($activeFilter === null) {
    $this->Html->script('https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js', ['block' => true]);
    $this->Html->script('articles_tree', ['block' => true]);
}
?>
<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center pages">
      <div class="d-flex align-items-center me-auto">
        <ul class="navbar-nav me-3">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false"><?= __('Status') ?></a>
            <ul class="dropdown-menu">
              <li>
                <?= $this->Html->link(
                    __('All'), 
                    ['action' => 'treeIndex', 'id' => ''], 
                    [
                      'class' => 'dropdown-item' . ($activeFilter === null ? ' active' : '')
                    ]
                ) ?>
              </li>
              <li>
                <?= $this->Html->link(
                    __('Un-Published'), 
                    ['action' => 'treeIndex', 'id' => '', '?' => ['status' => 0]], 
                    [
                      'class' => 'dropdown-item' . ($activeFilter === '0' ? ' active' : '')
                    ]
                ) ?>
              </li>
              <li>
                <?= $this->Html->link(
                    __('Published'), 
                    ['action' => 'treeIndex', 'id' => '', '?' => ['status' => 1]], 
                    [
                      'class' => 'dropdown-item' . ($activeFilter === '1' ? ' active' : '')
                    ]
                ) ?>
              </li>
            </ul>
          </li>
        </ul>
        <form class="d-flex-grow-1 me-3" role="search">
          <input id="articleSearch" type="search" class="form-control" placeholder="<?= __('Search Pages...') ?>" aria-label="Search">
        </form>
      </div>
      <div class="flex-shrink-0">
        <?= $this->Html->link(__('New Page'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
      </div>
    </div>
</header>
<?php
    if (!empty($articles)) {
        echo $this->element('page_tree', ['articles' => $articles, 'level' => 0]);
    } else {
        echo $this->Html->tag('p', __('No pages found.'));
    }
?>