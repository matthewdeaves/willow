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
                <?= $this->Gallery->galleryCard($gallery) ?>
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
                            <?= $this->Gallery->previewImage($gallery, [
                                'size' => 'thumbnail',
                                'class' => 'img-thumbnail gallery-preview-thumb',
                                'style' => 'width: 60px; height: 45px; object-fit: cover;',
                                'popover' => true
                            ]) ?>
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