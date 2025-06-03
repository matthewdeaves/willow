<?php if (!empty($articles)) : ?>
<div class="sidebar-section mb-4">
    <h4 class="fst-italic text-primary border-bottom pb-2 mb-3"><?= $title ?></h4>
    <div class="list-group list-group-flush">
    <?php foreach ($articles as $article) : ?>
        <a class="list-group-item list-group-item-action border-0 px-0 py-3" 
           href="<?= $this->Url->build(['_name' => $article->kind . '-by-slug', 'slug' => $article->slug]) ?>"
           aria-label="<?= h($article->title) ?>">
            <div class="d-flex gap-3 align-items-start">
                <?php if (!empty($article->image)) : ?>
                <div class="flex-shrink-0">
                    <?= $this->element('image/icon', [
                        'model' => $article, 
                        'icon' => $article->tinyImageUrl, 
                        'preview' => false,
                        'class' => 'rounded'
                    ]); ?>
                </div>
                <?php endif; ?>
                <div class="flex-grow-1">
                    <h6 class="mb-1 text-body-emphasis"><?= htmlspecialchars_decode($article->title) ?></h6>
                    <?php if (!empty($article->lede)): ?>
                    <p class="mb-1 text-body-secondary small"><?= $this->Text->truncate(strip_tags($article->lede), 80) ?></p>
                    <?php endif; ?>
                    <small class="text-body-tertiary"><?= $article->published->format('M j, Y') ?></small>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>