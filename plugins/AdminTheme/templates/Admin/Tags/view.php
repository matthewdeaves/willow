<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Tag $tag
 */
?>
<?php use App\Utility\SettingsManager; ?>
<div class="container-fluid mt-4">
    <div class="row">
        <?php
        echo $this->element('actions_card', [
            'modelName' => 'Tag',
            'controllerName' => 'Tags',
            'entity' => $tag,
            'entityDisplayName' => $tag->title
        ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= h($tag->title) ?></h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th><?= __('Title') ?></th>
                            <td><?= h($tag->title) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Slug') ?></th>
                            <td>
                                <?= $this->Html->link(htmlspecialchars_decode($tag->slug), ['_name' => 'tag-by-slug', 'slug' => $tag->slug]) ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Image') ?></th>
                            <td>
                                <?php if (!empty($tag->image)) : ?>
                                <div class="position-relative">
                                    <?= $this->Html->image(SettingsManager::read('ImageSizes.small', '200') . '/' . $tag->image, 
                                        ['pathPrefix' => 'files/Tags/image/', 
                                        'alt' => $tag->alt_text, 
                                        'class' => 'img-thumbnail', 
                                        'width' => '50',
                                        'data-bs-toggle' => 'popover',
                                        'data-bs-trigger' => 'hover',
                                        'data-bs-html' => 'true',
                                        'data-bs-content' => $this->Html->image(SettingsManager::read('ImageSizes.large', '400') . '/' . $tag->image, 
                                            ['pathPrefix' => 'files/Tags/image/', 
                                            'alt' => $tag->alt_text, 
                                            'class' => 'img-fluid', 
                                            'style' => 'max-width: 300px; max-height: 300px;'])
                                        ]) 
                                    ?>
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Description') ?></th>
                            <td><?= h($tag->description) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($tag->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($tag->modified) ?></td>
                        </tr>
                    </table>
                    <div class="mt-4">
                        <?= $this->element('seo_fields', ['model' => $tag, 'hideWordCount' => true]) ?>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    <h4 class="mb-0"><?= __('Related Articles') ?></h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($tag->articles)) : ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?= __('User') ?></th>
                                    <th><?= __('Title') ?></th>
                                    <th><?= __('Slug') ?></th>
                                    <th><?= __('Created') ?></th>
                                    <th><?= __('Modified') ?></th>
                                    <th class="actions"><?= __('Actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tag->articles as $article) : ?>
                                <tr>
                                    <td><?= h($article->user->username) ?></td>
                                    <td><?= h($article->title) ?></td>
                                    <td>
                                        <?php $ruleName = ($article->kind == 'article') ? 'article-by-slug' : 'page-by-slug';?>

                                        <?php if ($article->is_published == true): ?>
                                            <?= $this->Html->link(
                                                $article->slug,
                                                [
                                                    'controller' => 'Articles',
                                                    'action' => 'view-by-slug',
                                                    'slug' => $article->slug,
                                                    '_name' => $ruleName,
                                                ],
                                                ['escape' => false]
                                            );
                                            ?>
                                        <?php else: ?>
                                            <?= $this->Html->link(
                                                $article->slug,
                                                [
                                                    'prefix' => 'Admin',
                                                    'controller' => 'Articles',
                                                    'action' => 'view',
                                                    $article->id
                                                ],
                                                ['escape' => false]
                                            ) ?>
                                        <?php endif; ?>


                                    </td>
                                    <td><?= h($article->created) ?></td>
                                    <td><?= h($article->modified) ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__('View'), ['controller' => 'Articles', 'action' => 'view', $article->id], ['class' => 'btn btn-sm btn-info']) ?>
                                        <?= $this->Html->link(__('Edit'), ['controller' => 'Articles', 'action' => 'edit', $article->id], ['class' => 'btn btn-sm btn-primary']) ?>
                                        <?= $this->Form->postLink(__('Delete'), ['controller' => 'Articles', 'action' => 'delete', $article->id], ['confirm' => __('Are you sure you want to delete {0}?', $article->title), 'class' => 'btn btn-sm btn-danger']) ?>
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

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize popovers on page load
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    $('#tags-select').selectpicker({
        liveSearch: true,
        actionsBox: true,
        selectedTextFormat: 'count > 3'
    });
});
<?php $this->Html->scriptEnd(); ?>