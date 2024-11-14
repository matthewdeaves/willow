<?php use App\Utility\SettingsManager; ?>
<!doctype html>
<html lang="en" data-bs-theme="auto">
  <head>
    <?= $this->Html->script('AdminTheme.color-modes') ?>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= $this->element('meta_tags', ['model' => $article ?? $tag ?? null]) ?>
    <title><?= SettingsManager::read('SEO.siteName', 'Willow CMS') ?>: <?= $this->fetch('title') ?></title>
    <link rel="canonical" href=""> <!-- do we need this -->
    <?= $this->Html->meta('icon') ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <?= $this->Html->css('DefaultTheme.willow') ?>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
    <link href="https://fonts.googleapis.com/css?family=Playfair&#43;Display:700,900&amp;display=swap" rel="stylesheet">
    <?= $this->Html->scriptBlock(sprintf(
        'var csrfToken = %s;',
        json_encode($this->request->getAttribute('csrfToken'))
    )); ?>
</head>
  <body>
    <?= $this->element('site/bootstrap') ?>

<div class="container">

  <?= $this->element('site/header'); ?>

  <?= $this->element('site/main_menu'); ?>

  <?= $this->element('site/tags'); ?>
</div>
<main class="container">
  <div class="row g-5">
    
    <div class="col-md-8">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
    </div>

    <div class="col-md-4">
        <div class="position-sticky" style="top: 2rem;">

            <div class="p-4 mb-3 bg-body-tertiary rounded">
            <h4 class="fst-italic"><?= __('About') ?></h4>
            <p class="mb-0"><?= __("I'm building a content management system called Willow and I blog about related software development, features and how you can use it too.") ?></p>
            </div>

            <?php if (!empty($featuredArticles)) : ?>
              <?= $this->element('site/articles_list', ['articles' => $featuredArticles, 'title' => __('Featured posts')]) ?>
            <?php endif; ?>

            <?php if (!empty($recentArticles)) : ?>
            <?= $this->element('site/articles_list', ['articles' => $recentArticles, 'title' => __('Recent posts')]) ?>
            <?php endif; ?>

            <div class="p-4">
            <h4 class="fst-italic"><?= __('Archives') ?></h4>
            <ol class="list-unstyled mb-0">
                <li><a href="#">March 2021</a></li>
                <li><a href="#">February 2021</a></li>
                <li><a href="#">January 2021</a></li>
                <li><a href="#">December 2020</a></li>
                <li><a href="#">November 2020</a></li>
            </ol>
            </div>

            <?= $this->element('site/elsewhere') ?>

        </div>
    </div>
  </div>

</main>

<?= $this->element('site/footer'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>