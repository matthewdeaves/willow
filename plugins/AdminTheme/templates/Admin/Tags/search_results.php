<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Tag> $tags
 */
?>
<?php use App\Utility\SettingsManager; ?>
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
          <?= $this->element('evd_dropdown', ['model' => $tag, 'display' => 'title']); ?>
        </td>
    </tr>
    <?php endforeach; ?>