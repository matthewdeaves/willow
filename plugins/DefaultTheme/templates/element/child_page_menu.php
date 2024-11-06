<div class="container-fluid">
    <ul class="navbar-nav justify-content-center">
        <?php foreach ($childPages as $childPage) : ?>
            <li class="nav-item">
                <?= $this->Html->link(
                    html_entity_decode($childPage->title),
                    ['_name' => 'page-by-slug', 'slug' => $childPage->slug],
                    ['class' => 'nav-link']
                ) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>