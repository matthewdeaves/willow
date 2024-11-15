<?php if (!empty($articles)) : ?>
<div>
    <h4 class="fst-italic"><?= $title ?></h4>
    <ul class="list-unstyled">
    <?php foreach ($articles as $article) : ?>
        <li>
        <a class="d-flex flex-column flex-lg-row gap-3 align-items-start align-items-lg-center py-3 link-body-emphasis text-decoration-none border-top" href="<?= $this->Url->build(['_name' => $article->kind . '-by-slug', 'slug' => $article->slug]) ?>">
            <?php if (!empty($article->image)) : ?>
            <?= $this->Html->image($article->tinyImageUrl, ['pathPrefix' => '', 'alt' => $article->alt_text]) ?>
            <?php endif; ?>
            <div class="col-lg-8">
                <h6 class="mb-0"><?= $article->title ?></h6>
                <div>
                    <small class="text-body-secondary"><?= $article->lead ?></small>
                </div>
                <div>
                    <small class="text-body-tertiary"><?= $article->published->format('F j, Y') ?></small>
                </div>
            </div>
        </a>
        </li>
    <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>