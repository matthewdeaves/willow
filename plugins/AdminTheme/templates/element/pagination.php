<?php
$this->Paginator->setTemplates([
    'nextActive' => '<li class="page-item"><a class="page-link" rel="next" aria-label="Next" href="{{url}}">&raquo;</a></li>',
    'nextDisabled' => '<li class="page-item disabled"><a class="page-link" aria-label="Next" href="" onclick="return false;">&raquo;</a></li>',
    'prevActive' => '<li class="page-item"><a class="page-link" rel="prev" aria-label="Previous" href="{{url}}">&laquo;</a></li>',
    'prevDisabled' => '<li class="page-item disabled"><a class="page-link" aria-label="Previous" href="" onclick="return false;">&laquo;</a></li>',
    'number' => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
    'current' => '<li class="page-item active"><a class="page-link" href="">{{text}}</a></li>',
    'counterRange' => 'Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total',
    'counterPages' => 'Page {{page}} of {{pages}}',
]);

// Get the current search query from the request
$search = $this->request->getQuery('search');

// Add the search query to the pagination URL options
$paginationOptions = [
    'url' => [
        'controller' => $this->request->getParam('controller'),
        'action' => $this->request->getParam('action'),
    ],
];

if (!empty($search)) {
    $paginationOptions['url']['?']['search'] = $search;
}
?>
<div class="d-flex justify-content-center">
    <nav aria-label="Standard pagination example">
        <ul class="pagination">
            <?= $this->Paginator->prev('&laquo;', ['escape' => false], null, ['class' => 'page-link'], $paginationOptions) ?>
            <?= $this->Paginator->numbers([], ['class' => 'page-link'], $paginationOptions) ?>
            <?= $this->Paginator->next('&raquo;', ['escape' => false], null, ['class' => 'page-link'], $paginationOptions) ?>
        </ul>
    </nav>
</div>
<div class="d-flex justify-content-center">
    <?php if ($this->Paginator->total() > 0): ?>
        <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
    <?php endif; ?>
</div>