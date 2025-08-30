<?php
$this->extend('./site');
$this->append('script');
echo $this->element('libraries/highlightjs');
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
        <div role="main" aria-label="<?= __('Article content') ?>">
            <?= $this->fetch('content') ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="sidebar-content">
            <div class="d-none d-lg-block position-sticky" style="top: 2rem;">

            <?= $this->element('site/articles_list', ['articles' => $featuredArticles, 'title' => __('Featured posts')]) ?>

            <?= $this->element('site/articles_list', ['articles' => $recentArticles, 'title' => __('Recent posts')]) ?>

            <?= $this->element('site/elsewhere') ?>

            </div>
            
            <!-- Mobile sidebar (visible on smaller screens) -->
            <div class="d-lg-none mt-4">
                <div class="row">
                    <div class="col-md-6">
                        <?= $this->element('site/articles_list', ['articles' => $featuredArticles, 'title' => __('Featured posts')]) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $this->element('site/articles_list', ['articles' => $recentArticles, 'title' => __('Recent posts')]) ?>
                    </div>
                </div>
                <?= $this->element('site/elsewhere') ?>
            </div>
        </div>
    </div>
</div>
