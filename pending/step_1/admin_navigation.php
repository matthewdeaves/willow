<?php
/**
 * Admin Navigation Menu Integration
 * 
 * Add this to your admin layout template or navigation component
 * File: plugins/Admin/templates/layout/default.php or similar
 */

// Navigation Menu Items Array
$adminMenuItems = [
    [
        'title' => 'Dashboard',
        'url' => ['controller' => 'Dashboard', 'action' => 'index'],
        'icon' => 'fas fa-tachometer-alt',
        'active' => $this->request->getParam('controller') === 'Dashboard'
    ],
    [
        'title' => 'Content Management',
        'url' => '#',
        'icon' => 'fas fa-edit',
        'submenu' => [
            [
                'title' => 'Pages',
                'url' => ['controller' => 'Pages', 'action' => 'index'],
                'icon' => 'fas fa-file-alt'
            ],
            [
                'title' => 'Articles',
                'url' => ['controller' => 'Articles', 'action' => 'index'], 
                'icon' => 'fas fa-newspaper'
            ]
        ]
    ],
    [
        'title' => 'System Analysis',
        'url' => '#',
        'icon' => 'fas fa-chart-line',
        'submenu' => [
            [
                'title' => 'Cost Analysis',
                'url' => ['controller' => 'Pages', 'action' => 'costAnalysis'],
                'icon' => 'fas fa-calculator',
                'description' => 'Server deployment cost comparison',
                'badge' => 'NEW',
                'active' => $this->request->getParam('action') === 'costAnalysis'
            ],
            [
                'title' => 'Performance Monitor',
                'url' => ['controller' => 'Monitor', 'action' => 'performance'],
                'icon' => 'fas fa-tachometer-alt'
            ],
            [
                'title' => 'AI Metrics',
                'url' => ['controller' => 'Ai', 'action' => 'metrics'],
                'icon' => 'fas fa-robot'
            ]
        ]
    ],
    [
        'title' => 'Configuration',
        'url' => '#',
        'icon' => 'fas fa-cog',
        'submenu' => [
            [
                'title' => 'Settings',
                'url' => ['controller' => 'Settings', 'action' => 'index'],
                'icon' => 'fas fa-sliders-h'
            ],
            [
                'title' => 'Users',
                'url' => ['controller' => 'Users', 'action' => 'index'],
                'icon' => 'fas fa-users'
            ]
        ]
    ]
];
?>

<!-- Admin Navigation HTML -->
<nav class="admin-navigation">
    <div class="nav-brand">
        <i class="fas fa-tree"></i>
        <span>Willow CMS Admin</span>
    </div>

    <ul class="nav-menu">
        <?php foreach ($adminMenuItems as $item): ?>
            <li class="nav-item <?= !empty($item['active']) ? 'active' : '' ?> <?= !empty($item['submenu']) ? 'has-submenu' : '' ?>">
                <?php if (!empty($item['submenu'])): ?>
                    <a href="<?= h($item['url']) ?>" class="nav-link dropdown-toggle">
                        <i class="<?= h($item['icon']) ?>"></i>
                        <span><?= h($item['title']) ?></span>
                        <i class="fas fa-chevron-down submenu-arrow"></i>
                    </a>
                    <ul class="submenu">
                        <?php foreach ($item['submenu'] as $subitem): ?>
                            <li class="submenu-item <?= !empty($subitem['active']) ? 'active' : '' ?>">
                                <?= $this->Html->link(
                                    '<i class="' . h($subitem['icon']) . '"></i>' .
                                    '<span>' . h($subitem['title']) . '</span>' .
                                    (!empty($subitem['badge']) ? '<span class="badge">' . h($subitem['badge']) . '</span>' : ''),
                                    $subitem['url'],
                                    [
                                        'class' => 'submenu-link',
                                        'escape' => false,
                                        'title' => !empty($subitem['description']) ? h($subitem['description']) : h($subitem['title'])
                                    ]
                                ) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <?= $this->Html->link(
                        '<i class="' . h($item['icon']) . '"></i><span>' . h($item['title']) . '</span>',
                        $item['url'],
                        [
                            'class' => 'nav-link',
                            'escape' => false
                        ]
                    ) ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

<!-- Quick Access Widget for Cost Analysis -->
<div class="quick-access-widget">
    <h3><i class="fas fa-rocket"></i> Quick Access</h3>
    <div class="widget-content">
        <?= $this->Html->link(
            '<i class="fas fa-calculator"></i>' .
            '<div class="widget-info">' .
                '<strong>Cost Analysis</strong>' .
                '<span>Compare deployment platforms</span>' .
            '</div>',
            ['controller' => 'Pages', 'action' => 'costAnalysis'],
            [
                'class' => 'widget-link primary',
                'escape' => false
            ]
        ) ?>

        <?= $this->Html->link(
            '<i class="fas fa-robot"></i>' .
            '<div class="widget-info">' .
                '<strong>AI Metrics</strong>' .
                '<span>Monitor API usage & costs</span>' .
            '</div>',
            ['controller' => 'Ai', 'action' => 'metrics'],
            [
                'class' => 'widget-link info',
                'escape' => false
            ]
        ) ?>
    </div>
