<!-- File: templates/Admin/PageViews/page_view_stats.php -->

<div class="page-view-stats content">
    <h2><?= __('Page View Statistics for slug: {0}', h($article->slug)); ?></h2>

    <?php if (!empty($viewsOverTime) && $viewsOverTime->count() > 0): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?= __('Date') ?></th>
                    <th><?= __('Views') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($viewsOverTime as $view): ?>
                <tr>
                    <td><?= h($view->date) ?></td>
                    <td>
                        <?= $this->Html->link(
                            h($view->count),
                            ['action' => 'viewRecords', $article->id, '?' => ['date' => $view->date]],
                            ['title' => __('View detailed records for this date')]
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p><?= __('No page view data available for this article.') ?></p>
    <?php endif; ?>
</div>