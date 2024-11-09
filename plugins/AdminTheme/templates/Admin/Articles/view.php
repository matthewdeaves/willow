<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 */
?>
<?php use App\Utility\SettingsManager; ?>
<div class="container my-4">
    <div class="row">
    <?php
        echo $this->element('actions_card', [
            'modelName' => 'Article',
            'controllerName' => 'Articles',
            'entity' => $article,
            'entityDisplayName' => $article->title
        ]);
        ?>
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($article->title) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('User') ?></th>
                            <td><?= $article->hasValue('user') ? $this->Html->link($article->user->username, ['controller' => 'Users', 'action' => 'view', $article->user->id], ['class' => 'btn btn-link']) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Kind') ?></th>
                            <td><?= h($article->kind) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Title') ?></th>
                            <td><?= h($article->title) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Slug') ?></th>
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
                        </tr>
                        <tr>
                            <th><?= __('Image') ?></th>
                            <td>
                                <?php if (!empty($article->image)) : ?>
                                <div class="position-relative">
                                    <?= $this->Html->image(SettingsManager::read('ImageSizes.small', '200') . '/' . $article->image, 
                                        ['pathPrefix' => 'files/Articles/image/', 
                                        'alt' => $article->alt_text, 
                                        'class' => 'img-thumbnail', 
                                        'width' => '50',
                                        'data-bs-toggle' => 'popover',
                                        'data-bs-trigger' => 'hover',
                                        'data-bs-html' => 'true',
                                        'data-bs-content' => $this->Html->image(SettingsManager::read('ImageSizes.large', '400') . '/' . $article->image, 
                                            ['pathPrefix' => 'files/Articles/image/', 
                                            'alt' => $article->alt_text, 
                                            'class' => 'img-fluid', 
                                            'style' => 'max-width: 300px; max-height: 300px;'])
                                        ]) 
                                    ?>
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($article->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($article->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Published') ?></th>
                            <td><?= h($article->published) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is Published') ?></th>
                            <td><?= $article->is_published ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                    </table>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Body') ?></h5>
                            <p class="card-text"><?= html_entity_decode($article->body); ?>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Summary') ?></h5>
                            <p class="card-text"><?= html_entity_decode($article->summary); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Meta Description') ?></h5>
                            <p class="card-text"><?= html_entity_decode($article->meta_description); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Meta Keywords') ?></h5>
                            <p class="card-text"><?= html_entity_decode($article->meta_keywords); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Facebook Description') ?></h5>
                            <p class="card-text"><?= html_entity_decode($article->facebook_description); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Linkedin Description') ?></h5>
                            <p class="card-text"><?= html_entity_decode($article->linkedin_description); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Instagram Description') ?></h5>
                            <p class="card-text"><?= html_entity_decode($article->instagram_description); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Twitter Description') ?></h5>
                            <p class="card-text"><?= html_entity_decode($article->twitter_description); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title"><?= __('Related Tags') ?></h4>
                            <?php if (!empty($article->tags)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('Title') ?></th>
                                            <th><?= __('Slug') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($article->tags as $tag) : ?>
                                        <tr>
                                            <td><?= h($tag->title) ?></td>
                                            <td><?= h($tag->slug) ?></td>
                                            <td class="actions">
                                                <?= $this->Html->link(__('View'), ['controller' => 'Tags', 'action' => 'view', $tag->id], ['class' => 'btn btn-info btn-sm']) ?>
                                                <?= $this->Html->link(__('Edit'), ['controller' => 'Tags', 'action' => 'edit', $tag->id], ['class' => 'btn btn-warning btn-sm']) ?>
                                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'Tags', 'action' => 'delete', $tag->id], ['confirm' => __('Are you sure you want to delete {0}?', $tag->title), 'class' => 'btn btn-danger btn-sm']) ?>
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
                    <?php if (!empty($article->images)) : ?>
                        <div class="mb-3">
                        <?= $this->element('image_carousel', [
                            'images' => $article->images,
                            'carouselId' => $carouselId ?? 'imageCarouselID',
                            'hideRemove' => true,
                        ]) ?>
                        </div>
                    <?php endif; ?>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title"><?= __('Related Comments') ?></h4>
                            <?php if (!empty($article->comments)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('Id') ?></th>
                                            <th><?= __('Foreign Key') ?></th>
                                            <th><?= __('Model') ?></th>
                                            <th><?= __('User Id') ?></th>
                                            <th><?= __('Content') ?></th>
                                            <th><?= __('Display') ?></th>
                                            <th><?= __('Is Inappropriate') ?></th>
                                            <th><?= __('Is Analyzed') ?></th>
                                            <th><?= __('Inappropriate Reason') ?></th>
                                            <th><?= __('Created') ?></th>
                                            <th><?= __('Modified') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($article->comments as $comment) : ?>
                                        <tr>
                                            <td><?= h($comment->id) ?></td>
                                            <td><?= h($comment->foreign_key) ?></td>
                                            <td><?= h($comment->model) ?></td>
                                            <td><?= h($comment->user_id) ?></td>
                                            <td><?= h($comment->content) ?></td>
                                            <td><?= h($comment->display) ?></td>
                                            <td><?= h($comment->is_inappropriate) ?></td>
                                            <td><?= h($comment->is_analyzed) ?></td>
                                            <td><?= h($comment->inappropriate_reason) ?></td>
                                            <td><?= h($comment->created) ?></td>
                                            <td><?= h($comment->modified) ?></td>
                                            <td class="actions">
                                                <?= $this->Html->link(__('View'), ['controller' => 'Comments', 'action' => 'view', $comment->id], ['class' => 'btn btn-info btn-sm']) ?>
                                                <?= $this->Html->link(__('Edit'), ['controller' => 'Comments', 'action' => 'edit', $comment->id], ['class' => 'btn btn-warning btn-sm']) ?>
                                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'Comments', 'action' => 'delete', $comment->id], ['confirm' => __('Are you sure you want to delete # {0}?', $comment->id), 'class' => 'btn btn-danger btn-sm']) ?>
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
                            <?php if (!empty($article->slugs)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('Id') ?></th>
                                            <th><?= __('Article Id') ?></th>
                                            <th><?= __('Slug') ?></th>
                                            <th><?= __('Created') ?></th>
                                            <th><?= __('Modified') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($article->slugs as $slug) : ?>
                                        <tr>
                                            <td><?= h($slug->id) ?></td>
                                            <td><?= h($slug->article_id) ?></td>
                                            <td><?= h($slug->slug) ?></td>
                                            <td><?= h($slug->created) ?></td>
                                            <td><?= h($slug->modified) ?></td>
                                            <td class="actions">
                                                <?= $this->Html->link(__('View'), ['controller' => 'Slugs', 'action' => 'view', $slug->id], ['class' => 'btn btn-info btn-sm']) ?>
                                                <?= $this->Html->link(__('Edit'), ['controller' => 'Slugs', 'action' => 'edit', $slug->id], ['class' => 'btn btn-warning btn-sm']) ?>
                                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'Slugs', 'action' => 'delete', $slug->id], ['confirm' => __('Are you sure you want to delete # {0}?', $slug->id), 'class' => 'btn btn-danger btn-sm']) ?>
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