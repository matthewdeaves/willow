<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="widthis this =device-width, initial-scale=1">
    <title>
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['normalize.min', 'milligram.min', 'fonts', 'cake']) ?>

    <?= $this->Html->css('logs') ?>
    
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
    <?= $this->Html->scriptBlock(sprintf(
        'var csrfToken = %s;',
        json_encode($this->request->getAttribute('csrfToken'))
    )); ?>
</head>
<body>
    <nav class="top-nav">
        <div class="top-nav-title">
            <a href="<?= $this->Url->build('/') ?>"><span>Cake</span>PHP</a>
        </div>
        <?php if ($isLoggedIn): ?>
            <div class="top-nav-links">
                <?= $this->Html->link('Images', ['prefix' => 'Admin', 'controller' => 'Images', 'action' => 'index']) ?>
                <?= $this->Html->link('Users', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index']) ?>
                <?= $this->Html->link('Articles', ['prefix' => 'Admin', 'controller' => 'Articles', 'action' => 'index']) ?>
                <?= $this->Html->link('Pages', ['prefix' => 'Admin', 'controller' => 'Articles', 'action' => 'treeIndex']) ?>
                <?= $this->Html->link('Tags', ['prefix' => 'Admin', 'controller' => 'Tags', 'action' => 'index']) ?>
                <?= $this->Html->link('Comments', ['prefix' => 'Admin', 'controller' => 'Comments', 'action' => 'index']) ?>
                <?= $this->Html->link('Settings', ['prefix' => 'Admin', 'controller' => 'Settings', 'action' => 'index']) ?>
                <?= $this->Html->link('Logs', ['prefix' => 'Admin', 'controller' => 'SystemLogs', 'action' => 'index']) ?>
                <?= $this->Html->link('Blocks', ['prefix' => 'Admin', 'controller' => 'BlockedIps', 'action' => 'index']) ?>
            </div>
        <?php endif; ?>
        <?php if ($isLoggedIn): ?>
            <?= $this->Html->link('Logout', ['prefix' => false, 'controller' => 'Users', 'action' => 'logout'], ['class' => 'button float-right']) ?>
        <?php else: ?>
            <?= $this->Html->link('Login', ['prefix' => false, 'controller' => 'Users', 'action' => 'login'], ['class' => 'button float-right']) ?>
        <?php endif; ?>
    </nav>
    <main class="main">
        <div class="container">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <footer>
    </footer>
</body>
</html>
