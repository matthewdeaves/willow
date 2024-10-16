<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Tag> $tags
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4 text-primary"><?= __('Tags') ?></h2>
            <div class="card shadow">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($tags as $tag): ?>
                            <?= $this->Html->link(
                                h($tag->title),
                                ['action' => 'view-by-slug', $tag->slug],
                                ['class' => 'btn btn-outline-primary btn-sm']
                            ) ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?= $this->element('pagination', ['recordCount' => count($tags)]) ?>
</div>