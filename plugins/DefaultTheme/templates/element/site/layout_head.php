  <?php if (!empty($consentData) && $consentData['analytics_consent']) :?>
    <?= $this->SiteConfig->googleTagManagerHead() ?>
    <?php endif; ?>
    <?= $this->Html->script('willow-modal') ?>
    <?= $this->Html->script('DefaultTheme.color-modes') ?>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= $this->element('site/meta_tags', ['model' => $article ?? $tag ?? null]) ?>
    <?= $this->MetaTags->title($this->fetch('title')) ?>
    <?= $this->Html->meta('icon') ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <?= $this->Html->css('DefaultTheme.willow') ?>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
    <link href="https://fonts.googleapis.com/css?family=Playfair&#43;Display:700,900&amp;display=swap" rel="stylesheet">
    <?= $this->Html->scriptBlock(sprintf(
        'var csrfToken = %s;',
        json_encode($this->request->getAttribute('csrfToken'))
    )); ?>
