<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 */
?>
<div class="container my-4">
    <div class="row">
        <aside class="col-lg-3">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title"><?= __('Actions') ?></h4>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><?= $this->Html->link(__('Edit Article'), ['action' => 'edit', $article->id], ['class' => 'btn btn-primary btn-sm']) ?></li>
                        <li class="list-group-item"><?= $this->Form->postLink(__('Delete Article'), ['action' => 'delete', $article->id], ['confirm' => __('Are you sure you want to delete # {0}?', $article->id), 'class' => 'btn btn-danger btn-sm']) ?></li>
                        <li class="list-group-item"><?= $this->Html->link(__('List Articles'), ['action' => 'index'], ['class' => 'btn btn-secondary btn-sm']) ?></li>
                        <li class="list-group-item"><?= $this->Html->link(__('New Article'), ['action' => 'add'], ['class' => 'btn btn-success btn-sm']) ?></li>
                    </ul>
                </div>
            </div>
        </aside>
        <div class="col-lg-9">
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
                            <p class="card-text"><?= $this->Text->autoParagraph(h($article->body)); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Summary') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($article->summary)); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Meta Description') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($article->meta_description)); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Meta Keywords') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($article->meta_keywords)); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Facebook Description') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($article->facebook_description)); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Linkedin Description') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($article->linkedin_description)); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Instagram Description') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($article->instagram_description)); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Twitter Description') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($article->twitter_description)); ?></p>
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
                                            <th><?= __('File') ?></th>
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
                                            <td><?= h($image->file) ?></td>
                                            <td><?= h($image->dir) ?></td>
                                            <td><?= h($image->size) ?></td>
                                            <td><?= h($image->mime) ?></td>
                                            <td><?= h($image->created) ?></td>
                                            <td><?= h($image->modified) ?></td>
                                            <td class="actions">
                                                <?= $this->Html->link(__('View'), ['controller' => 'Images', 'action' => 'view', $image->id], ['class' => 'btn btn-info btn-sm']) ?>
                                                <?= $this->Html->link(__('Edit'), ['controller' => 'Images', 'action' => 'edit', $image->id], ['class' => 'btn btn-warning btn-sm']) ?>
                                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'Images', 'action' => 'delete', $image->id], ['confirm' => __('Are you sure you want to delete # {0}?', $image->id), 'class' => 'btn btn-danger btn-sm']) ?>
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
                                            <th><?= __('Created') ?></th>
                                            <th><?= __('Modified') ?></th>
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
                                            <td><?= h($tag->created) ?></td>
                                            <td><?= h($tag->modified) ?></td>
                                            <td><?= h($tag->meta_title) ?></td>
                                            <td><?= h($tag->meta_description) ?></td>
                                            <td><?= h($tag->meta_keywords) ?></td>
                                            <td><?= h($tag->facebook_description) ?></td>
                                            <td><?= h($tag->linkedin_description) ?></td>
                                            <td><?= h($tag->instagram_description) ?></td>
                                            <td><?= h($tag->twitter_description) ?></td>
                                            <td class="actions">
                                                <?= $this->Html->link(__('View'), ['controller' => 'Tags', 'action' => 'view', $tag->id], ['class' => 'btn btn-info btn-sm']) ?>
                                                <?= $this->Html->link(__('Edit'), ['controller' => 'Tags', 'action' => 'edit', $tag->id], ['class' => 'btn btn-warning btn-sm']) ?>
                                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'Tags', 'action' => 'delete', $tag->id], ['confirm' => __('Are you sure you want to delete # {0}?', $tag->id), 'class' => 'btn btn-danger btn-sm']) ?>
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
                            <h4 class="card-title"><?= __('Related Articles Translations') ?></h4>
                            <?php if (!empty($article->_i18n)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('Id') ?></th>
                                            <th><?= __('Locale') ?></th>
                                            <th><?= __('Title') ?></th>
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
                                                <?= $this->Html->link(__('View'), ['controller' => 'ArticlesTranslations', 'action' => 'view', $articlesTranslation->id], ['class' => 'btn btn-info btn-sm']) ?>
                                                <?= $this->Html->link(__('Edit'), ['controller' => 'ArticlesTranslations', 'action' => 'edit', $articlesTranslation->id], ['class' => 'btn btn-warning btn-sm']) ?>
                                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'ArticlesTranslations', 'action' => 'delete', $articlesTranslation->id], ['confirm' => __('Are you sure you want to delete # {0}?', $articlesTranslation->id), 'class' => 'btn btn-danger btn-sm']) ?>
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
                                                <?= $this->Html->link(__('View'), ['controller' => 'PageViews', 'action' => 'view', $pageView->id], ['class' => 'btn btn-info btn-sm']) ?>
                                                <?= $this->Html->link(__('Edit'), ['controller' => 'PageViews', 'action' => 'edit', $pageView->id], ['class' => 'btn btn-warning btn-sm']) ?>
                                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'PageViews', 'action' => 'delete', $pageView->id], ['confirm' => __('Are you sure you want to delete # {0}?', $pageView->id), 'class' => 'btn btn-danger btn-sm']) ?>
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