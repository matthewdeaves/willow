<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Slug $slug
 * @var array|null $relatedRecord
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
                    <h2 class="card-title">
                        <?php
                        if ($relatedRecord) {
                            $routeName = match ($slug->model) {
                                'Articles' => $relatedRecord->kind === 'page' ? 'page-by-slug' : 'article-by-slug',
                                'Tags' => 'tag-by-slug',
                                default => null,
                            };

                            if ($routeName) {
                                echo $this->Html->link(
                                    h($slug->slug),
                                    [
                                        '_name' => $routeName,
                                        'slug' => $slug->slug,
                                    ],
                                    [
                                        'class' => 'text-decoration-none',
                                        'target' => '_blank'
                                    ]
                                );
                            } else {
                                echo h($slug->slug);
                            }
                        } else {
                            echo h($slug->slug);
                        }
                        ?>
                    </h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Model') ?></th>
                            <td>
                                <?php
                                if ($relatedRecord && $slug->model === 'Articles') {
                                    echo h(ucfirst($relatedRecord->kind));
                                } else {
                                    echo h(str_replace('s', '', $slug->model));
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Related Content') ?></th>
                            <td>
                                <?php if ($relatedRecord): ?>
                                    <?= $this->Html->link(
                                        h($relatedRecord->title),
                                        [
                                            'controller' => $slug->model,
                                            'action' => 'view',
                                            $relatedRecord->id
                                        ],
                                        [
                                            'class' => 'text-decoration-none',
                                            'escape' => false
                                        ]
                                    ) ?>
                                <?php else: ?>
                                    <?= h($slug->foreign_key) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($slug->created) ?></td>
                        </tr>
                        <?php if ($relatedRecord): ?>
                        <tr>
                            <th><?= __('Preview') ?></th>
                            <td>
                                <?php
                                $routeName = match ($slug->model) {
                                    'Articles' => $relatedRecord->kind === 'page' ? 'page-by-slug' : 'article-by-slug',
                                    'Tags' => 'tag-by-slug',
                                    default => null,
                                };

                                if ($routeName) {
                                    echo $this->Html->link(
                                        __('View on site'),
                                        [
                                            '_name' => $routeName,
                                            'slug' => $slug->slug,
                                        ],
                                        [
                                            'class' => 'btn btn-sm btn-outline-primary',
                                            'target' => '_blank'
                                        ]
                                    );
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>