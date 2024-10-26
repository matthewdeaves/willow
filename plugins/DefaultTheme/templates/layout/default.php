<?php use App\Utility\SettingsManager; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?= $this->element('meta_tags', ['model' => $article ?? $tag ?? null]) ?>
    
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css']) ?>
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
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <?= $this->Html->link(__('Home'), ['_name' => 'home'], ['class' => 'nav-link']) ?>
                    </li>
                    <li class="nav-item">
                        <?= $this->Html->link('Tags', ['controller' => 'Tags', 'action' => 'index'], ['class' => 'nav-link']) ?>
                    </li>
                    <?php if ($this->Identity->isLoggedIn()): ?>
                        <li class="nav-item">
                            <?= $this->Html->link('Account', ['controller' => 'Users', 'action' => 'edit', $this->Identity->get('id')], ['class' => 'nav-link']) ?>
                        </li>
                        <?php if ($this->Identity->get('is_admin')): ?>
                            <li class="nav-item">
                                <?= $this->Html->link('Admin', ['prefix' => 'Admin', 'controller' => 'Articles', 'action' => 'index'], ['class' => 'nav-link']) ?>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <?= $this->Html->link(__('Logout'), ['_name' => 'logout'], ['class' => 'nav-link']) ?>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <?= $this->Html->link('Login', ['controller' => 'Users', 'action' => 'login'], ['class' => 'nav-link']) ?>
                        </li>
                        <li class="nav-item">
                            <?= $this->Html->link('Register', ['controller' => 'Users', 'action' => 'register'], ['class' => 'nav-link']) ?>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container-fluid pt-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    <?= $this->element('page_menu', ['articleTreeMenu' => $articleTreeMenu]) ?>
                    <?= $this->element('tag_menu', ['tags' => $tagTreeMenu]) ?>
                </div>
                <div class="col-md-9">
                    <?= $this->Flash->render() ?>
                    <?= $this->fetch('content') ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer mt-auto py-3 bg-dark">
        <div class="container text-center">
            <span class="text-white">&copy; <?= date('Y') ?> <?= SettingsManager::read('SEO.siteName', 'Willow CMS') ?>. All rights reserved.</span>
        </div>
    </footer>

    <?= $this->fetch('script') ?>
</body>
</html>