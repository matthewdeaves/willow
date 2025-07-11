<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Article> $articles
 */
?>
<?php if (empty($articles)): ?>
    <?= $this->element('empty_state', [
        'type' => 'search',
        'title' => __('No Articles found'),
        'message' => __('Try adjusting your search terms or filters.')
    ]) ?>
<?php else: ?>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                            <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('user_id') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('kind') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('featured') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('title') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('lede') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('slug') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('body') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('markdown') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('summary') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('image') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('alt_text') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('keywords') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('name') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('dir') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('size') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('mime') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('is_published') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('published') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('meta_title') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('meta_description') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('meta_keywords') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('facebook_description') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('linkedin_description') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('instagram_description') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('twitter_description') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('word_count') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('parent_id') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('lft') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('rght') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('main_menu') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('view_count') ?></th>
                            <th scope="col"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $article): ?>
      <tr>
                                                <td><?= h($article->id) ?></td>
                                    <td><?= $article->hasValue('user') ? $this->Html->link($article->user->username, ['controller' => 'Users', 'action' => 'view', $article->user->id], ['class' => 'btn btn-link']) : '' ?></td>
                                                                <td><?= h($article->kind) ?></td>
                                                        <td><?= h($article->featured) ?></td>
                                                        <td><?= h($article->title) ?></td>
                                                        <td><?= h($article->lede) ?></td>
                                                        <td><?= h($article->slug) ?></td>
                                                        <td><?= h($article->body) ?></td>
                                                        <td><?= h($article->markdown) ?></td>
                                                        <td><?= h($article->summary) ?></td>
                                                        <td><?= h($article->image) ?></td>
                                                        <td><?= h($article->alt_text) ?></td>
                                                        <td><?= h($article->keywords) ?></td>
                                                        <td><?= h($article->name) ?></td>
                                                        <td><?= h($article->dir) ?></td>
                                                        <td><?= $article->size === null ? '' : $this->Number->format($article->size) ?></td>
                                                        <td><?= h($article->mime) ?></td>
                                                        <td><?= h($article->is_published) ?></td>
                                                        <td><?= h($article->created) ?></td>
                                                        <td><?= h($article->modified) ?></td>
                                                        <td><?= h($article->published) ?></td>
                                                        <td><?= h($article->meta_title) ?></td>
                                                        <td><?= h($article->meta_description) ?></td>
                                                        <td><?= h($article->meta_keywords) ?></td>
                                                        <td><?= h($article->facebook_description) ?></td>
                                                        <td><?= h($article->linkedin_description) ?></td>
                                                        <td><?= h($article->instagram_description) ?></td>
                                                        <td><?= h($article->twitter_description) ?></td>
                                                        <td><?= $article->word_count === null ? '' : $this->Number->format($article->word_count) ?></td>
                                                        <td><?= h($article->parent_id) ?></td>
                                                        <td><?= $this->Number->format($article->lft) ?></td>
                                                        <td><?= $this->Number->format($article->rght) ?></td>
                                                        <td><?= h($article->main_menu) ?></td>
                                                        <td><?= $this->Number->format($article->view_count) ?></td>
                          <td>
            <?= $this->element('evd_dropdown', ['model' => $article, 'display' => 'title']); ?>
          </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?= $this->element('pagination') ?>
<?php endif; ?>

