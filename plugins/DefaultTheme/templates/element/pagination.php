<nav class="blog-pagination" aria-label="Pagination">
    <?php if ($this->Paginator->hasPrev()): ?>
        <a class="btn btn-outline-primary rounded-pill" href="<?= $this->Paginator->prevUrl() ?>">Older</a>
    <?php else: ?>
        <a class="btn btn-outline-primary rounded-pill disabled" aria-disabled="true">Older</a>
    <?php endif; ?>

    <?php if ($this->Paginator->hasNext()): ?>
        <a class="btn btn-outline-secondary rounded-pill" href="<?= $this->Paginator->nextUrl() ?>">Newer</a>
    <?php else: ?>
        <a class="btn btn-outline-secondary rounded-pill disabled" aria-disabled="true">Newer</a>
    <?php endif; ?>
</nav>