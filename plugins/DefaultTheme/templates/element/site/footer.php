<?php use App\Utility\SettingsManager; ?>
<footer class="py-5 text-center text-body-secondary bg-body-tertiary">
    <p>&copy; <?= date('Y') ?> <?= SettingsManager::read('SEO.siteName', 'Willow CMS') ?>. <?= __('All rights reserved.') ?></p>
    <p class="mb-0">
    <a href="#"><?= __('Back to top') ?></a>
    </p>
</footer>