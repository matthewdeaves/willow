<?php use Cake\Routing\Router; ?>
<!doctype html>
<html lang="<?= $this->request->getParam('lang', 'en') ?>" data-bs-theme="auto">
  <head>
<?= $this->element('site/layout_head') ?>
    <?= $this->Html->meta([
        'link' => Router::url([
            '_name' => 'rss'
        ], true),
        'type' => 'application/rss+xml',
        'title' => __('Latest Articles RSS Feed'),
        'rel' => 'alternate'
    ]); ?>
  </head>
  <body>
      <?php if (!empty($consentData) && $consentData['marketing_consent']) :?>
      <?= $this->element('site/facebook/sdk') ?>
      <?php endif; ?>

    <?= $this->element('site/bootstrap') ?>

    <div class="container">

      <?= $this->element('site/header'); ?>

      <?= $this->element('site/main_menu'); ?>

      <?= $this->element('site/tags'); ?>

    </div>

    <main class="container" id="main-content">
      <div class="row g-5">
        
        <div class="col-lg-8">
            <div role="main" aria-label="<?= __('Article list') ?>">
                <?= $this->Flash->render() ?>
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

                <?= $this->cell('DefaultTheme.FeaturedPosts') ?>

                <?= $this->cell('DefaultTheme.RecentPosts') ?>

                <?= $this->cell('DefaultTheme.Archives') ?>

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
                            <?= $this->cell('DefaultTheme.FeaturedPosts') ?>
                            <?= $this->cell('DefaultTheme.Archives') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $this->cell('DefaultTheme.RecentPosts') ?>
                            <?= $this->element('site/elsewhere') ?>
                        </div>
                    </div>
                    <?= $this->element('site/feeds') ?>
                </div>
            </div>
        </div>
      </div>

    </main>

<?= $this->element('site/layout_scripts') ?>
  </body>
</html>