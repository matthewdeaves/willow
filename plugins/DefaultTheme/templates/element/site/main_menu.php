<?php $mbAmount = $mbAmount ?? 0; ?>
<?php $currentUrl = $this->request->getPath(); ?>

<div class="nav-scroller py-1 mb-<?= $mbAmount ?> border-bottom">
    <nav class="nav nav-underline justify-content-center">

        <?php $url = $this->Html->Url->build(['_name' => 'home']); ?>
        <?= $this->Html->link(__('Blog'), $url, [
            'class' => 'nav-item nav-link link-body-emphasis' . (($currentUrl == $url) ? ' active' : '')
        ]) ?>

        <?php foreach ($rootPages as $rootPage) : ?>
            <?php $url = $this->Html->Url->build(['_name' => 'page-by-slug', 'slug' => $rootPage['slug']]); ?>
            <?=
                $this->Html->link(
                    htmlspecialchars_decode($rootPage['title']),
                    $url,
                    [
                        'class' => 'nav-item nav-link link-body-emphasis' . (($currentUrl == $url) ? ' active' : ''),
                        'escape' => false
                    ]
                );
            ?>
        <?php endforeach ?>
        <a class="nav-item nav-link link-body-emphasis" href="https://www.github.com/matthewdeaves/willow">GitHub</a>
    </nav>
</div>