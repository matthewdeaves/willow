<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Article> $articles
 */
?>
<?php use App\Utility\SettingsManager; ?>
<?php $activeFilter = $this->request->getQuery('status'); ?>
<?php foreach ($articles as $article): ?>
    <tr>
      <td>
        <?php if (!empty($article->image)) : ?>
        <div class="position-relative">
          <?= $this->Html->image(SettingsManager::read('ImageSizes.small', '200') . '/' . $article->image, 
            [
              'pathPrefix' => 'files/Articles/image/', 
              'alt' => $article->alt_text, 
              'class' => 'img-thumbnail', 
              'width' => '50',
              'data-bs-toggle' => 'popover',
              'data-bs-trigger' => 'hover',
              'data-bs-html' => 'true',
              'data-bs-content' => $this->Html->image(
                SettingsManager::read('ImageSizes.large', '400') . '/' . $article->image, 
                [
                  'pathPrefix' => 'files/Articles/image/', 
                  'alt' => $article->alt_text, 
                  'class' => 'img-fluid', 
                  'style' => 'max-width: 300px; max-height: 300px;'
                ])
            ])?>
        </div>
        <?php endif; ?>
      </td>
      <td>
        <?php if (isset($article->_matchingData['Users']) && $article->_matchingData['Users']->username): ?>
            <?= $this->Html->link(
                h($article->_matchingData['Users']->username),
                ['controller' => 'Users', 'action' => 'view', $article->_matchingData['Users']->id]
            ) ?>
        <?php else: ?>
            <?= h(__('Unknown Author')) ?>
        <?php endif; ?>
      </td>
      <td>
        <?php if ($article->is_published == true): ?>
            <?= $this->Html->link(
                html_entity_decode($article->title),
                [
                    'controller' => 'Articles',
                    'action' => 'view-by-slug',
                    'slug' => $article->slug,
                    '_name' => 'article-by-slug'
                ],
                ['escape' => false]
            );
            ?>
        <?php else: ?>
            <?= $this->Html->link(
                html_entity_decode($article->title),
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
      <?php if (null === $activeFilter) :?>
      <td><?= $article->is_published ? '<span class="badge bg-success">' . __('Published') . '</span>' : '<span class="badge bg-warning">' . __('Un-Published') . '</span>'; ?></td>
      <?php elseif ('1' === $activeFilter) :?>
      <td><?= h($article->published) ?></td>
      <?php elseif ('0' === $activeFilter) :?>
      <td><?= h($article->modified) ?></td>
      <?php endif; ?>
      <td>
        <div class="btn-group w-100 align-items-center justify-content-between flex-wrap">
          <div class="dropdown">
          <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <?= __('Actions') ?>
          </button>
          <ul class="dropdown-menu">
              <li>
                  <?= $this->Html->link(__('Edit'), ['action' => 'edit', $article->id], ['class' => 'dropdown-item']) ?>
              </li>
              <li>
                  <?= $this->Html->link(__('View'), ['action' => 'view', $article->id], ['class' => 'dropdown-item']) ?>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                  <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $article->id], ['confirm' => __('Are you sure you want to delete {0}?', $article->title), 'class' => 'dropdown-item text-danger']) ?>
              </li>
          </ul>
          </div>
        </div>
      </td>
    </tr>
<?php endforeach; ?>