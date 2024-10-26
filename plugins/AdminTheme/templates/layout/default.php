<?php 
use App\Utility\SettingsManager;
use Cake\Core\Configure;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= SettingsManager::read('SEO.siteName', 'Willow CMS') ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">

    <?= $this->Html->css(['https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css']) ?>
    <?= $this->Html->script(['https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js']) ?>
    <?= $this->Html->script('AdminTheme.image-preview') ?>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
    <?= $this->Html->scriptBlock(sprintf(
        'var csrfToken = %s;',
        json_encode($this->request->getAttribute('csrfToken'))
    )); ?>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><?= SettingsManager::read('SEO.siteName', 'Willow CMS') ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <?= $this->Html->link(__('Articles'),
                        ['prefix' => 'Admin', 'controller' => 'Articles', 'action' => 'index'], ['class' => 'nav-link']) ?>
                    </li>
                    <li class="nav-item">
                        <?= $this->Html->link(__('Pages'),
                        ['prefix' => 'Admin', 'controller' => 'Articles', 'action' => 'treeIndex'], ['class' => 'nav-link']) ?>
                    </li>
                    <li class="nav-item">
                        <?= $this->Html->link(__('Tags'),
                        ['prefix' => 'Admin', 'controller' => 'Tags', 'action' => 'index'], ['class' => 'nav-link']) ?>
                    </li>
                    <li class="nav-item">
                        <?= $this->Html->link(__('Images'),
                        ['prefix' => 'Admin', 'controller' => 'Images', 'action' => 'index', '?' => ['view' => 'grid']], ['class' => 'nav-link']) ?>
                    </li>
                    <li class="nav-item">
                        <?= $this->Html->link(__('Comments'),
                        ['prefix' => 'Admin', 'controller' => 'Comments', 'action' => 'index'], ['class' => 'nav-link']) ?>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="systemDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= __('Admin') ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="systemDropdown">
                            <li><?= $this->Html->link(__('Users'), ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                            <li><?= $this->Html->link(__('Email Templates'), ['prefix' => 'Admin', 'controller' => 'EmailTemplates', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                            <li><?= $this->Html->link(__('Slugs'), ['prefix' => 'Admin', 'controller' => 'slugs', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                            <li><?= $this->Html->link(__('AI Prompts'), ['prefix' => 'Admin', 'controller' => 'Aiprompts', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                            <li><?= $this->Html->link(__('Cache'), ['prefix' => 'Admin', 'controller' => 'Cache', 'action' => 'clearAll'], ['class' => 'dropdown-item']) ?></li>
                            <li><?= $this->Html->link(__('Block IPs'), ['prefix' => 'Admin', 'controller' => 'BlockedIps', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                            <li><?= $this->Html->link(__('Logs'), ['prefix' => 'Admin', 'controller' => 'SystemLogs', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                            <li><?= $this->Html->link(__('Settings'), ['prefix' => 'Admin', 'controller' => 'Settings', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                            <?php if (Configure::read('debug')) : ?>
                            <li><?= $this->Html->link(__('Code Coverage'), 'coverage/src/index.html', ['class' => 'dropdown-item']) ?></li>
                            <?php endif; ?>  
                        </ul>
                    </li>
                    <?php if ($this->Identity->isLoggedIn()): ?>
                    <li class="nav-item">
                    <?= $this->Html->link(__('Front Site'), '/', ['class' => 'nav-link']) ?>
                    </li>
                    <li class="nav-item">
                        <?= $this->Html->link(__('Logout'), ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'logout'], ['class' => 'nav-link']) ?>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="main">
        <div class="container mt-3">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <footer>
    </footer>
</body>
</html>