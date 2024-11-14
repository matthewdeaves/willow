<?php $mbAmount = $mbAmount ?? 0; ?>
<div class="nav-scroller py-1 mb-<?= $mbAmount ?> border-bottom">
    <nav class="nav nav-underline justify-content-center">
        <?= $this->Html->link(__('Blog'), ['_name' => 'home'], ['class' => 'nav-item nav-link link-body-emphasis active']) ?>
        <?php foreach ($rootPages as $rootPage) : ?>
            <?=
                $this->Html->link(
                    htmlspecialchars_decode($rootPage['title']),
                    ['_name' => 'page-by-slug', 'slug' => $rootPage['slug']],
                    [
                        'class' => 'nav-item nav-link link-body-emphasis',
                        'escape' => false
                    ]
                );
            ?>
        <?php endforeach ?>
        <a class="nav-item nav-link link-body-emphasis" href="https://www.github.com/matthewdeaves/willow">GitHub</a>
    </nav>
</div>