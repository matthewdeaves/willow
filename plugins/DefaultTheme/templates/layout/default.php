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

    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
      <symbol id="check2" viewBox="0 0 16 16">
        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
      </symbol>
      <symbol id="circle-half" viewBox="0 0 16 16">
        <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
      </symbol>
      <symbol id="moon-stars-fill" viewBox="0 0 16 16">
        <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/>
        <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>
      </symbol>
      <symbol id="sun-fill" viewBox="0 0 16 16">
        <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
      </symbol>
    </svg>

    <div class="dropdown position-fixed bottom-0 end-0 mb-3 me-3 bd-mode-toggle">
      <button class="btn btn-bd-primary py-2 dropdown-toggle d-flex align-items-center"
              id="bd-theme"
              type="button"
              aria-expanded="false"
              data-bs-toggle="dropdown"
              aria-label="Toggle theme (auto)">
        <svg class="bi my-1 theme-icon-active" width="1em" height="1em"><use href="#circle-half"></use></svg>
        <span class="visually-hidden" id="bd-theme-text"><?= __('Toggle theme') ?></span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
            <svg class="bi me-2 opacity-50" width="1em" height="1em"><use href="#sun-fill"></use></svg>
            <?= __('Light') ?>
            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
            <svg class="bi me-2 opacity-50" width="1em" height="1em"><use href="#moon-stars-fill"></use></svg>
            <?= __('Dark') ?>
            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">
            <svg class="bi me-2 opacity-50" width="1em" height="1em"><use href="#circle-half"></use></svg>
            <?= __('Auto') ?>
            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
          </button>
        </li>
      </ul>
    </div>

<svg xmlns="http://www.w3.org/2000/svg" class="d-none">
  <symbol id="aperture" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
    <circle cx="12" cy="12" r="10"/>
    <path d="M14.31 8l5.74 9.94M9.69 8h11.48M7.38 12l5.74-9.94M9.69 16L3.95 6.06M14.31 16H2.83m13.79-4l-5.74 9.94"/>
  </symbol>
  <symbol id="cart" viewBox="0 0 16 16">
    <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
  </symbol>
  <symbol id="chevron-right" viewBox="0 0 16 16">
    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
  </symbol>
</svg>

<div class="container">
  <header class="border-bottom lh-1 py-3">
    <div class="row flex-nowrap justify-content-between align-items-center">
      <div class="col-4 pt-1">
      </div>
      <div class="flex-nowrap col-4 text-center">
        <a class="blog-header-logo text-body-emphasis text-decoration-none" href="#"><?= SettingsManager::read('SEO.siteName', 'Willow CMS') ?></a>
      </div>
      <div class="col-4 d-flex justify-content-end align-items-center">
        <?= $this->Html->link(__('Log In'), ['controller' => 'Users', 'action' => 'login'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
      </div>
    </div>
  </header>
  <!-- Main Menu (blog and pages) -->
  <div class="nav-scroller py-1 border-bottom">
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
        <a class="nav-item nav-link link-body-emphasis" href="www.github.com/matthewdeaves/willow">GitHub</a>
    </nav>
  </div>
  <!-- Tags -->
  <div class="nav-scroller py-1 mb-3 border-bottom">
    <nav class="nav nav-underline justify-content-between">
    <?= $this->Html->link(
    __('All'),
    [
        '_name' => 'home',
    ],
    [
        'class' => 'nav-item nav-link link-body-emphasis' . (!$selectedTagId ? ' active' : '')
    ]) ?>
    <?php foreach ($rootTags as $rootTag) : ?>
        <?= $this->Html->link(
            htmlspecialchars_decode($rootTag['title']),
            [
                '_name' => 'home',
                '?' => ['tag' => $rootTag['id']]
            ],
            ['class' => 'nav-item nav-link link-body-emphasis' . ($selectedTagId == $rootTag['id'] ? ' active' : '')]
        ) ?>
    <?php endforeach; ?>
    </nav>
  </div>
</div>
<main class="container">
  <div class="row g-5">
    <div class="col-md-8">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
        <?= $this->element('pagination', ['recordCount' => count($articles)]) ?>
    </div>

    <div class="col-md-4">
        <div class="position-sticky" style="top: 2rem;">

            <div class="p-4 mb-3 bg-body-tertiary rounded">
            <h4 class="fst-italic"><?= __('About') ?></h4>
            <p class="mb-0"><?= __("I'm building a content management system called Willow and I blog about related software development, features and how you can use it too.") ?></p>
            </div>

            <div>
            <h4 class="fst-italic"><?= __('Recent posts') ?></h4>
            <ul class="list-unstyled">
                <li>
                <a class="d-flex flex-column flex-lg-row gap-3 align-items-start align-items-lg-center py-3 link-body-emphasis text-decoration-none border-top" href="#">
                    <svg class="bd-placeholder-img" width="100%" height="96" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" preserveAspectRatio="xMidYMid slice" focusable="false"><rect width="100%" height="100%" fill="#777"/></svg>
                    <div class="col-lg-8">
                    <h6 class="mb-0">Example blog post title</h6>
                    <small class="text-body-secondary">January 15, 2024</small>
                    </div>
                </a>
                </li>
            </ul>
            </div>
            <?php if (!empty($featuredArticles)) : ?>
            <div>
            <h4 class="fst-italic"><?= __('Featured posts') ?></h4>
            <ul class="list-unstyled">
                <?php foreach ($featuredArticles as $featuredArticle) : ?>
                <li>
                <a class="d-flex flex-column flex-lg-row gap-3 align-items-start align-items-lg-center py-3 link-body-emphasis text-decoration-none border-top" href="#">
                    <svg class="bd-placeholder-img" width="100%" height="96" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" preserveAspectRatio="xMidYMid slice" focusable="false"><rect width="100%" height="100%" fill="#777"/></svg>
                    <div class="col-lg-8">
                    <h6 class="mb-0"><?= $featuredArticle->title ?></h6>
                    <small class="text-body-secondary">January 15, 2024</small>
                    </div>
                </a>
                </li>
                <?php endforeach; ?>
            </ul>
            </div>
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

            <div class="p-4">
            <h4 class="fst-italic"><?= __('Elsewhere') ?></h4>
            <ol class="list-unstyled">
                <li><a href="https://www.github.com/matthewdeaves/willow">GitHub</a></li>
            </ol>
            </div>

        </div>
    </div>
  </div>

</main>

<footer class="py-5 text-center text-body-secondary bg-body-tertiary">
  <p>&copy; <?= date('Y') ?> <?= SettingsManager::read('SEO.siteName', 'Willow CMS') ?>. <?= __('All rights reserved.') ?></p>
  <p class="mb-0">
    <a href="#"><?= __('Back to top') ?></a>
  </p>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>