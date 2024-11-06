<?php
use Cake\Utility\Inflector;

if (!function_exists('renderArticleMainMenuItem')) {
    function renderArticleMainMenuItem($item, $Html, $currentUrl) {
        $hasChildren = !empty($item['children']);
        
        // Use CakePHP's URL helper to generate the URL
        $url = $Html->Url->build(['_name' => 'page-by-slug', 'slug' => $item['slug']]);

        // Check if the current URL matches the item's URL
        $isActive = $currentUrl === $url;
        $activeClass = $isActive ? 'active' : '';

        // Capitalize each word in the title
        $title = Inflector::humanize($item['title']);

        echo '<li class="nav-item' . ($hasChildren ? ' dropdown' : '') . '">';
        echo $Html->link(
            htmlspecialchars_decode($title),
            $url,
            [
                'class' => 'nav-link' . ($hasChildren ? ' dropdown-toggle' : '') . ' ' . $activeClass,
                'id' => $hasChildren ? 'navbarDropdown' . $item['id'] : null,
                'role' => $hasChildren ? 'button' : null,
                'data-bs-toggle' => $hasChildren ? 'dropdown' : null,
                'aria-expanded' => $hasChildren ? 'false' : null,
                'escape' => false,
                'onclick' => $hasChildren ? 'handleRootMenuClick(event, this)' : null
            ]
        );

        if ($hasChildren) {
            echo '<ul class="dropdown-menu" aria-labelledby="navbarDropdown' . $item['id'] . '">';
            foreach ($item['children'] as $child) {
                $childUrl = $Html->Url->build(['_name' => 'page-by-slug', 'slug' => $child['slug']]);
                echo '<li>';
                echo $Html->link(
                    htmlspecialchars_decode($child['title']),
                    $childUrl,
                    [
                        'class' => 'dropdown-item ' . ($currentUrl === $childUrl ? 'active' : '')
                    ]
                );
                echo '</li>';
            }
            echo '</ul>';
        }
        echo '</li>';
    }
}
?>

<ul class="navbar-nav ms-auto">
    <?php
    // Get the current URL
    $currentUrl = $this->request->getPath();

    // Render each root page as a menu item
    foreach ($articleTreeMenu as $item) {
        renderArticleMainMenuItem($item, $this->Html, $currentUrl);
    }
    ?>
</ul>

<script>
function handleRootMenuClick(event, link) {
    if (!event.target.closest('.dropdown-menu')) {
        window.location.href = link.href;
    }
}
</script>