<div class="row">
    <div class="col-12">
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm justify-content-center mt-2">
                <?php
                echo $this->Paginator->numbers([
                    'before' => '',
                    'after' => '',
                    'modulus' => 2,
                    'templates' => [
                        'number' => '<li class="page-item mx-1"><a class="page-link" href="{{url}}">{{text}}</a></li>',
                        'current' => '<li class="page-item active mx-1"><a class="page-link" href="{{url}}">{{text}}</a></li>',
                    ]
                ]);
                ?>
            </ul>
            <ul class="pagination pagination-sm justify-content-center">
                <?php if ($this->Paginator->hasPrev()): ?>
                    <li class="page-item mx-1"><?= $this->Paginator->first('« First') ?></li>
                    <li class="page-item mx-1"><?= $this->Paginator->prev('‹ Previous') ?></li>
                <?php endif; ?>
                <?php if ($this->Paginator->hasNext()): ?>
                    <li class="page-item mx-1"><?= $this->Paginator->next('Next ›') ?></li>
                    <li class="page-item mx-1"><?= $this->Paginator->last('Last »') ?></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php if (!isset($recordCount) || $recordCount > 0): ?>
    <div class="col-12 text-center">
        <p class="pagination-counter mb-0">
            <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
        </p>
    </div>
    <?php endif; ?>
</div>