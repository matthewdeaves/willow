<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $users
 */
?>
<?php use App\Utility\SettingsManager; ?>
<?php $activeFilter = $this->request->getQuery('status'); ?>
<?php foreach ($users as $user): ?>
    <tr>
        <td>
          <?php if (!empty($user->picture)) : ?>
          <div class="position-relative">
              <?= $this->Html->image(SettingsManager::read('ImageSizes.small', '200') . '/' . $user->picture, 
                  ['pathPrefix' => 'files/Users/picture/', 
                  'alt' => $user->alt_text, 
                  'class' => 'img-thumbnail', 
                  'width' => '50',
                  'data-bs-toggle' => 'popover',
                  'data-bs-trigger' => 'hover',
                  'data-bs-html' => 'true',
                  'data-bs-content' => $this->Html->image(SettingsManager::read('ImageSizes.large', '400') . '/' . $user->picture, 
                      ['pathPrefix' => 'files/Users/picture/', 
                      'alt' => $user->alt_text, 
                      'class' => 'img-fluid', 
                      'style' => 'max-width: 300px; max-height: 300px;'])
                  ]) 
              ?>
          </div>
          <?php endif; ?>
        </td>
        <td><?= $this->Html->link(h($user->email), 'mailto:' . h($user->email)) ?></td>
        <td>
          <?= $user->is_admin ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-warning">' . __('No') . '</span>'; ?>
        </td>
        <td>
          <?= $user->active ? '<span class="badge bg-warning">' . __('No') . '</span>' : '<span class="badge bg-success">' . __('Yes') . '</span>'; ?>
        </td>
        <td>
            <div class="btn-group w-100 align-items-center justify-content-between flex-wrap">
                <div class="dropdown">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <?= __('Actions') ?>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <?= $this->Html->link(__('View'), ['action' => 'view', $user->id], ['class' => 'dropdown-item']) ?>
                    </li>
                    <li>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $user->id], ['class' => 'dropdown-item']) ?>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $user->id], ['confirm' => __('Are you sure you want to delete  {0}?', $user->email), 'class' => 'dropdown-item text-danger']) ?>
                    </li>
                </ul>
                </div>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>