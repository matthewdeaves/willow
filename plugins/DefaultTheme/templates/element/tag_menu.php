<?php
use Cake\Utility\Inflector;

if (!function_exists('renderTagMenuItem')) {
    function renderTagMenuItem($tag, $Html, $currentUrl) {
        $url = ['controller' => 'Tags', 'action' => 'view-by-slug', $tag->slug];

        // Capitalize each word in the title
        $title = Inflector::humanize($tag->title);

        // Determine if the current URL matches the menu item's URL
        $isActive = $currentUrl === $Html->Url->build($url);

        // Set the class for the menu item, adding 'active-dark-grey' if it's the current URL
        $class = 'list-group-item list-group-item-action' . ($isActive ? ' active-light-grey' : '');

        echo $Html->link(
            htmlspecialchars_decode($title),
            $url,
            [
                'class' => $class
            ]
        );
    }
}
?>

<div class="list-group">
    <div class="list-group">
        <?php
        // Get the current URL
        $currentUrl = $this->request->getPath();

        // Add the Home link as the top root node with Bootstrap primary color
        echo $this->Html->link(
            __('Blog'),
            ['controller' => 'Articles', 'action' => 'index'],
            [
                'class' => 'list-group-item list-group-item-action bg-primary text-white'
            ]
        );
        ?>
        <?php foreach ($tagTreeMenu as $tag): ?>
            <?php renderTagMenuItem($tag, $this->Html, $currentUrl); ?>
        <?php endforeach; ?>
    </div>
</div>