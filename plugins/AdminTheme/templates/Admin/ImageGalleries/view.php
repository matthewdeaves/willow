<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ImageGallery $imageGallery
 */
?>
<style>
/* Fix dropdown z-index issue in tables */
.table-responsive {
    overflow: visible !important;
}

.table .dropdown-menu {
    z-index: 1050 !important;
    position: absolute !important;
}

.table .dropdown {
    position: static;
}

.table .btn-group {
    position: relative;
}
</style>
<?php
echo $this->element('actions_card', [
    'modelName' => 'Image Gallery',
    'controllerName' => 'Image Galleries',
    'entity' => $imageGallery,
    'entityDisplayName' => $imageGallery->name
]);
?>
<div class="container my-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($imageGallery->name) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($imageGallery->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Slug') ?></th>
                            <td><?= h($imageGallery->slug) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($imageGallery->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($imageGallery->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is Published') ?></th>
                            <td><?= $imageGallery->is_published ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                    </table>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Description') ?></h5>
                            <p class="card-text"><?= html_entity_decode($imageGallery->description); ?></p>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-body">
                            <?= $this->element('shared_photo_gallery', [
                                'images' => $imageGallery->images,
                                'title' => __('Gallery Images'),
                                'theme' => 'admin',
                                'showActions' => false,
                                'showBulkActions' => false,
                                'galleryId' => $imageGallery->id
                            ]) ?>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title"><?= __('Related Slugs') ?></h4>
                            <?php if (!empty($imageGallery->slugs)) : ?>
                            <div class="table-responsive" style="overflow: visible;">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('Slug') ?></th>
                                            <th><?= __('Created') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($imageGallery->slugs as $slug) : ?>
                                        <tr>
                                            <td><?= h($slug->slug) ?></td>
                                            <td><?= h($slug->created) ?></td>
                                            <td class="actions">
                                                <?= $this->element('evd_dropdown', ['controller' => 'Slugs', 'model' => $slug, 'display' => 'slug']); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


