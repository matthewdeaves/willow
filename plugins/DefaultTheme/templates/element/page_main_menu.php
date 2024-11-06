<?php
use Cake\Utility\Inflector;

if (!function_exists('renderArticleMainMenuItem')) {
    function renderArticleMainMenuItem($item, $Html, $currentUrl) {
        // Use CakePHP's URL helper to generate the URL
        $url = $Html->Url->build(['_name' => 'page-by-slug', 'slug' => $item['slug']]);

        // Check if the current URL matches the item's URL
        $isActive = $currentUrl === $url;
        $activeClass = $isActive ? 'active' : '';

        // Capitalize each word in the title
        $title = Inflector::humanize($item['title']);

        echo '<li class="nav-item">';
        echo $Html->link(
            htmlspecialchars_decode($title),
            $url,
            [
                'class' => 'nav-link ' . $activeClass,
                'escape' => false
            ]
        );
        echo '</li>';
    }
}
?>

<ul class="navbar-nav ms-auto">
    <li class="nav-item">
        <?= $this->Html->link(__('Blog'), ['_name' => 'home'], ['class' => 'nav-link']) ?>
    </li>
    <li class="nav-item">
        <?= $this->Html->link(__('Tags'), ['_name' => 'tags-index'], ['class' => 'nav-link']) ?>
    </li>
    <?php
    // Get the current URL
    $currentUrl = $this->request->getPath();

    // Render each root page as a menu item
    foreach ($articleTreeMenu as $item) {
        renderArticleMainMenuItem($item, $this->Html, $currentUrl);
    }
    ?>
</ul>