
<?php if (!empty($model->image)) : ?>
<?= $this->Html->image($icon, 
[
    'pathPrefix' => '', 
    'alt' => $model->alt_text, 
    'class' => 'img-thumbnail', 
    'data-bs-toggle' => 'popover',
    'data-bs-trigger' => 'hover',
    'data-bs-html' => 'true',
    'data-bs-content' => $this->Html->image(
    $preview, 
    [
        'pathPrefix' => '', 
        'alt' => $model->alt_text, 
        'class' => 'img-fluid', 
    ])
])?>
<?php endif; ?>