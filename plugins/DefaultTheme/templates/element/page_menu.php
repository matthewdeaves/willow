<?php
use Cake\Utility\Inflector;

if (!function_exists('renderArticleMenuItem')) {
    function renderArticleMenuItem($item, $Html, $currentUrl, $level = 0) {
        $hasChildren = !empty($item['children']);
        $indentClass = $level > 0 ? 'ps-' . ($level * 3) : '';
        
        // Use CakePHP's array-based URL generation
        $url = isset($item['slug']) ? ['_name' => 'page-by-slug', 'slug' => $item['slug']] : '#';

        // Check if the current URL matches the item's URL
        $isActive = $currentUrl === $Html->Url->build($url);
        $activeClass = $isActive ? 'active-light-grey' : ''; // Updated class name

        // Capitalize each word in the title
        $title = Inflector::humanize($item['title']);

        echo $Html->link(
            htmlspecialchars_decode($title),
            $url,
            [
                'class' => 'list-group-item list-group-item-action ' . $activeClass . ' ' . $indentClass
            ]
        );

        if ($hasChildren) {
            foreach ($item['children'] as $child) {
                renderArticleMenuItem($child, $Html, $currentUrl, $level + 1);
            }
        }
    }
}
?>

<div class="list-group">
    <?php
    // Get the current URL
    $currentUrl = $this->request->getPath();

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
        <?php renderArticleMenuItem($item, $this->Html, $currentUrl); ?>
    <?php endforeach; ?>
</div>