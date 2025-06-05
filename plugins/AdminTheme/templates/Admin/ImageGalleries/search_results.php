<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\ImageGallery> $imageGalleries
 * @var string $viewType
 * @var string|null $search
 * @var string|null $statusFilter
 */

if ($viewType === 'grid'): ?>
    <!-- Grid View Results -->
    <?php if (empty($imageGalleries)): ?>
        <?= $this->element('empty_state', [
            'type' => 'search',
            'title' => __('No galleries found'),
            'message' => __('Try adjusting your search terms or filters.')
        ]) ?>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            <?php foreach ($imageGalleries as $gallery): ?>
            <div class="col">
                <div class="card h-100 gallery-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><?= h($gallery->name) ?></h6>
                        <?= $this->Gallery->statusBadge($gallery) ?>
                    </div>
                    
                    <div class="card-body p-0">
                        <?php if (!empty($gallery->images)): ?>
                            <!-- Use photo_gallery element for inline slideshow -->
                            <div class="position-relative">
                                <!-- Preview image overlay -->
                                <?php if ($gallery->hasPreviewImage()): ?>
                                    <div class="gallery-preview-overlay" data-gallery-id="gallery-<?= $gallery->id ?>">
                                        <img src="<?= h($gallery->getPreviewImageUrl()) ?>" 
                                             alt="<?= h($gallery->name) ?>"
                                             class="gallery-preview-image">
                                        
                                        <!-- Image count overlay -->
                                        <div class="gallery-image-count">
                                            <i class="fas fa-images me-1"></i><?= $gallery->getImageCount() ?>
                                        </div>
                                        
                                        <!-- Play button overlay -->
                                        <div class="position-absolute top-50 start-50 translate-middle gallery-play-button">
                                            <i class="fas fa-play-circle fa-3x text-white"></i>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Hidden photo gallery for slideshow -->
                                <div class="d-none">
                                    <?= $this->element('shared_photo_gallery', [
                                        'images' => $gallery->images,
                                        'title' => $gallery->name,
                                        'gallery_id' => 'gallery-' . $gallery->id,
                                        'theme' => 'admin'
                                    ]) ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- No images state -->
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-images fa-2x mb-2"></i>
                                <p><?= __('No images') ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Gallery info -->
                        <div class="p-3">
                            <?php if ($gallery->description): ?>
                                <p class="card-text small">
                                    <?= $this->Text->truncate(h($gallery->description), 100) ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <strong><?= __('Slug') ?>:</strong> <code><?= h($gallery->slug) ?></code>
                                </small>
                                <small class="text-muted">
                                    <?= $gallery->created->format('M j, Y') ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="d-flex gap-2">
                            <?= $this->Html->link(
                                '<i class="fas fa-eye"></i> ' . __('View'),
                                ['action' => 'view', $gallery->id],
                                ['class' => 'btn btn-outline-primary btn-sm flex-fill', 'escape' => false]
                            ) ?>
                            <?= $this->Html->link(
                                '<i class="fas fa-edit"></i> ' . __('Edit'),
                                ['action' => 'edit', $gallery->id],
                                ['class' => 'btn btn-outline-secondary btn-sm flex-fill', 'escape' => false]
                            ) ?>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><?= $this->Html->link(__('Manage Images'), 
                                        ['action' => 'manageImages', $gallery->id], 
                                        ['class' => 'dropdown-item']) ?></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><?= $this->Form->postLink(__('Delete'), 
                                        ['action' => 'delete', $gallery->id], 
                                        ['class' => 'dropdown-item text-danger', 'confirm' => __('Are you sure you want to delete this gallery?')]) ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?= $this->element('pagination') ?>
    <?php endif; ?>

<?php else: ?>
    <!-- List View Results -->
    <?php if (empty($imageGalleries)): ?>
        <?= $this->element('empty_state', [
            'type' => 'search',
            'title' => __('No galleries found'),
            'message' => __('Try adjusting your search terms or filters.')
        ]) ?>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th><?= __('Preview') ?></th>
                        <th><?= $this->Paginator->sort('name') ?></th>
                        <th><?= $this->Paginator->sort('slug') ?></th>
                        <th><?= __('Status') ?></th>
                        <th><?= __('Images') ?></th>
                        <th><?= $this->Paginator->sort('created') ?></th>
                        <th class="actions"><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($imageGalleries as $gallery): ?>
                    <tr>
                        <td>
                            <?php if ($gallery->hasPreviewImage()): ?>
                                <img src="<?= h($gallery->getPreviewImageUrl()) ?>" 
                                     alt="<?= h($gallery->name) ?>"
                                     class="img-thumbnail gallery-preview-thumb"
                                     style="width: 60px; height: 45px; object-fit: cover;"
                                     data-bs-toggle="popover"
                                     data-bs-trigger="hover"
                                     data-bs-content="<img src='<?= h($gallery->getPreviewImageUrl()) ?>' style='max-width: 300px; max-height: 200px;' alt='<?= h($gallery->name) ?>'>"
                                     data-bs-html="true"
                                     data-bs-placement="right">
                            <?php elseif (!empty($gallery->images)): ?>
                                <?= $this->element('image/icon', [
                                    'model' => $gallery->images[0],
                                    'icon' => $gallery->images[0]->tinyImageUrl ?? null,
                                    'class' => 'gallery-preview-thumb',
                                    'style' => 'width: 60px; height: 45px; object-fit: cover;',
                                    'popover' => true,
                                    'popover_content' => $this->element('shared_photo_gallery', [
                                        'images' => array_slice($gallery->images, 0, 4),
                                        'gallery_id' => 'preview-' . $gallery->id,
                                        'grid_class' => 'row g-1',
                                        'image_class' => 'col-6'
                                    ])
                                ]) ?>
                            <?php else: ?>
                                <div class="text-center text-muted d-flex align-items-center justify-content-center" 
                                     style="width: 60px; height: 45px; border: 1px solid #ddd; border-radius: 4px;">
                                    <i class="fas fa-images"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= h($gallery->name) ?></strong>
                            <?php if ($gallery->description): ?>
                                <br><small class="text-muted"><?= $this->Text->truncate(h($gallery->description), 50) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><code><?= h($gallery->slug) ?></code></td>
                        <td>
                            <?= $this->Gallery->statusBadge($gallery) ?>
                        </td>
                        <td>
                            <?= $this->Gallery->imageCountBadge($gallery) ?>
                        </td>
                        <td><?= $gallery->created->format('M j, Y') ?></td>
                        <td>
                            <?= $this->element('evd_dropdown', [
                                'model' => $gallery,
                                'display' => 'name',
                                'controller' => 'ImageGalleries'
                            ]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?= $this->element('pagination') ?>
    <?php endif; ?>
<?php endif; ?>