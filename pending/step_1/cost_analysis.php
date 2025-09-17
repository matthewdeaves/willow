<?php
/**
 * Cost Analysis Template for Admin Pages
 * @var \App\View\AppView $this
 * @var array $platforms
 * @var array $aiCosts
 * @var array $insights
 */

$this->assign('title', 'Server Deployment Cost Analysis');
?>

<div class="cost-analysis-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="title-section">
                <i class="fas fa-chart-line header-icon"></i>
                <h1>Server Deployment Cost Analysis</h1>
                <p class="subtitle">10-Year Infrastructure Investment Comparison for Willow CMS</p>
            </div>
            <div class="summary-stats">
                <div class="stat-card primary">
                    <span class="stat-number"><?= count($platforms) ?></span>
                    <span class="stat-label">Platforms Analyzed</span>
                </div>
                <div class="stat-card success">
                    <span class="stat-number">$<?= number_format(min(array_column($platforms, 'ten_year_cost'))) ?></span>
                    <span class="stat-label">Minimum 10-Year Cost</span>
                </div>
                <div class="stat-card warning">
                    <span class="stat-number">$<?= number_format($aiCosts['estimated_yearly']) ?></span>
                    <span class="stat-label">Estimated AI Costs/Year</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Cost Comparison Grid -->
    <div class="platforms-section">
        <h2><i class="fas fa-balance-scale"></i> Platform Comparison</h2>
        <p class="section-description">
            Comprehensive analysis of deployment platforms for a 10-year CakePHP server project with AI integration.
            <strong>Note:</strong> Infrastructure costs are minimal compared to AI API usage (~$3,000/year).
        </p>

        <div class="platforms-grid">
            <?php foreach ($platforms as $platform): ?>
                <div class="platform-card <?= $platform['color_class'] ?> <?= !empty($platform['recommended']) ? 'recommended' : '' ?>">
                    <?php if (!empty($platform['recommended'])): ?>
                        <div class="recommended-badge">
                            <i class="fas fa-star"></i> RECOMMENDED
                        </div>
                    <?php endif; ?>

                    <div class="platform-header">
                        <i class="<?= $platform['icon'] ?> platform-icon"></i>
                        <h3><?= h($platform['name']) ?></h3>
                        <span class="category-badge <?= $platform['category'] ?>"><?= ucwords(str_replace('-', ' ', $platform['category'])) ?></span>
                    </div>

                    <div class="cost-display">
                        <div class="cost-item">
                            <span class="cost-label">Monthly:</span>
                            <span class="cost-value"><?= $platform['monthly_cost'] == 0 ? 'FREE' : '$' . number_format($platform['monthly_cost']) ?></span>
                        </div>
                        <div class="cost-item">
                            <span class="cost-label">Yearly:</span>
                            <span class="cost-value"><?= $platform['yearly_cost'] == 0 ? 'FREE' : '$' . number_format($platform['yearly_cost']) ?></span>
                        </div>
                        <div class="cost-item total">
                            <span class="cost-label">10-Year Total:</span>
                            <span class="cost-value"><?= $platform['ten_year_cost'] == 0 ? 'FREE' : '$' . number_format($platform['ten_year_cost']) ?></span>
                        </div>
                    </div>

                    <div class="platform-details">
                        <div class="detail-row">
                            <i class="fas fa-layer-group"></i>
                            <span><strong>Difficulty:</strong> <?= h($platform['difficulty']) ?></span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-user-graduate"></i>
                            <span><strong>Experience:</strong> <?= h($platform['experience_needed']) ?></span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-expand-arrows-alt"></i>
                            <span><strong>Scaling:</strong> <?= h($platform['scalability']) ?></span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-bullseye"></i>
                            <span><strong>Best For:</strong> <?= h($platform['best_for']) ?></span>
                        </div>
                    </div>

                    <div class="pros-cons">
                        <div class="pros">
                            <h4><i class="fas fa-check-circle"></i> Pros</h4>
                            <ul>
                                <?php foreach ($platform['pros'] as $pro): ?>
                                    <li><?= h($pro) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="cons">
                            <h4><i class="fas fa-times-circle"></i> Cons</h4>
                            <ul>
                                <?php foreach ($platform['cons'] as $con): ?>
                                    <li><?= h($con) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- AI Cost Impact Section -->
    <div class="ai-costs-section">
        <h2><i class="fas fa-robot"></i> AI Integration Cost Impact</h2>
        <div class="ai-cost-grid">
            <div class="ai-cost-card primary">
                <i class="fas fa-brain"></i>
                <h3>Anthropic Claude API</h3>
                <div class="cost-breakdown">
                    <div class="cost-line">
                        <span>Rate:</span>
                        <span class="cost-value">$<?= $aiCosts['anthropic_claude'] ?>/1M characters</span>
                    </div>
                    <div class="cost-line">
                        <span>Estimated Monthly:</span>
                        <span class="cost-value">$<?= number_format($aiCosts['estimated_monthly']) ?></span>
                    </div>
                    <div class="cost-line total">
                        <span>Yearly Estimate:</span>
                        <span class="cost-value">$<?= number_format($aiCosts['estimated_yearly']) ?></span>
                    </div>
                </div>
            </div>

            <div class="comparison-card">
                <h3><i class="fas fa-calculator"></i> Cost Reality Check</h3>
                <div class="comparison-item">
                    <span class="comparison-label">Most expensive infrastructure (10 years):</span>
                    <span class="comparison-value">$<?= number_format(max(array_column($platforms, 'ten_year_cost'))) ?></span>
                </div>
                <div class="comparison-item">
                    <span class="comparison-label">AI costs (10 years est.):</span>
                    <span class="comparison-value">$<?= number_format($aiCosts['estimated_yearly'] * 10) ?></span>
                </div>
                <div class="comparison-item highlight">
                    <span class="comparison-label">AI costs dominate by:</span>
                    <span class="comparison-value"><?= round(($aiCosts['estimated_yearly'] * 10) / max(array_column($platforms, 'ten_year_cost')), 1) ?>x</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Insights Section -->
    <div class="insights-section">
        <h2><i class="fas fa-lightbulb"></i> Key Insights & Recommendations</h2>
        <div class="insights-grid">
            <?php foreach ($insights as $index => $insight): ?>
                <div class="insight-card">
                    <div class="insight-number"><?= $index + 1 ?></div>
                    <div class="insight-content">
                        <p><?= h($insight) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="recommendation-banner">
            <div class="recommendation-content">
                <i class="fas fa-trophy"></i>
                <div>
                    <h3>Final Recommendation</h3>
                    <p>Start with <strong>Digital Ocean Droplet + Docker Compose</strong> at $8/month. 
                    Scale to Kubernetes only when traffic demands it. Focus budget optimization on 
                    AI prompt efficiency rather than infrastructure costs.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Development Path Timeline -->
    <div class="timeline-section">
        <h2><i class="fas fa-road"></i> Recommended Development Path</h2>
        <div class="development-timeline">
            <div class="timeline-item">
                <div class="timeline-marker phase-1"></div>
                <div class="timeline-content">
                    <h4>Phase 1: Development (Months 1-6)</h4>
                    <p><strong>Kind (Local):</strong> $0/month for development</p>
                    <p><strong>Transition to DO Droplet:</strong> $7/month</p>
                    <div class="phase-cost">Total Cost: ~$42</div>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-marker phase-2"></div>
                <div class="timeline-content">
                    <h4>Phase 2: Production Prep (Months 7-12)</h4>
                    <p><strong>Docker Compose:</strong> $8-12/month</p>
                    <p><strong>GitHub Actions CI/CD:</strong> Free for public repos</p>
                    <div class="phase-cost">Total Cost: ~$144-288</div>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-marker phase-3"></div>
                <div class="timeline-content">
                    <h4>Phase 3: Scale When Needed (Years 2-10)</h4>
                    <p><strong>Stay Simple:</strong> Docker Compose at $8/month</p>
                    <p><strong>Or Scale Up:</strong> Kubernetes at $25/month</p>
                    <div class="phase-cost">Total Cost: $2,160-7,200</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Cost Analysis Page Styles using existing color system */
