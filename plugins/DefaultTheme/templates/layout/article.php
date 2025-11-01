<!doctype html>
  <html lang="<?= $this->request->getParam('lang', 'en') ?>" data-bs-theme="auto">
    <head>
<?= $this->element('site/layout_head') ?>
      <?= $this->element('libraries/highlightjs'); ?>
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
            <div role="main" aria-label="<?= __('Article content') ?>">
                <?= $this->Flash->render() ?>
                <?= $this->fetch('content') ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="sidebar-content">
                <div class="d-none d-lg-block position-sticky" style="top: 2rem;">

                <?= $this->cell('DefaultTheme.FeaturedPosts') ?>

                <?= $this->cell('DefaultTheme.RecentPosts') ?>

                <?= $this->element('site/elsewhere') ?>

                </div>

                <!-- Mobile sidebar (visible on smaller screens) -->
                <div class="d-lg-none mt-4">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $this->cell('DefaultTheme.FeaturedPosts') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $this->cell('DefaultTheme.RecentPosts') ?>
                        </div>
                    </div>
                    <?= $this->element('site/elsewhere') ?>
                </div>
            </div>
        </div>
      </div>

    </main>

<?= $this->element('site/layout_scripts') ?>
  </body>
</html>