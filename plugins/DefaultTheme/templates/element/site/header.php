<?php use App\Utility\SettingsManager; ?>
<header class="border-bottom lh-1 py-3">
  <div class="row flex-nowrap justify-content-between align-items-center">
    <div class="col-4 pt-1">
      <?= $this->element('site_language', ['languages' => $siteLanguages, 'selectedSiteLanguage' => $selectedSiteLanguage]) ?>
    </div>
    <div class="flex-nowrap col-4 text-center">
      <a class="blog-header-logo text-body-emphasis text-decoration-none" href="#"><?= SettingsManager::read('SEO.siteName', 'Willow CMS') ?></a>
    </div>
    <div class="col-4 d-flex justify-content-end align-items-center">
      <?php if ($this->Identity->isLoggedIn()): ?>

        <?php if ($this->Identity->get('is_admin')): ?>
          <?= $this->Html->link(__('Admin'), ['prefix' => 'Admin', 'controller' => 'Articles', 'action' => 'index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?php endif; ?>

        <?= $this->Html->link(__('Logout'), ['_name' => 'logout'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>

      <?php else: ?>

        <?= $this->Html->link(__('Log In'), ['controller' => 'Users', 'action' => 'login'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?php if (SettingsManager::read('Users.registrationEnabled', false)) :?>
          <?= $this->Html->link(__('Register'), ['controller' => 'Users', 'action' => 'register'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?php endif; ?>

      <?php endif; ?>
    </div>
  </div>
</header>