.cost-analysis-page {
    font-family: var(--font-family-base);
    background: var(--color-background);
    min-height: 100vh;
    padding: var(--space-32);
}

/* Page Header */
.page-header {
    background: var(--color-bg-1);
    border-radius: var(--radius-lg);
    padding: var(--space-32);
    margin-bottom: var(--space-32);
    border: 1px solid var(--color-card-border);
}

.header-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--space-32);
    align-items: center;
}

.title-section {
    text-align: left;
}

.header-icon {
    font-size: 3rem;
    color: var(--color-primary);
    margin-bottom: var(--space-16);
}

.title-section h1 {
    font-size: var(--font-size-4xl);
    margin-bottom: var(--space-12);
    background: linear-gradient(135deg, var(--color-primary), var(--color-teal-400));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.subtitle {
    font-size: var(--font-size-lg);
    color: var(--color-text-secondary);
    font-style: italic;
}

/* Summary Stats */
.summary-stats {
    display: flex;
    flex-direction: column;
    gap: var(--space-16);
}

.stat-card {
    background: var(--color-surface);
    padding: var(--space-16);
    border-radius: var(--radius-base);
    border: 1px solid var(--color-card-border);
    text-align: center;
}

.stat-card.primary {
    border: 2px solid var(--color-primary);
    background: var(--color-bg-1);
}

.stat-card.success {
    border: 2px solid var(--color-success);
    background: var(--color-bg-3);
}

.stat-card.warning {
    border: 2px solid var(--color-warning);
    background: var(--color-bg-2);
}

.stat-number {
    display: block;
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--color-primary);
    line-height: 1;
}

