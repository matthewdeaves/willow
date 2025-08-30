<?php ?>
<!doctype html>
<html lang="<?= $this->request->getParam('lang', 'en') ?>" data-bs-theme="auto">
  <head>
    <?= $this->element('layout/head') ?>
  </head>
  <body>
    <?php if (!empty($consentData) && $consentData['marketing_consent']) :?>
        <?= $this->element('site/facebook/sdk') ?>
    <?php endif; ?>

    <?= $this->element('site/bootstrap') ?>

    <div class="container">
      <?= $this->element('site/header'); ?>
      <?= $this->fetch('main_menu'); ?>

      <?= $this->fetch('tags_menu') ?>
    </div>

    <main class="container" id="main-content">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
    </main>

    <?= $this->element('site/footer'); ?>
    <?= $this->element('site/cookie_prefs'); ?>

    <?= $this->element('layout/scripts') ?>
  </body>
</html>
