<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Tag $tag
 */
?>
<div class="row">
    <div class="col-md-9">
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title"><?= h($tag->title) ?></h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tr>
                        <th><?= __('Title') ?></th>
                        <td><?= h($tag->title) ?></td>
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
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><?= __('Related Articles') ?></h4>
            </div>
            <div class="card-body">
                <?php if (!empty($tag->articles)) : ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th><?= __('User') ?></th>
                                <th><?= __('Title') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tag->articles as $article) : ?>
                            <tr>
                                <td><?= h($article->user->username) ?></td>
                                <td><?= h($article->title) ?></td>
                                <td class="actions">
                                    <?= $this->Html->link(
                                        __('View'),
                                        '/' . $article->slug,
                                        ['class' => 'btn btn-primary btn-sm']
                                    ) ?>
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