.stat-card.success .stat-number {
    color: var(--color-success);
}

.stat-card.warning .stat-number {
    color: var(--color-warning);
}

.stat-label {
    font-size: var(--font-size-sm);
    color: var(--color-text-secondary);
    margin-top: var(--space-4);
    display: block;
}

/* Section Headers */
.cost-analysis-page h2 {
    display: flex;
    align-items: center;
    gap: var(--space-12);
    margin-bottom: var(--space-16);
    font-size: var(--font-size-3xl);
    color: var(--color-text);
}

.section-description {
    color: var(--color-text-secondary);
    font-size: var(--font-size-lg);
    margin-bottom: var(--space-32);
    line-height: var(--line-height-normal);
}

/* Platform Cards Grid */
.platforms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: var(--space-24);
    margin-bottom: var(--space-32);
}

.platform-card {
    background: var(--color-surface);
    border-radius: var(--radius-lg);
    padding: var(--space-24);
    border: 1px solid var(--color-card-border);
    position: relative;
    transition: transform var(--duration-normal) var(--ease-standard);
}

.platform-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.platform-card.primary {
    border: 2px solid var(--color-primary);
    background: var(--color-bg-1);
}

.platform-card.success {
    border: 2px solid var(--color-success);
    background: var(--color-bg-3);
}

.platform-card.info {
    border: 2px solid var(--color-info);
    background: var(--color-bg-8);
}

.platform-card.warning {
    border: 2px solid var(--color-warning);
    background: var(--color-bg-2);
}

.platform-card.error {
    border: 2px solid var(--color-error);
    background: var(--color-bg-4);
}

.platform-card.recommended {
    border: 3px solid var(--color-success);
    background: var(--color-bg-3);
}

/* Recommended Badge */
.recommended-badge {
    position: absolute;
    top: -12px;
    right: var(--space-16);
    background: var(--color-success);
    color: var(--color-btn-primary-text);
    padding: var(--space-6) var(--space-12);
    border-radius: var(--radius-full);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-bold);
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

/* Platform Header */
.platform-header {
    display: flex;
    align-items: center;
    gap: var(--space-12);
    margin-bottom: var(--space-20);
    flex-wrap: wrap;
}

.platform-icon {
    font-size: 2.5rem;
    color: var(--color-primary);
}

.platform-card.success .platform-icon {
    color: var(--color-success);
}

