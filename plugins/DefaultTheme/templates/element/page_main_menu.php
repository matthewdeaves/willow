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
                    'aria-expanded' => 'false',
                    'escape' => false
                ]
            );
            echo '<ul class="dropdown-menu" aria-labelledby="navbarDropdown' . $item['id'] . '">';
            foreach ($item['children'] as $child) {
                echo '<li>'; // Add the <li> element here
                renderArticleMainMenuItem($child, $Html, $currentUrl);
                echo '</li>'; // Close the <li> element
            }
            echo '</ul>';
            echo '</li>';
        } else {
            echo '<li class="nav-item">';
            echo $Html->link(
                htmlspecialchars_decode($title),
                $url,
                [
                    'class' => 'nav-link child-menu-item ' . $activeClass // Add the 'child-menu-item' class
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