</div>

<style>
/* Admin Navigation Styles using existing color system */
.admin-navigation {
    background: var(--color-surface);
    border-right: 1px solid var(--color-card-border);
    width: 280px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    overflow-y: auto;
    z-index: 1000;
}

.nav-brand {
    display: flex;
    align-items: center;
    gap: var(--space-12);
    padding: var(--space-20);
    border-bottom: 1px solid var(--color-card-border);
    background: var(--color-bg-1);
}

.nav-brand i {
    font-size: 1.5rem;
    color: var(--color-primary);
}

.nav-brand span {
    font-weight: var(--font-weight-semibold);
    font-size: var(--font-size-lg);
    color: var(--color-text);
}

.nav-menu {
    list-style: none;
    margin: 0;
    padding: var(--space-16) 0;
}

.nav-item {
    margin-bottom: var(--space-4);
    position: relative;
}

.nav-link, .submenu-link {
    display: flex;
    align-items: center;
    gap: var(--space-12);
    padding: var(--space-12) var(--space-20);
    color: var(--color-text);
    text-decoration: none;
    transition: all var(--duration-fast) var(--ease-standard);
    border-radius: 0 var(--radius-base) var(--radius-base) 0;
    margin-right: var(--space-8);
}

.nav-link:hover, .submenu-link:hover {
    background: var(--color-bg-1);
    color: var(--color-primary);
    transform: translateX(4px);
}

.nav-item.active > .nav-link,
.submenu-item.active .submenu-link {
    background: var(--color-primary);
    color: var(--color-btn-primary-text);
    font-weight: var(--font-weight-semibold);
}

.nav-item.active > .nav-link::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: var(--color-primary);
}

.submenu-arrow {
    margin-left: auto;
    font-size: var(--font-size-sm);
    transition: transform var(--duration-fast) var(--ease-standard);
}

.nav-item.has-submenu:hover .submenu-arrow {
    transform: rotate(180deg);
}

.submenu {
    list-style: none;
    margin: 0;
    padding: 0;
    background: var(--color-bg-1);
    border-radius: 0 0 var(--radius-base) 0;
    margin-right: var(--space-8);
    max-height: 0;
    overflow: hidden;
    transition: max-height var(--duration-normal) var(--ease-standard);
}

.nav-item.has-submenu:hover .submenu {
    max-height: 500px;
}

.submenu-item {
    border-left: 2px solid var(--color-border);
}

.submenu-link {
    padding-left: var(--space-32);
    font-size: var(--font-size-sm);
}

.badge {
    background: var(--color-success);
    color: var(--color-btn-primary-text);
    font-size: var(--font-size-xs);
    padding: var(--space-2) var(--space-6);
    border-radius: var(--radius-full);
    font-weight: var(--font-weight-bold);
    margin-left: auto;
}

/* Quick Access Widget */
.quick-access-widget {
    background: var(--color-surface);
    border: 1px solid var(--color-card-border);
    border-radius: var(--radius-lg);
    padding: var(--space-20);
    margin: var(--space-20);
}

.quick-access-widget h3 {
    display: flex;
    align-items: center;
    gap: var(--space-8);
    margin-bottom: var(--space-16);
    font-size: var(--font-size-lg);
    color: var(--color-text);
}

.quick-access-widget h3 i {
    color: var(--color-primary);
}

.widget-content {
    display: flex;
    flex-direction: column;
    gap: var(--space-12);
}

.widget-link {
    display: flex;
    align-items: center;
    gap: var(--space-12);
    padding: var(--space-12);
    border-radius: var(--radius-base);
    text-decoration: none;
    transition: all var(--duration-fast) var(--ease-standard);
    border: 1px solid var(--color-card-border);
}

.widget-link:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.widget-link.primary {
    background: var(--color-bg-1);
    border-color: var(--color-primary);
}

.widget-link.primary:hover {
    background: var(--color-primary);
    color: var(--color-btn-primary-text);
}

.widget-link.info {
    background: var(--color-bg-8);
    border-color: var(--color-info);
}

.widget-link.info:hover {
    background: var(--color-info);
    color: var(--color-btn-primary-text);
}

.widget-link i {
    font-size: 1.5rem;
    color: var(--color-primary);
    flex-shrink: 0;
}

.widget-info strong {
    display: block;
    color: var(--color-text);
    font-weight: var(--font-weight-semibold);
    margin-bottom: var(--space-2);
}

.widget-info span {
    font-size: var(--font-size-sm);
    color: var(--color-text-secondary);
}

.widget-link:hover .widget-info strong,
.widget-link:hover .widget-info span,
.widget-link:hover i {
    color: inherit;
}

/* Responsive Navigation */
@media (max-width: 768px) {
    .admin-navigation {
        width: 100%;
        height: auto;
        position: relative;
    }

    .quick-access-widget {
        margin: var(--space-12);
    }

    .widget-content {
        flex-direction: row;
        gap: var(--space-8);
    }

    .widget-link {
        flex: 1;
        justify-content: center;
        text-align: center;
    }

    .widget-info {
        display: none;
    }
}
</style>
