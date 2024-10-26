<?php
use Cake\Utility\Inflector;

if (!function_exists('renderTagMenuItem')) {
    function renderTagMenuItem($tag, $Html) {
        $url = ['controller' => 'Tags', 'action' => 'view-by-slug', $tag->slug];

        // Capitalize each word in the title
        $title = Inflector::humanize($tag->title);

        echo $Html->link(
            h($title),
            $url,
            [
                'class' => 'list-group-item list-group-item-action'
            ]
        );
    }
}
?>

<div class="list-group mt-4">
    <div class="list-group">
        <?php
        // Add the Home link as the top root node with Bootstrap primary color
        echo $this->Html->link(
            'Tags',
            ['controller' => 'Tags', 'action' => 'index'],
            [
                'class' => 'list-group-item list-group-item-action bg-primary text-white'
            ]
        );
        ?>
        <?php foreach ($tagTreeMenu as $tag): ?>
            <?php renderTagMenuItem($tag, $this->Html); ?>
        <?php endforeach; ?>
    </div>
</div>