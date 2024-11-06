<?php
use Cake\Utility\Inflector;

if (!function_exists('renderArticleMainMenuItem')) {
    function renderArticleMainMenuItem($item, $Html, $currentUrl) {
        $hasChildren = !empty($item['children']);
        
        // Use CakePHP's array-based URL generation
        $url = isset($item['slug']) ? ['_name' => 'page-by-slug', 'slug' => $item['slug']] : '#';

        // Check if the current URL matches the item's URL
        $isActive = $currentUrl === $Html->Url->build($url);
        $activeClass = $isActive ? 'active' : '';

        // Capitalize each word in the title
        $title = Inflector::humanize($item['title']);

        if ($hasChildren) {
            echo '<li class="nav-item dropdown">';
            echo $Html->link(
                htmlspecialchars_decode($title),
                $url,
                [
                    'class' => 'nav-link dropdown-toggle ' . $activeClass,
                    'id' => 'navbarDropdown' . $item['id'],
                    'role' => 'button',
                    'data-bs-toggle' => 'dropdown',
                    'aria-expanded' => 'false',
                    'escape' => false
                ]
            );
            echo '<ul class="dropdown-menu" aria-labelledby="navbarDropdown' . $item['id'] . '">';
            foreach ($item['children'] as $child) {
                $childUrl = isset($child['slug']) ? ['_name' => 'page-by-slug', 'slug' => $child['slug']] : '#';
                echo '<li>';
                echo $Html->link(
                    htmlspecialchars_decode($child['title']),
                    $childUrl,
                    [
                        'class' => 'dropdown-item ' . ($currentUrl === $Html->Url->build($childUrl) ? 'active' : '')
                    ]
                );
                echo '</li>';
            }
            echo '</ul>';
            echo '</li>';
        } else {
            echo '<li class="nav-item">';
            echo $Html->link(
                htmlspecialchars_decode($title),
                $url,
                [
                    'class' => 'nav-link ' . $activeClass
                ]
            );
            echo '</li>';
        }
    }
}
?>

<ul class="navbar-nav">
    <?php
    // Get the current URL
    $currentUrl = $this->request->getPath();

    // Render each root page as a menu item
    foreach ($articleTreeMenu as $item) {
        renderArticleMainMenuItem($item, $this->Html, $currentUrl);
    }
    ?>
</ul>