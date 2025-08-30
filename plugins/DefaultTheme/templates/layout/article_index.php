<?php use Cake\Routing\Router; ?>
<?php
$this->extend('./site');
$this->append('meta');
echo $this->Html->meta([
    'link' => Router::url([
        '_name' => 'rss',
    ], true),
    'type' => 'application/rss+xml',
    'title' => __('Latest Articles RSS Feed'),
    'rel' => 'alternate',
]);
$this->end();
$this->append('tags_menu');
echo $this->element('site/tags');
$this->end();
$this->append('main_menu');
echo $this->element('site/main_menu');
$this->end();
?>

<div class="row g-5">
    <div class="col-lg-8">
        <div role="main" aria-label="<?= __('Article list') ?>">
            <?= $this->fetch('content') ?>
            <?= $this->element('pagination', ['recordCount' => count($articles)]) ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="sidebar-content">
            <div class="d-none d-lg-block position-sticky" style="top: 2rem;">

                <div class="p-4 mb-3 bg-body-tertiary rounded">
                    <h4 class="fst-italic"><?= __('About') ?></h4>
                    <p class="mb-0"><?= __("Welcome to willowcms.app. This site uses Willow - a content management system I'm building in the open. Here you'll find development updates, feature highlights, and guides on using Willow for your own sites.") ?></p>
                </div>

                <?= $this->element('site/articles_list', ['articles' => $featuredArticles, 'title' => __('Featured posts')]) ?>

                <?= $this->element('site/articles_list', ['articles' => $recentArticles, 'title' => __('Recent posts')]) ?>

                <?= $this->element('site/archives') ?>

                <?= $this->element('site/elsewhere') ?>

                <?= $this->element('site/feeds') ?>

            </div>
            
            <!-- Mobile sidebar (visible on smaller screens) -->
            <div class="d-lg-none mt-4">
                <div class="p-4 mb-3 bg-body-tertiary rounded">
                    <h4 class="fst-italic"><?= __('About') ?></h4>
                    <p class="mb-0"><?= __("Welcome to willowcms.app. This site uses Willow - a content management system I'm building in the open. Here you'll find development updates, feature highlights, and guides on using Willow for your own sites.") ?></p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <?= $this->element('site/articles_list', ['articles' => $featuredArticles, 'title' => __('Featured posts')]) ?>
                        <?= $this->element('site/archives') ?>
                    </div>
                    <div class="col-md-6">
                        <?= $this->element('site/articles_list', ['articles' => $recentArticles, 'title' => __('Recent posts')]) ?>
                        <?= $this->element('site/elsewhere') ?>
                    </div>
                </div>
                <?= $this->element('site/feeds') ?>
            </div>
        </div>
    </div>
</div>
