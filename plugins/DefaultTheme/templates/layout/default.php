<?php use App\Utility\SettingsManager; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?= $this->element('meta_tags', ['model' => $article ?? $tag ?? null]) ?>
    
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css']) ?>
    <?= $this->Html->css('/DefaultTheme/css/willow.css') ?>
    <?= $this->Html->script(['https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <?= $this->Html->link(SettingsManager::read('SEO.siteStrapline', 'Default strapline'), '/', ['class' => 'navbar-brand']) ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <?= $this->element('page_main_menu', ['articleTreeMenu' => $articleTreeMenu]) ?>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <?= $this->Html->link(__('Tags'), ['controller' => 'Tags', 'action' => 'index'], ['class' => 'nav-link']) ?>
                    </li>
                    <?php if ($this->Identity->isLoggedIn()): ?>
                        <li class="nav-item">
                            <?= $this->Html->link(__('Account'), ['controller' => 'Users', 'action' => 'edit', $this->Identity->get('id')], ['class' => 'nav-link']) ?>
                        </li>
                        <?php if ($this->Identity->get('is_admin')): ?>
                            <li class="nav-item">
                                <?= $this->Html->link(__('Admin'), ['prefix' => 'Admin', 'controller' => 'Articles', 'action' => 'index'], ['class' => 'nav-link']) ?>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <?= $this->Html->link(__('Logout'), ['_name' => 'logout'], ['class' => 'nav-link']) ?>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <?= $this->Html->link(__('Login'), ['controller' => 'Users', 'action' => 'login'], ['class' => 'nav-link']) ?>
                        </li>
                        <?php if (SettingsManager::read('Users.registrationEnabled', false)) :?>
                        <li class="nav-item">
                            <?= $this->Html->link(__('Register'), ['controller' => 'Users', 'action' => 'register'], ['class' => 'nav-link']) ?>
                        </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container-fluid pt-3">
        <div class="container-fluid">
            <?php if(!empty($filterTags)) : ?>
                <div class="collapse d-none d-lg-block" id="sidebarMenu">
                    <?= $this->element('tag_filters', ['tags' => $filterTags, 'selectedTagId' => $selectedTagId]) ?>
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-lg-2 mb-4">
                    <div class="collapse d-none d-lg-block" id="sidebarMenu">
                        <?= $this->element('page_menu', ['articleTreeMenu' => $articleTreeMenu]) ?>
                        <?= $this->element('tag_menu', ['tags' => $tagTreeMenu]) ?>
                    </div>
                </div>
                <div class="col-lg-10">
                    <?= $this->Flash->render() ?>
                    <?= $this->fetch('content') ?>
                </div>
            </div>
        </div>
    </main>
    <footer class="footer mt-auto py-3 bg-dark">
        <div class="container text-center">
            <?= $this->element('site_language', ['languages' => $siteLanguages, 'selectedSiteLanguage' => $selectedSiteLanguage]) ?>
        </div>
        <div class="container text-center">
            <span class="text-white">&copy; <?= date('Y') ?> <?= SettingsManager::read('SEO.siteName', 'Willow CMS') ?>. <?= __('All rights reserved.') ?></span>
        </div>
    </footer>
    <?= $this->fetch('script') ?>
</body>
</html>