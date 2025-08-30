<?php
$this->extend('./site');
$this->append('main_menu');
echo $this->element('site/main_menu', ['mbAmount' => 3]);
$this->end();
?>

<div class="row g-5">
    <div class="col-md-8">
        <?= $this->fetch('content') ?>
    </div>

    <div class="col-md-4">
        <div class="position-sticky" style="top: 2rem;">

            <?= $this->element('site/articles_list', ['articles' => $childPages, 'title' => __('Related pages')]) ?>

            <?= $this->element('site/articles_list', ['articles' => $featuredArticles, 'title' => __('Featured posts')]) ?>

            <?= $this->element('site/articles_list', ['articles' => $recentArticles, 'title' => __('Recent posts')]) ?>

            <?= $this->element('site/elsewhere') ?>

        </div>
    </div>
</div>
