<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Tag> $tags
 */
?>
<?php use App\Utility\SettingsManager; ?>
<table class="table table-striped">
  <thead>
    <tr>
          <th><?= __('Picture') ?></th>
          <th scope="col"><?= $this->Paginator->sort('title') ?></th>
          <th scope="col"><?= $this->Paginator->sort('slug') ?></th>
          <th scope="col"><?= $this->Paginator->sort('parent_id', __('Parent')) ?></th>
          <th scope="col"><?= __('Actions') ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($tags as $tag): ?>
    <tr>
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
            <td><?= html_entity_decode($tag->title) ?></td>
            <td><?= h($tag->slug) ?></td>
            <td>
              <?php if (!empty($tag->parent_tag)) : ?>
                  <?= $this->Html->link(
                      h($tag->parent_tag->title), 
                      ['controller' => 'Tags', 'action' => 'view', $tag->parent_tag->id]
                  ); ?>
              <?php endif; ?>
            </td>
        <td>
          <?= $this->element('evd_dropdown', ['model' => $tag, 'display' => 'title']); ?>
        </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?= $this->element('pagination', ['recordCount' => count($tags), 'search' => $search ?? '']) ?>