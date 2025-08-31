<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 */
?>
<?php
echo $this->element('actions_card', [
    'modelName' => 'Product',
    'controllerName' => 'Products',
    'entity' => $product,
    'entityDisplayName' => $product->title
]);
?>
<div class="container my-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($product->title) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($product->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('User') ?></th>
                            <td><?= $product->hasValue('user') ? $this->Html->link($product->user->username, ['controller' => 'Users', 'action' => 'view', $product->user->id], ['class' => 'btn btn-link']) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Article') ?></th>
                            <td><?= $product->hasValue('article') ? $this->Html->link($product->article->title, ['controller' => 'Articles', 'action' => 'view', $product->article->id], ['class' => 'btn btn-link']) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Title') ?></th>
                            <td><?= h($product->title) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Slug') ?></th>
                            <td><?= h($product->slug) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Manufacturer') ?></th>
                            <td><?= h($product->manufacturer) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Model Number') ?></th>
                            <td><?= h($product->model_number) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Currency') ?></th>
                            <td><?= h($product->currency) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Image') ?></th>
                            <td><?= h($product->image) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Alt Text') ?></th>
                            <td><?= h($product->alt_text) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Verification Status') ?></th>
                            <td><?= h($product->verification_status) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Price') ?></th>
                            <td><?= $product->price === null ? '' : $this->Number->format($product->price) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Reliability Score') ?></th>
                            <td><?= $product->reliability_score === null ? '' : $this->Number->format($product->reliability_score) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('View Count') ?></th>
                            <td><?= $this->Number->format($product->view_count) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($product->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($product->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is Published') ?></th>
                            <td><?= $product->is_published ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Featured') ?></th>
                            <td><?= $product->featured ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                    </table>
                    
                    <!-- Capability Information -->
                    <div class="card mt-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-cogs"></i> <?= __('Capability Information') ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Capability Name') ?></th>
                                            <td><?= h($product->capability_name) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Capability Category') ?></th>
                                            <td><?= h($product->capability_category) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Capability Value') ?></th>
                                            <td><?= h($product->capability_value) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Testing Standard') ?></th>
                                            <td><?= h($product->testing_standard) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Certifying Organization') ?></th>
                                            <td><?= h($product->certifying_organization) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Numeric Rating') ?></th>
                                            <td><?= $product->numeric_rating ? number_format($product->numeric_rating, 2) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <?php if ($product->technical_specifications): ?>
                            <div class="mt-3">
                                <h6><?= __('Technical Specifications') ?></h6>
                                <div class="bg-light p-3">
                                    <pre><?= h(json_encode(json_decode($product->technical_specifications), JSON_PRETTY_PRINT)) ?></pre>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Port/Connector Information -->
                    <div class="card mt-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-plug"></i> <?= __('Port & Connector Information') ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Port Family') ?></th>
                                            <td><?= h($product->port_family) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Port Type Name') ?></th>
                                            <td><?= h($product->port_type_name) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Form Factor') ?></th>
                                            <td><?= h($product->form_factor) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Connector Gender') ?></th>
                                            <td><?= h($product->connector_gender) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-4">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Pin Count') ?></th>
                                            <td><?= $product->pin_count ? number_format($product->pin_count) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Data Pin Count') ?></th>
                                            <td><?= $product->data_pin_count ? number_format($product->data_pin_count) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Power Pin Count') ?></th>
                                            <td><?= $product->power_pin_count ? number_format($product->power_pin_count) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Ground Pin Count') ?></th>
                                            <td><?= $product->ground_pin_count ? number_format($product->ground_pin_count) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-4">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Endpoint Position') ?></th>
                                            <td><?= h($product->endpoint_position) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Is Detachable') ?></th>
                                            <td><?= $product->is_detachable ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-muted"></i>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Electrical Shielding') ?></th>
                                            <td><?= h($product->electrical_shielding) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Durability Cycles') ?></th>
                                            <td><?= $product->durability_cycles ? number_format($product->durability_cycles) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <?php if ($product->adapter_functionality): ?>
                            <div class="mt-3">
                                <h6><?= __('Adapter Functionality') ?></h6>
                                <div class="bg-light p-3">
                                    <?= nl2br(h($product->adapter_functionality)) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Device Compatibility -->
                    <div class="card mt-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-laptop"></i> <?= __('Device Compatibility') ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Device Category') ?></th>
                                            <td><?= h($product->device_category) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Device Brand') ?></th>
                                            <td><?= h($product->device_brand) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Device Model') ?></th>
                                            <td><?= h($product->device_model) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Compatibility Level') ?></th>
                                            <td>
                                                <?php if ($product->compatibility_level): ?>
                                                    <?php 
                                                    $compatClass = [
                                                        'Full' => 'success',
                                                        'Partial' => 'warning', 
                                                        'Limited' => 'info',
                                                        'Incompatible' => 'danger'
                                                    ][$product->compatibility_level] ?? 'secondary';
                                                    ?>
                                                    <span class="badge badge-<?= $compatClass ?>"><?= h($product->compatibility_level) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Performance Rating') ?></th>
                                            <td><?= $product->performance_rating ? number_format($product->performance_rating, 2) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('User Reported Rating') ?></th>
                                            <td><?= $product->user_reported_rating ? number_format($product->user_reported_rating, 2) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Verification Date') ?></th>
                                            <td><?= $product->verification_date ? $product->verification_date->format('M j, Y') : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Verified By') ?></th>
                                            <td><?= h($product->verified_by) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <?php if ($product->compatibility_notes): ?>
                            <div class="mt-3">
                                <h6><?= __('Compatibility Notes') ?></h6>
                                <div class="bg-light p-3">
                                    <?= nl2br(h($product->compatibility_notes)) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Physical Specifications -->
                    <div class="card mt-4">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="fas fa-ruler"></i> <?= __('Physical Specifications') ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Physical Spec Name') ?></th>
                                            <td><?= h($product->physical_spec_name) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Spec Type') ?></th>
                                            <td><?= h($product->spec_type) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Spec Value') ?></th>
                                            <td><?= h($product->spec_value) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Numeric Value') ?></th>
                                            <td><?= $product->numeric_value ? number_format($product->numeric_value, 3) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Measurement Unit') ?></th>
                                            <td><?= h($product->measurement_unit) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Max Voltage') ?></th>
                                            <td><?= $product->max_voltage ? number_format($product->max_voltage, 2) . ' V' : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Max Current') ?></th>
                                            <td><?= $product->max_current ? number_format($product->max_current, 2) . ' A' : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Physical Specs Summary') ?></th>
                                            <td><?= h($product->physical_specs_summary) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Introduced Date') ?></th>
                                            <td><?= $product->introduced_date ? $product->introduced_date->format('M j, Y') : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Deprecated Date') ?></th>
                                            <td><?= $product->deprecated_date ? $product->deprecated_date->format('M j, Y') : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <?php if ($product->spec_description): ?>
                            <div class="mt-3">
                                <h6><?= __('Specification Description') ?></h6>
                                <div class="bg-light p-3">
                                    <?= nl2br(h($product->spec_description)) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Category Information -->
                    <div class="card mt-4">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="fas fa-tags"></i> <?= __('Category Information') ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Parent Category Name') ?></th>
                                            <td><?= h($product->parent_category_name) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Category Icon') ?></th>
                                            <td>
                                                <?php if ($product->category_icon): ?>
                                                    <i class="<?= h($product->category_icon) ?>"></i> <?= h($product->category_icon) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Display Order') ?></th>
                                            <td><?= $product->display_order ? number_format($product->display_order) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Is Certified') ?></th>
                                            <td>
                                                <?= $product->is_certified ? '<i class="fas fa-certificate text-success"></i> Yes' : '<i class="fas fa-times text-muted"></i> No' ?>
                                                <?php if ($product->certification_date): ?>
                                                    <br><small class="text-muted"><?= $product->certification_date->format('M j, Y') ?></small>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Needs Normalization') ?></th>
                                            <td><?= $product->needs_normalization ? '<i class="fas fa-exclamation-triangle text-warning"></i> Yes' : '<i class="fas fa-check text-success"></i> No' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <?php if ($product->category_description): ?>
                            <div class="mt-3">
                                <h6><?= __('Category Description') ?></h6>
                                <div class="bg-light p-3">
                                    <?= nl2br(h($product->category_description)) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if ($product->prototype_notes): ?>
                            <div class="mt-3">
                                <h6><?= __('Prototype Notes') ?></h6>
                                <div class="bg-light p-3">
                                    <?= nl2br(h($product->prototype_notes)) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Description') ?></h5>
                            <p class="card-text"><?= html_entity_decode($product->description); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title"><?= __('Related Tags') ?></h4>
                            <?php if (!empty($product->tags)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('Id') ?></th>
                                            <th><?= __('Title') ?></th>
                                            <th><?= __('Slug') ?></th>
                                            <th><?= __('Description') ?></th>
                                            <th><?= __('Image') ?></th>
                                            <th><?= __('Dir') ?></th>
                                            <th><?= __('Alt Text') ?></th>
                                            <th><?= __('Keywords') ?></th>
                                            <th><?= __('Size') ?></th>
                                            <th><?= __('Mime') ?></th>
                                            <th><?= __('Name') ?></th>
                                            <th><?= __('Meta Title') ?></th>
                                            <th><?= __('Meta Description') ?></th>
                                            <th><?= __('Meta Keywords') ?></th>
                                            <th><?= __('Facebook Description') ?></th>
                                            <th><?= __('Linkedin Description') ?></th>
                                            <th><?= __('Instagram Description') ?></th>
                                            <th><?= __('Twitter Description') ?></th>
                                            <th><?= __('Parent Id') ?></th>
                                            <th><?= __('Main Menu') ?></th>
                                            <th><?= __('Lft') ?></th>
                                            <th><?= __('Rght') ?></th>
                                            <th><?= __('Modified') ?></th>
                                            <th><?= __('Created') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($product->tags as $tag) : ?>
                                        <tr>
                                            <td><?= h($tag->id) ?></td>
                                            <td><?= h($tag->title) ?></td>
                                            <td><?= h($tag->slug) ?></td>
                                            <td><?= h($tag->description) ?></td>
                                            <td><?= h($tag->image) ?></td>
                                            <td><?= h($tag->dir) ?></td>
                                            <td><?= h($tag->alt_text) ?></td>
                                            <td><?= h($tag->keywords) ?></td>
                                            <td><?= h($tag->size) ?></td>
                                            <td><?= h($tag->mime) ?></td>
                                            <td><?= h($tag->name) ?></td>
                                            <td><?= h($tag->meta_title) ?></td>
                                            <td><?= h($tag->meta_description) ?></td>
                                            <td><?= h($tag->meta_keywords) ?></td>
                                            <td><?= h($tag->facebook_description) ?></td>
                                            <td><?= h($tag->linkedin_description) ?></td>
                                            <td><?= h($tag->instagram_description) ?></td>
                                            <td><?= h($tag->twitter_description) ?></td>
                                            <td><?= h($tag->parent_id) ?></td>
                                            <td><?= h($tag->main_menu) ?></td>
                                            <td><?= h($tag->lft) ?></td>
                                            <td><?= h($tag->rght) ?></td>
                                            <td><?= h($tag->modified) ?></td>
                                            <td><?= h($tag->created) ?></td>
                                            <td class="actions">
                                                <?= $this->element('evd_dropdown', ['controller' => 'Tags', 'model' => $tag, 'display' => 'title']); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title"><?= __('Related Slugs') ?></h4>
                            <?php if (!empty($product->slugs)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('Id') ?></th>
                                            <th><?= __('Model') ?></th>
                                            <th><?= __('Foreign Key') ?></th>
                                            <th><?= __('Slug') ?></th>
                                            <th><?= __('Created') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($product->slugs as $slug) : ?>
                                        <tr>
                                            <td><?= h($slug->id) ?></td>
                                            <td><?= h($slug->model) ?></td>
                                            <td><?= h($slug->foreign_key) ?></td>
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