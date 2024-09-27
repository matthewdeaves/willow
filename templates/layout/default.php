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
</head>
<body>
    <nav class="top-nav">
        <div class="top-nav-title">
            <a href="<?= $this->Url->build('/') ?>"><span>Cake</span>PHP</a>
        </div>
        <div class="top-nav-links">
            <?= $this->Html->link('Articles', ['prefix' => false, 'controller' => 'Articles', 'action' => 'index']) ?>
            <?= $this->Html->link('Pages', ['prefix' => false, 'controller' => 'Articles', 'action' => 'pageIndex']) ?>
            <?= $this->Html->link('Tags', ['prefix' => false, 'controller' => 'Tags', 'action' => 'index']) ?>
            <?php if ($isLoggedIn): ?>
                <?= $this->Html->link('Account', ['prefix' => false, 'controller' => 'Users', 'action' => 'edit', $this->request->getAttribute('identity')->getIdentifier()]) ?>
            <?php endif; ?>
        </div>
        <div class="top-nav">
            <?php if ($isLoggedIn): ?>
                <?= $this->Html->link('Logout', ['prefix' => false, 'controller' => 'Users', 'action' => 'logout'], ['class' => 'button float-right']) ?>
            <?php else: ?>
                <?= $this->Html->link('Login', ['prefix' => false, 'controller' => 'Users', 'action' => 'login'], ['class' => 'button float-right']) ?>
                <?= $this->Html->link('Register', ['prefix' => false, 'controller' => 'Users', 'action' => 'register'], ['class' => 'button float-right']) ?>
            <?php endif; ?>
        </div>
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
