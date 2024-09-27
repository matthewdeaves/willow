<?php
$this->Html->css('articles_tree', ['block' => true]);
$this->Html->script('https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js', ['block' => true]);
$this->Html->script('articles_tree', ['block' => true]);
?>
<div class="articles index content">
    <h3><?= __('Pages') ?></h3>
    <div class="actions">
        <?= $this->Html->link(__('Add Page'), ['prefix' => 'Admin', 'action' => 'add', '?' => ['is_page' => 1]], ['class' => 'button']) ?>
    </div>

    <div id="articles-tree">
        <?php
            function displayTree($articles, $Form, $level = 0) {
                echo '<ul class="sortable-list' . ($level > 0 ? ' nested' : '') . '">';
                foreach ($articles as $article) {
                    echo '<li class="sortable-item" data-id="' . $article['id'] . '">';
                    echo '<div class="article-title" >';
                    echo '<span class="handle">☰</span> ';
                    echo h($article['title']);

                    // Add link
                    echo $Form->Html->link('Add', [
                        'prefix' => 'Admin',
                        'action' => 'add',
                        '?' => ['is_page' => 1, 'parent_id' => $article['id']]
                    ], [
                    ]);

                    // Edit link
                    echo $Form->Html->link('Edit', [
                        'prefix' => 'Admin',
                        'action' => 'edit',
                        $article['id'],
                        '?' => ['is_page' => 1, 'parent_id' => $article['parent_id']]
                    ], [
                    ]);

                    // Delete button
                    echo $Form->postButton(
                        'Delete',
                        ['prefix' => 'Admin', 'action' => 'delete', $article['id']],
                        [
                            'confirm' => 'Are you sure you want to delete # ' . $article['id'] . '?',
                            'data' => ['is_page' => 1],
                        ]
                    );

                    echo '</div>';
                    if (!empty($article['children'])) {
                        displayTree($article['children'], $Form, $level + 1);
                    } else {
                        echo '<ul class="sortable-list nested"></ul>';
                    }
                    echo '</li>';
                }
                echo '</ul>';
            }

        if (!empty($articles)) {
            displayTree($articles, $this->Form);
        } else {
            echo '<p>No articles found.</p>';
        }
        ?>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var nestedSortables = [].slice.call(document.querySelectorAll('.sortable-list'));
    for (var i = 0; i < nestedSortables.length; i++) {
        new Sortable(nestedSortables[i], {
            group: 'nested',
            animation: 150,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            ghostClass: 'blue-background-class',
            onEnd: function(evt) {
                var itemEl = evt.item;
                var newParentId = itemEl.parentNode.closest('.sortable-item') ? 
                    itemEl.parentNode.closest('.sortable-item').dataset.id : null;
                var order = Array.from(itemEl.parentNode.children).map(el => el.dataset.id);
                saveNewOrder(itemEl.dataset.id, newParentId, order);
            }
        });
    }
});

function saveNewOrder(itemId, newParentId, order) {
    fetch('/admin/articles/reorder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?= $this->request->getAttribute('csrfToken') ?>'
        },
        body: JSON.stringify({
            itemId: itemId,
            newParentId: newParentId,
            order: order
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Order saved successfully');
        } else {
            console.error('Error saving order');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
