<?php
$this->Html->css('articles_tree', ['block' => true]);
$this->Html->script('https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js', ['block' => true]);
$this->Html->script('articles_tree', ['block' => true]);
?>
<div class="articles index content container-fluid mt-4">
    <div class="row mb-4">
        <div class="col">
            <h3><?= __('Pages') ?></h3>
        </div>
        <div class="col-auto">
            <?= $this->Html->link(__('Add Page'), ['prefix' => 'Admin', 'action' => 'add', '?' => ['kind' => 'page']], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <div id="articles-tree" class="card">
        <div class="card-body p-4">
            <?php
                function displayTree($articles, $Form, $level = 0) {
                    echo "<ul class=\"list-group sortable-list\" data-level=\"$level\">";
                    foreach ($articles as $article) {
                        echo "<li class=\"list-group-item sortable-item py-2 px-3 mb-2 border\" data-id=\"{$article['id']}\">";
                        echo '<div class="d-flex justify-content-between align-items-center">';
                        echo '<div class="d-flex align-items-center">';
                        echo '<span class="handle me-2">☰</span>';
                        echo '<span class="me-3">' . h($article['title']) . '</span>';
                        echo '<span class="badge ' . ($article->is_published ? 'bg-success' : 'bg-secondary') . '">' 
                            . ($article->is_published ? 'Published' : 'Unpublished') . '</span>';

                        echo $Form->Html->link(
                            '<span class="badge bg-info">' . __('{0} Views', $article->pageview_count) . '</span>',
                            [
                                'prefix' => 'Admin', 
                                'controller' => 'PageViews', 
                                'action' => 'pageViewStats', 
                                $article['id']
                            ],
                            [
                                'escape' => false,
                                'class' => 'ms-2'
                            ]
                        );

                        echo '</div>';
                        echo '<div class="btn-group" role="group">';

                        if ($article->is_published) {
                            echo $Form->Html->link(
                                __('Live'),
                                [
                                    'controller' => 'Articles',
                                    'action' => 'view-by-slug',
                                    'slug' => $article->slug,
                                    '_name' => 'page-by-slug'
                                ],
                                ['class' => 'btn btn-sm btn-outline-info']
                            );
                        }

                        echo $Form->Html->link(
                            __('View'),
                            [
                                'prefix' => 'Admin',
                                'controller' => 'Articles',
                                'action' => 'view',
                                $article->id,
                            ],
                            ['class' => 'btn btn-sm btn-outline-info']
                        );
                        
                        echo $Form->Html->link('Add', [
                            'prefix' => 'Admin',
                            'action' => 'add',
                            '?' => ['kind' => 'page', 'parent_id' => $article['id']]
                        ], ['class' => 'btn btn-sm btn-outline-primary']);
                
                        echo $Form->Html->link('Edit', [
                            'prefix' => 'Admin',
                            'action' => 'edit',
                            $article['id'],
                            '?' => ['kind' => 'page', 'parent_id' => $article['parent_id']]
                        ], ['class' => 'btn btn-sm btn-outline-secondary']);
                
                        echo $Form->postLink(
                            'Delete',
                            ['prefix' => 'Admin', 'action' => 'delete', $article['id']],
                            [
                                'confirm' => __('Are you sure you want to delete {0}?', $article['title']),
                                'data' => ['kind' => 'page'],
                                'class' => 'btn btn-sm btn-outline-danger'
                            ]
                        );
                
                        echo '</div>'; // Close btn-group
                        echo '</div>'; // Close d-flex justify-content-between
                
                        echo '<div class="children-container">';
                        if (!empty($article['children'])) {
                            displayTree($article['children'], $Form, $level + 1);
                        } else {
                            echo '<ul class="list-group sortable-list ms-4 mt-2"></ul>';
                        }
                        echo '</div>'; // Close children-container
                        echo '</li>';
                    }
                    echo '</ul>';
                }

                if (!empty($articles)) {
                    displayTree($articles, $this->Form);
                } else {
                    echo $this->Html->tag('p', __('No pages found.'));
                }
            ?>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var articleTree = document.querySelector('#articles-tree .card-body');

    new Sortable(articleTree, {
        group: 'nested',
        animation: 150,
        fallbackOnBody: true,
        swapThreshold: 0.65,
        ghostClass: 'bg-light',
        handle: '.handle',
        dragoverBubble: true,
        onStart: function (evt) {
            evt.from.classList.add('dragging');
        },
        onEnd: function (evt) {
            evt.from.classList.remove('dragging');
            var itemEl = evt.item;
            var targetEl = evt.to.closest('.list-group-item') || evt.to;

            if (targetEl !== articleTree) {
                var childList = targetEl.querySelector('.children-container > .list-group');
                if (!childList) {
                    childList = document.createElement('ul');
                    childList.className = 'list-group sortable-list ms-4 mt-2';
                    targetEl.querySelector('.children-container').appendChild(childList);
                }
                childList.appendChild(itemEl);
            }

            updateOrder();
        },
        filter: '.btn-group, .btn', // Prevents dragging when clicking buttons
        onMove: function (evt, originalEvent) {
            if (evt.related.classList.contains('btn-group') || evt.related.classList.contains('btn')) {
                return false;
            }
            return true; // Allow dropping on all items
        }
    });

    function updateOrder() {
        var items = articleTree.querySelectorAll('.list-group-item');
        var order = [];
        items.forEach(function(item) {
            var parentId = item.parentNode.closest('.list-group-item') ? 
                item.parentNode.closest('.list-group-item').dataset.id : null;
            order.push({
                id: item.dataset.id,
                parentId: parentId
            });
        });
        saveNewOrder(order);
    }
});

function saveNewOrder(order) {
    fetch('/admin/articles/reorder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrfToken"]').content
        },
        body: JSON.stringify({ order: order })
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