.platform-card.warning .platform-icon {
    color: var(--color-warning);
}

.platform-card.error .platform-icon {
    color: var(--color-error);
}

.platform-card.info .platform-icon {
    color: var(--color-info);
}

.platform-header h3 {
    flex: 1;
    margin: 0;
    font-size: var(--font-size-xl);
}

.category-badge {
    padding: var(--space-4) var(--space-8);
    border-radius: var(--radius-base);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    background: var(--color-secondary);
    color: var(--color-text-secondary);
}

.category-badge.zero-cost {
    background: rgba(var(--color-success-rgb), 0.2);
    color: var(--color-success);
}

.category-badge.low-cost {
    background: rgba(var(--color-primary-rgb), 0.2);
    color: var(--color-primary);
}

.category-badge.moderate-cost {
    background: rgba(var(--color-warning-rgb), 0.2);
    color: var(--color-warning);
}

.category-badge.expensive {
    background: rgba(var(--color-error-rgb), 0.2);
    color: var(--color-error);
}

/* Cost Display */
.cost-display {
    background: rgba(var(--color-primary-rgb), 0.05);
    border-radius: var(--radius-base);
    padding: var(--space-16);
    margin-bottom: var(--space-20);
}

.cost-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-8) 0;
    border-bottom: 1px solid rgba(var(--color-primary-rgb), 0.1);
}

.cost-item:last-child {
    border-bottom: none;
}

.cost-item.total {
    font-weight: var(--font-weight-bold);
    font-size: var(--font-size-lg);
    color: var(--color-primary);
    border-top: 2px solid rgba(var(--color-primary-rgb), 0.3);
    margin-top: var(--space-8);
    padding-top: var(--space-12);
}

.cost-label {
    color: var(--color-text-secondary);
}

.cost-value {
    font-weight: var(--font-weight-semibold);
    color: var(--color-text);
}

/* Platform Details */
.platform-details {
    margin-bottom: var(--space-20);
}

.detail-row {
    display: flex;
    align-items: center;
    gap: var(--space-8);
    margin-bottom: var(--space-8);
    font-size: var(--font-size-sm);
}

.detail-row i {
    color: var(--color-primary);
    width: 16px;
    text-align: center;
}

/* Pros and Cons */
.pros-cons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-16);
}

.pros, .cons {
    font-size: var(--font-size-sm);
}

.pros h4, .cons h4 {
    display: flex;
    align-items: center;
    gap: var(--space-8);
    margin-bottom: var(--space-8);
    font-size: var(--font-size-sm);
}

.pros h4 {
    color: var(--color-success);
}

.cons h4 {
    color: var(--color-error);
}

.pros ul, .cons ul {
    margin: 0;
    padding-left: var(--space-16);
    list-style: none;
}

.pros li, .cons li {
    margin-bottom: var(--space-4);
    padding-left: var(--space-16);
    position: relative;
    line-height: var(--line-height-normal);
}

.pros li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: var(--color-success);
    font-weight: bold;
}

.cons li:before {
    content: "✗";
    position: absolute;
    left: 0;
    color: var(--color-error);
    font-weight: bold;
}

/* AI Costs Section */
.ai-costs-section {
    background: var(--color-bg-1);
    border-radius: var(--radius-lg);
    padding: var(--space-32);
    margin-bottom: var(--space-32);
    border: 1px solid var(--color-card-border);
}

.ai-cost-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-24);
    margin-top: var(--space-24);
}

.ai-cost-card {
    background: var(--color-surface);
    border-radius: var(--radius-lg);
    padding: var(--space-24);
    border: 2px solid var(--color-primary);
    text-align: center;
}

.ai-cost-card i {
    font-size: 3rem;
    color: var(--color-primary);
    margin-bottom: var(--space-16);
}

.cost-breakdown {
    text-align: left;
    margin-top: var(--space-16);
}

.cost-line {
    display: flex;
    justify-content: space-between;
    padding: var(--space-8) 0;
    border-bottom: 1px solid var(--color-border);
}

