<?php
use Cake\Utility\Inflector;

if (!function_exists('renderArticleMenuItem')) {
    function renderArticleMenuItem($item, $Html, $level = 0) {
        $activeClass = isset($item['active']) && $item['active'] ? 'active' : '';
        $hasChildren = !empty($item['children']);
        $indentClass = $level > 0 ? 'ps-' . ($level * 3) : '';
        $rootClass = $level == 0 ? 'bg-secondary' : '';

        // Use CakePHP's array-based URL generation
        $url = isset($item['slug']) ? ['_name' => 'article-by-slug', 'slug' => $item['slug']] : '#';

        // Capitalize each word in the title
        $title = Inflector::humanize($item['title']);

        echo $Html->link(
            htmlspecialchars_decode($title),
            $url,
            [
                'class' => 'list-group-item list-group-item-action ' . $activeClass . ' ' . $indentClass . ' ' . $rootClass
            ]
        );

        if ($hasChildren) {
            foreach ($item['children'] as $child) {
                renderArticleMenuItem($child, $Html, $level + 1);
            }
        }
    }
}
?>

<div class="list-group">
    <?php
    // Add the Home link as the top root node with Bootstrap primary color
    echo $this->Html->link(
        __('Home'),
        ['controller' => 'Articles', 'action' => 'index'],
        [
            'class' => 'list-group-item list-group-item-action bg-primary text-white'
        ]
    );
    ?>
    <?php foreach ($articleTreeMenu as $item): ?>
        <?php renderArticleMenuItem($item, $this->Html); ?>
    <?php endforeach; ?>
</div>