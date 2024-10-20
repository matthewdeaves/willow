<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 * @var \Cake\Collection\CollectionInterface $viewsOverTime
 */
?>
<div class="page-view-stats content container mt-4">
    <h2 class="mb-4"><?= __('Page View Statistics for slug: {0}', h($article->slug)); ?></h2>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <input type="date" id="start-date" class="form-control">
                <input type="date" id="end-date" class="form-control">
                <button id="filter-btn" class="btn btn-primary"><?= __('Filter') ?></button>
            </div>
        </div>
        <div class="col-md-6 text-md-right">
            <h4><?= __('Total Views: ') ?><span id="total-views"><?= array_sum(array_column($viewsOverTime->toArray(), 'count')) ?></span></h4>
        </div>
    </div>

    <div class="chart-container mb-4" style="position: relative; height:300px; width:100%">
        <canvas id="viewsChart"></canvas>
    </div>

    <?php if (!empty($viewsOverTime) && $viewsOverTime->count() > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th><?= __('Date') ?></th>
                        <th><?= __('Views') ?></th>
                    </tr>
                </thead>
                <tbody id="stats-table-body">
                    <?php foreach ($viewsOverTime as $view): ?>
                    <tr>
                        <td><?= h((new DateTime($view->date))->format('d-m-Y')) ?></td>
                        <td>
                            <?= $this->Html->link(
                                h($view->count),
                                ['action' => 'viewRecords', $article->id, '?' => ['date' => $view->date]],
                                ['title' => __('View detailed records for this date'), 'class' => 'btn btn-sm btn-outline-primary']
                            ) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info" role="alert">
            <?= __('No page view data available for this article.') ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('viewsChart').getContext('2d');
    let chart;
    const filterBtn = document.getElementById('filter-btn');
    const startDate = document.getElementById('start-date');
    const endDate = document.getElementById('end-date');
    const totalViews = document.getElementById('total-views');
    const tableBody = document.getElementById('stats-table-body');

    // Initialize chart with current data
    initChart(<?= json_encode($viewsOverTime->toArray()) ?>);

    filterBtn.addEventListener('click', function() {
        console.log('Filter button clicked'); // Debug log
        if (!startDate.value || !endDate.value) {
            alert('Please select both start and end dates');
            return;
        }

        const url = `/admin/page-views/filter-stats/<?= $article->id ?>?start=${startDate.value}&end=${endDate.value}`;
        console.log('Fetching:', url); // Debug log

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data); // Debug log
                updateTable(data.viewsOverTime);
                updateChart(data.viewsOverTime);
                totalViews.textContent = data.totalViews;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching data. Please try again.');
            });
    });

    function initChart(data) {
        const chartData = prepareChartData(data);
        chart = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    },
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            displayFormats: {
                                day: 'dd-MM-yyyy'
                            }
                        }
                    }
                }
            }
        });
    }

    function updateChart(data) {
        const chartData = prepareChartData(data);
        chart.data = chartData;
        chart.update();
    }

    function prepareChartData(data) {
        return {
            datasets: [{
                label: 'Page Views',
                data: data.map(item => ({x: new Date(item.date), y: item.count})),
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        };
    }

    function updateTable(viewsData) {
        tableBody.innerHTML = '';
        viewsData.forEach(view => {
            const row = `
                <tr>
                    <td>${formatDate(view.date)}</td>
                    <td>
                        <a href="/admin/page-views/view-records/<?= $article->id ?>?date=${view.date}" 
                           class="btn btn-sm btn-outline-primary" 
                           title="View detailed records for this date">
                            ${view.count}
                        </a>
                    </td>
                </tr>
            `;
            tableBody.innerHTML += row;
        });
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '-');
    }
});
</script>