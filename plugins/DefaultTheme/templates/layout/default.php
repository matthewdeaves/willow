<!doctype html>
<html lang="<?= $this->request->getParam('lang', 'en') ?>" data-bs-theme="auto">
  <head>
<?= $this->element('site/layout_head') ?>
  </head>
  <body>
      <?php if (!empty($consentData) && $consentData['marketing_consent']) :?>
        <?= $this->element('site/facebook/sdk') ?>
      <?php endif; ?>

    <?= $this->element('site/bootstrap') ?>

    <div class="container">

      <?= $this->element('site/header'); ?>

      <?= $this->element('site/main_menu', ['mbAmount' => 3]); ?>

    </div>
    <main class="container" id="main-content">
      <div class="row g-5">
        <div class="col-md-12">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
      </div>
    </main>

<?= $this->element('site/layout_scripts') ?>
  </body>
</html>