<?php use App\Utility\SettingsManager; ?>
<div class="mb-3">
    <?php echo $this->Form->control('title', ['class' => 'form-control' . ($this->Form->isFieldError('title') ? ' is-invalid' : '')]); ?>
        <?php if ($this->Form->isFieldError('title')): ?>
        <div class="invalid-feedback">
            <?= $this->Form->error('title') ?>
        </div>
    <?php endif; ?>
</div>

<div class="mb-3">
    <?php echo $this->Form->control('slug', ['class' => 'form-control' . ($this->Form->isFieldError('slug') ? ' is-invalid' : '')]); ?>
        <?php if ($this->Form->isFieldError('slug')): ?>
        <div class="invalid-feedback">
            <?= $this->Form->error('slug') ?>
        </div>
    <?php endif; ?>
</div>

<div class="mb-3">
    <?php echo $this->Form->control('description', ['class' => 'form-control' . ($this->Form->isFieldError('description') ? ' is-invalid' : '')]); ?>
        <?php if ($this->Form->isFieldError('description')): ?>
        <div class="invalid-feedback">
            <?= $this->Form->error('description') ?>
        </div>
    <?php endif; ?>
</div>

<div class="mb-3">
    <?= $this->Form->control('image', [
        'type' => 'file',
        'label' => [
            'text' => __('Main Image'),
            'class' => 'form-label'
        ],
        'class' => 'form-control' . ($this->Form->isFieldError('image') ? ' is-invalid' : ''),
        'id' => 'customFile'
    ]) ?>
    <?php if ($this->Form->isFieldError('image')): ?>
        <div class="invalid-feedback">
            <?= $this->Form->error('image') ?>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($tag->image)): ?>
    <div class="mb-3">
    <?= $this->Html->image(SettingsManager::read('ImageSizes.teeny', '200') . '/' . $tag->image, 
        [
            'pathPrefix' => 'files/Tags/image/',
            'alt' => $tag->alt_text,
            'class' => 'img-thumbnail',
            'data-bs-toggle' => 'popover',
            'data-bs-trigger' => 'hover',
            'data-bs-html' => 'true',
            'data-bs-content' => $this->Html->image(SettingsManager::read('ImageSizes.extra-large', '400') . '/' . $tag->image,
                ['pathPrefix' => 'files/Tags/image/',
                'alt' => $tag->alt_text,
                'class' => 'img-fluid',
                'style' => 'max-width: 300px; max-height: 300px;'
        ])]) ?>
    </div>
<?php endif; ?>

<div class="me-3">
    <?php echo $this->Form->label('articles._ids', __('Tag Articles/Pages'), ['class' => 'form-label']); ?>
    <?php echo $this->Form->select('articles._ids', $articles, [
        'multiple' => true,
        'data-live-search' => 'true',
        'data-actions-box' => 'true',
        'id' => 'articles-select',
        'class' => 'w-100 form-select' . ($this->Form->isFieldError('articles._ids') ? ' is-invalid' : '')
    ]); ?>
    <?php if ($this->Form->isFieldError('articles._ids')): ?>
        <div class="invalid-feedback">
            <?= $this->Form->error('articles._ids') ?>
        </div>
    <?php endif; ?>
</div>