.cost-line.total {
    font-weight: var(--font-weight-bold);
    color: var(--color-primary);
    border-top: 2px solid var(--color-primary);
    margin-top: var(--space-8);
}

/* Comparison Card */
.comparison-card {
    background: var(--color-surface);
    border-radius: var(--radius-lg);
    padding: var(--space-24);
    border: 1px solid var(--color-card-border);
}

.comparison-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-8) 0;
    border-bottom: 1px solid var(--color-border);
}

.comparison-item.highlight {
    background: rgba(var(--color-warning-rgb), 0.1);
    padding: var(--space-12);
    border-radius: var(--radius-base);
    margin-top: var(--space-8);
    border: 1px solid var(--color-warning);
    color: var(--color-warning);
    font-weight: var(--font-weight-bold);
}

/* Insights Section */
.insights-section {
    background: var(--color-bg-3);
    border-radius: var(--radius-lg);
    padding: var(--space-32);
    margin-bottom: var(--space-32);
    border: 1px solid var(--color-card-border);
}

.insights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--space-16);
    margin-bottom: var(--space-32);
}

.insight-card {
    background: var(--color-surface);
    border-radius: var(--radius-lg);
    padding: var(--space-20);
    border: 1px solid var(--color-card-border);
    display: flex;
    align-items: flex-start;
    gap: var(--space-16);
}

.insight-number {
    background: var(--color-primary);
    color: var(--color-btn-primary-text);
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: var(--font-weight-bold);
    flex-shrink: 0;
}

.insight-content p {
    margin: 0;
    line-height: var(--line-height-normal);
}

/* Recommendation Banner */
.recommendation-banner {
    background: var(--color-surface);
    border: 2px solid var(--color-success);
    border-radius: var(--radius-lg);
    padding: var(--space-24);
}

.recommendation-content {
    display: flex;
    align-items: flex-start;
    gap: var(--space-16);
}

.recommendation-content i {
    font-size: 2rem;
    color: var(--color-success);
    margin-top: var(--space-4);
}

.recommendation-content h3 {
    color: var(--color-success);
    margin-bottom: var(--space-8);
}

/* Timeline Section */
.timeline-section {
    background: var(--color-surface);
    border-radius: var(--radius-lg);
    padding: var(--space-32);
    border: 1px solid var(--color-card-border);
}

.development-timeline {
    position: relative;
    margin-top: var(--space-32);
}

.development-timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--color-border);
}

.timeline-item {
    position: relative;
    margin-bottom: var(--space-32);
    padding-left: var(--space-32);
}

.timeline-marker {
    position: absolute;
    left: 12px;
    top: var(--space-8);
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid var(--color-border);
    background: var(--color-surface);
}

.timeline-marker.phase-1 {
    background: var(--color-success);
    border-color: var(--color-success);
}

.timeline-marker.phase-2 {
    background: var(--color-warning);
    border-color: var(--color-warning);
}

.timeline-marker.phase-3 {
    background: var(--color-primary);
    border-color: var(--color-primary);
}

.timeline-content {
    background: var(--color-bg-1);
    border-radius: var(--radius-base);
    padding: var(--space-20);
    border: 1px solid var(--color-card-border);
}

.timeline-content h4 {
    color: var(--color-primary);
    margin-bottom: var(--space-12);
}

.phase-cost {
    background: rgba(var(--color-primary-rgb), 0.1);
    color: var(--color-primary);
    padding: var(--space-8);
    border-radius: var(--radius-base);
    margin-top: var(--space-12);
    font-weight: var(--font-weight-semibold);
    text-align: center;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .platforms-grid {
        grid-template-columns: 1fr;
    }

    .header-content {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .ai-cost-grid,
    .pros-cons {
        grid-template-columns: 1fr;
    }

    .insights-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .cost-analysis-page {
        padding: var(--space-16);
    }

    .summary-stats {
        flex-direction: column;
    }

    .platform-header {
        flex-direction: column;
        text-align: center;
    }

    .cost-display {
        font-size: var(--font-size-sm);
    }

    .recommendation-content {
        flex-direction: column;
        text-align: center;
    }
}
</style>