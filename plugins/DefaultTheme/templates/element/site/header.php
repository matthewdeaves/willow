<?php use App\Utility\SettingsManager; ?>
<header class="border-bottom lh-1 py-3">
  <div class="row flex-nowrap justify-content-between align-items-center">
    <div class="col-4 pt-1">
      <?= $this->element('site/site_language', ['languages' => $siteLanguages, 'selectedSiteLanguage' => $selectedSiteLanguage]) ?>
    </div>
    <div class="flex-nowrap col-4 text-center">
      <a class="blog-header-logo text-body-emphasis text-decoration-none" href="<?= $this->Url->build(['_name' => 'home']) ?>"><?= SettingsManager::read('SEO.siteName', 'Willow CMS') ?></a>
    </div>
    <div class="col-4 d-flex justify-content-end align-items-center">
        <?= $this->element('site/user_actions') ?>
    </div>
  </div>
</header>