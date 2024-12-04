<?php if (!empty($model->image)) : ?>
    <?php 
    // Base image options
    $imageOptions = [
        'pathPrefix' => '', 
        'alt' => $model->alt_text, 
        'class' => 'img-thumbnail'
    ];
    
    // Only add popover options if preview is provided
    if ($preview !== false) {
        $imageOptions = array_merge($imageOptions, [
            'data-bs-toggle' => 'popover',
            'data-bs-trigger' => 'hover',
            'data-bs-html' => 'true',
            'data-bs-content' => $this->Html->image(
                $preview, 
                [
                    'pathPrefix' => '', 
                    'alt' => $model->alt_text, 
                    'class' => 'img-fluid', 
                ]
            )
        ]);
    }
    ?>
    <?= $this->Html->image($icon, $imageOptions) ?>
<?php endif; ?>