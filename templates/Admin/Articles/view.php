<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 */
?>
<?php
echo $this->element('actions_card', [
    'modelName' => 'Article',
    'controllerName' => 'Articles',
    'entity' => $article,
    'entityDisplayName' => $article->title
]);
?>
<div class="container my-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($article->title) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($article->id) ?></td>
                        </tr>
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
                            <th><?= __('Lede') ?></th>
                            <td><?= h($article->lede) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Slug') ?></th>
                            <td><?= h($article->slug) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Image') ?></th>
                            <td><?= h($article->image) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Alt Text') ?></th>
                            <td><?= h($article->alt_text) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Keywords') ?></th>
                            <td><?= h($article->keywords) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($article->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Dir') ?></th>
                            <td><?= h($article->dir) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Mime') ?></th>
                            <td><?= h($article->mime) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Meta Title') ?></th>
                            <td><?= h($article->meta_title) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Parent Id') ?></th>
                            <td><?= h($article->parent_id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Size') ?></th>
                            <td><?= $article->size === null ? '' : $this->Number->format($article->size) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Word Count') ?></th>
                            <td><?= $article->word_count === null ? '' : $this->Number->format($article->word_count) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Lft') ?></th>
                            <td><?= $this->Number->format($article->lft) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Rght') ?></th>
                            <td><?= $this->Number->format($article->rght) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('View Count') ?></th>
                            <td><?= $this->Number->format($article->view_count) ?></td>
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
                            <th><?= __('Featured') ?></th>
                            <td><?= $article->featured ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is Published') ?></th>
                            <td><?= $article->is_published ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Main Menu') ?></th>
                            <td><?= $article->main_menu ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                    </table>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Body') ?></h5>
                            <p class="card-text"><?= html_entity_decode($article->body); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Markdown') ?></h5>
                            <p class="card-text"><?= html_entity_decode($article->markdown); ?></p>
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
                            <h4 class="card-title"><?= __('Related Images') ?></h4>
                            <?php if (!empty($article->images)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('Id') ?></th>
                                            <th><?= __('Name') ?></th>
                                            <th><?= __('Alt Text') ?></th>
                                            <th><?= __('Keywords') ?></th>
                                            <th><?= __('Image') ?></th>
                                            <th><?= __('Dir') ?></th>
                                            <th><?= __('Size') ?></th>
                                            <th><?= __('Mime') ?></th>
                                            <th><?= __('Created') ?></th>
                                            <th><?= __('Modified') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($article->images as $image) : ?>
                                        <tr>
                                            <td><?= h($image->id) ?></td>
                                            <td><?= h($image->name) ?></td>
                                            <td><?= h($image->alt_text) ?></td>
                                            <td><?= h($image->keywords) ?></td>
                                            <td><?= h($image->image) ?></td>
                                            <td><?= h($image->dir) ?></td>
                                            <td><?= h($image->size) ?></td>
                                            <td><?= h($image->mime) ?></td>
                                            <td><?= h($image->created) ?></td>
                                            <td><?= h($image->modified) ?></td>
                                            <td class="actions">
                                                <?= $this->element('evd_dropdown', ['controller' => 'Images', 'model' => $image, 'display' => 'name']); ?>
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
                            <h4 class="card-title"><?= __('Related Tags') ?></h4>
                            <?php if (!empty($article->tags)) : ?>
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
                                        <?php foreach ($article->tags as $tag) : ?>
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
                                                <?= $this->element('evd_dropdown', ['controller' => 'Comments', 'model' => $comment, 'display' => 'model']); ?>
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
                                            <th><?= __('Model') ?></th>
                                            <th><?= __('Foreign Key') ?></th>
                                            <th><?= __('Slug') ?></th>
                                            <th><?= __('Created') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($article->slugs as $slug) : ?>
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
                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title"><?= __('Related Articles Translations') ?></h4>
                            <?php if (!empty($article->_i18n)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('Id') ?></th>
                                            <th><?= __('Locale') ?></th>
                                            <th><?= __('Title') ?></th>
                                            <th><?= __('Lede') ?></th>
                                            <th><?= __('Body') ?></th>
                                            <th><?= __('Summary') ?></th>
                                            <th><?= __('Meta Title') ?></th>
                                            <th><?= __('Meta Description') ?></th>
                                            <th><?= __('Meta Keywords') ?></th>
                                            <th><?= __('Facebook Description') ?></th>
                                            <th><?= __('Linkedin Description') ?></th>
                                            <th><?= __('Instagram Description') ?></th>
                                            <th><?= __('Twitter Description') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($article->_i18n as $articlesTranslation) : ?>
                                        <tr>
                                            <td><?= h($articlesTranslation->id) ?></td>
                                            <td><?= h($articlesTranslation->locale) ?></td>
                                            <td><?= h($articlesTranslation->title) ?></td>
                                            <td><?= h($articlesTranslation->lede) ?></td>
                                            <td><?= h($articlesTranslation->body) ?></td>
                                            <td><?= h($articlesTranslation->summary) ?></td>
                                            <td><?= h($articlesTranslation->meta_title) ?></td>
                                            <td><?= h($articlesTranslation->meta_description) ?></td>
                                            <td><?= h($articlesTranslation->meta_keywords) ?></td>
                                            <td><?= h($articlesTranslation->facebook_description) ?></td>
                                            <td><?= h($articlesTranslation->linkedin_description) ?></td>
                                            <td><?= h($articlesTranslation->instagram_description) ?></td>
                                            <td><?= h($articlesTranslation->twitter_description) ?></td>
                                            <td class="actions">
                                                <?= $this->element('evd_dropdown', ['controller' => 'ArticlesTranslations', 'model' => $articlesTranslation, 'display' => 'title']); ?>
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
                            <h4 class="card-title"><?= __('Related Page Views') ?></h4>
                            <?php if (!empty($article->page_views)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('Id') ?></th>
                                            <th><?= __('Article Id') ?></th>
                                            <th><?= __('Ip Address') ?></th>
                                            <th><?= __('User Agent') ?></th>
                                            <th><?= __('Referer') ?></th>
                                            <th><?= __('Created') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($article->page_views as $pageView) : ?>
                                        <tr>
                                            <td><?= h($pageView->id) ?></td>
                                            <td><?= h($pageView->article_id) ?></td>
                                            <td><?= h($pageView->ip_address) ?></td>
                                            <td><?= h($pageView->user_agent) ?></td>
                                            <td><?= h($pageView->referer) ?></td>
                                            <td><?= h($pageView->created) ?></td>
                                            <td class="actions">
                                                <?= $this->element('evd_dropdown', ['controller' => 'PageViews', 'model' => $pageView, 'display' => 'ip_address']); ?>
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