<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Slug $slug
 */
?>
<div class="container my-4">
    <div class="row">
        <?php
        echo $this->element('actions_card', [
            'modelName' => 'Slug',
            'controllerName' => 'Slugs',
            'entity' => $slug,
            'entityDisplayName' => $slug->slug
        ]);
        ?>
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($slug->slug) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Article') ?></th>
                            <td><?= $slug->hasValue('article') ? $this->Html->link($slug->article->title, ['controller' => 'Articles', 'action' => 'view', $slug->article->id], ['class' => 'btn btn-link']) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Slug') ?></th>
                            <td><?= h($slug->slug) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($slug->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($slug->modified) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>