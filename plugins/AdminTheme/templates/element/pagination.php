<div class="row">
    <div class="col-12">
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm justify-content-center">
                <li class="page-item mx-1"><?= $this->Paginator->first('« First') ?></li>
                <li class="page-item mx-2"><?= $this->Paginator->prev('‹ Previous') ?></li>
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
                <li class="page-item mx-2"><?= $this->Paginator->next('Next ›') ?></li>
                <li class="page-item mx-1"><?= $this->Paginator->last('Last »') ?></li>
            </ul>
        </nav>
    </div>
    <div class="col-12 text-center">
        <p class="pagination-counter mb-0">
            <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
        </p>
    </div>
</div>