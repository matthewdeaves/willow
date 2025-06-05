<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Model\Entity\ImageGallery;
use Cake\View\Helper;
use Cake\View\Helper\FormHelper;
use Cake\View\Helper\HtmlHelper;
use Cake\View\Helper\TextHelper;
use Cake\View\Helper\UrlHelper;

/**
 * Shared Gallery Helper
 *
 * Provides template logic helpers for Image Gallery functionality across themes.
 * Auto-detects theme context and provides appropriate features.
 *
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \Cake\View\Helper\FormHelper $Form
 * @property \Cake\View\Helper\TextHelper $Text
 * @property \Cake\View\Helper\UrlHelper $Url
 */
class GalleryHelper extends Helper
{
    /**
     * Helpers used by this helper
     *
     * @var array
     */
    protected array $helpers = ['Html', 'Form', 'Text', 'Url'];

    /**
     * Detected theme context
     *
     * @var string|null
     */
    private ?string $theme = null;

    /**
     * Initialize helper and detect theme
     *
     * @param array $config Configuration options
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->theme = $this->detectTheme();
    }

    /**
     * Detect current theme context
     *
     * @return string 'admin' or 'default'
     */
    public function detectTheme(): string
    {
        // Check current request path
        $request = $this->getView()->getRequest();
        if ($request && str_contains($request->getPath(), '/admin/')) {
            return 'admin';
        }

        // Check view file path for theme indicators
        $viewPath = $this->getView()->getTemplate() ?? '';
        if (str_contains($viewPath, 'Admin/')) {
            return 'admin';
        }

        // Check plugin context
        $plugin = $this->getView()->getPlugin();
        if ($plugin === 'AdminTheme') {
            return 'admin';
        }

        return 'default';
    }

    /**
     * Get current theme
     *
     * @return string
     */
    public function getTheme(): string
    {
        return $this->theme ?? $this->detectTheme();
    }

    /**
     * Check if currently in admin theme
     *
     * @return bool
     */
    public function isAdminTheme(): bool
    {
        return $this->getTheme() === 'admin';
    }

    /**
     * Render gallery status badge (admin theme only)
     *
     * @param \App\Model\Entity\ImageGallery $gallery Gallery entity
     * @param array $options Additional HTML attributes
     * @return string HTML badge element or empty string for default theme
     */
    public function statusBadge(ImageGallery $gallery, array $options = []): string
    {
        if (!$this->isAdminTheme()) {
            return '';
        }

        $defaults = [
            'class' => 'badge gallery-status-badge ' . $gallery->getStatusClass(),
        ];
        $attributes = array_merge($defaults, $options);

        return $this->Html->tag('span', h($gallery->getStatusDisplay()), $attributes);
    }

    /**
     * Render gallery image count badge
     *
     * @param \App\Model\Entity\ImageGallery $gallery Gallery entity
     * @param array $options Additional HTML attributes
     * @return string HTML badge element
     */
    public function imageCountBadge(ImageGallery $gallery, array $options = []): string
    {
        $defaults = [
            'class' => $this->isAdminTheme() ? 'badge bg-info' : 'badge bg-secondary',
        ];
        $attributes = array_merge($defaults, $options);

        $content = $gallery->getImageCount() . ' ' . __('images');

        return $this->Html->tag('span', $content, $attributes);
    }

    /**
     * Render gallery preview image or placeholder
     *
     * @param \App\Model\Entity\ImageGallery $gallery Gallery entity
     * @param array $options Options for image rendering
     * @return string HTML image element or placeholder
     */
    public function previewImage(ImageGallery $gallery, array $options = []): string
    {
        $defaults = [
            'size' => 'thumbnail',
            'class' => 'gallery-preview-thumb',
            'popover' => $this->isAdminTheme(), // Only admin theme gets popovers
            'galleryData' => false,
        ];
        $config = array_merge($defaults, $options);

        if ($gallery->hasPreviewImage()) {
            $imageAttributes = [
                'src' => h($gallery->getPreviewImageUrl()),
                'alt' => h($gallery->name),
                'class' => $config['class'],
            ];

            // Add gallery data attribute for interactions
            if ($config['galleryData']) {
                $imageAttributes['data-gallery-id'] = 'gallery-' . $gallery->id;
            }

            // Add additional attributes
            foreach (['style', 'width', 'height'] as $attr) {
                if (isset($config[$attr])) {
                    $imageAttributes[$attr] = $config[$attr];
                }
            }

            $image = $this->Html->tag('img', '', $imageAttributes);

            // Add popover if requested (admin only)
            if ($config['popover'] && !empty($gallery->images)) {
                $popoverContent = $this->_generatePopoverContent($gallery);
                $image = $this->Html->tag('div', $image, [
                    'data-bs-toggle' => 'popover',
                    'data-bs-trigger' => 'hover',
                    'data-bs-content' => $popoverContent,
                    'data-bs-html' => 'true',
                    'data-bs-placement' => 'right',
                ]);
            }

            return $image;
        } elseif (!empty($gallery->images)) {
            // Use first gallery image as preview
            return $this->getView()->element('image/icon', [
                'image' => $gallery->images[0],
                'size' => 'tiny',
                'class' => $config['class'],
                'popover' => $config['popover'],
                'popover_content' => $config['popover'] ? $this->_generateGalleryGrid($gallery) : null,
            ]);
        } else {
            // No images placeholder
            return $this->_renderPlaceholder($config);
        }
    }

    /**
     * Render gallery card for grid view (admin theme only)
     *
     * @param \App\Model\Entity\ImageGallery $gallery Gallery entity
     * @param array $options Card rendering options
     * @return string HTML card element or simple preview for default theme
     */
    public function galleryCard(ImageGallery $gallery, array $options = []): string
    {
        if (!$this->isAdminTheme()) {
            // Default theme gets simple gallery preview
            return $this->getView()->element('shared_photo_gallery', [
                'images' => $gallery->images,
                'title' => $gallery->name,
                'theme' => 'default',
            ]);
        }

        $defaults = [
            'showActions' => true,
            'showPreview' => true,
            'cardClass' => 'card h-100 gallery-card',
        ];
        $config = array_merge($defaults, $options);

        $cardContent = [];

        // Card header
        $cardContent[] = $this->Html->tag('div', 
            $this->Html->tag('h6', h($gallery->name), ['class' => 'mb-0']) . 
            $this->statusBadge($gallery),
            ['class' => 'card-header d-flex justify-content-between align-items-center']
        );

        // Card body with preview
        if ($config['showPreview']) {
            $bodyContent = $this->_renderCardBody($gallery);
            $cardContent[] = $this->Html->tag('div', $bodyContent, ['class' => 'card-body p-0']);
        }

        // Card footer with actions
        if ($config['showActions']) {
            $footerContent = $this->_renderCardActions($gallery);
            $cardContent[] = $this->Html->tag('div', $footerContent, ['class' => 'card-footer']);
        }

        return $this->Html->tag('div', implode('', $cardContent), ['class' => $config['cardClass']]);
    }

    /**
     * Render view switcher buttons (admin theme only)
     */
    public function viewSwitcher(string $currentView, array $queryParams = []): string
    {
        if (!$this->isAdminTheme()) {
            return '';
        }

        $buttons = [];

        // List view button
        $buttons[] = $this->Html->link(
            '<i class="fas fa-list"></i>',
            ['action' => 'index', '?' => ['view' => 'list'] + $queryParams],
            [
                'class' => 'btn ' . ($currentView === 'list' ? 'btn-primary' : 'btn-outline-secondary'),
                'escape' => false,
                'title' => __('List View'),
            ]
        );

        // Grid view button
        $buttons[] = $this->Html->link(
            '<i class="fas fa-th"></i>',
            ['action' => 'index', '?' => ['view' => 'grid'] + $queryParams],
            [
                'class' => 'btn ' . ($currentView === 'grid' ? 'btn-primary' : 'btn-outline-secondary'),
                'escape' => false,
                'title' => __('Grid View'),
            ]
        );

        return $this->Html->tag('div', implode('', $buttons), [
            'class' => 'btn-group me-3',
            'role' => 'group',
        ]);
    }

    /**
     * Render search form
     */
    public function searchForm(?string $currentSearch = null): string
    {
        $inputClass = $this->isAdminTheme() ? 'form-control' : 'form-control form-control-sm';
        $buttonClass = $this->isAdminTheme() ? 'btn btn-outline-secondary' : 'btn btn-outline-primary btn-sm';

        $inputGroup = $this->Html->tag('div', 
            $this->Form->control('search', [
                'type' => 'search',
                'id' => 'gallery-search',
                'class' => $inputClass,
                'placeholder' => __('Search galleries...'),
                'value' => h($currentSearch),
                'label' => false,
            ]) . 
            $this->Html->tag('button', '<i class="fas fa-search"></i>', [
                'class' => $buttonClass,
                'type' => 'submit',
                'escape' => false,
            ]),
            ['class' => 'input-group']
        );

        $formClass = $this->isAdminTheme() ? 'd-flex me-3' : 'd-flex mb-3';
        return $this->Html->tag('form', $inputGroup, [
            'class' => $formClass,
            'id' => 'gallery-search-form',
        ]);
    }

    /**
     * Format file size in human readable format
     */
    public function formatFileSize(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;
        $size = $bytes;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Generate popover content for gallery preview (admin only)
     */
    private function _generatePopoverContent(ImageGallery $gallery): string
    {
        if (!$this->isAdminTheme()) {
            return '';
        }

        if ($gallery->hasPreviewImage()) {
            return "<img src='" . h($gallery->getPreviewImageUrl()) . "' style='max-width: 300px; max-height: 200px;' alt='" . h($gallery->name) . "'>";
        }

        return $this->_generateGalleryGrid($gallery);
    }

    /**
     * Generate small gallery grid for popover (admin only)
     */
    private function _generateGalleryGrid(ImageGallery $gallery): string
    {
        if (!$this->isAdminTheme() || empty($gallery->images)) {
            return '<p class="text-muted">' . __('No images') . '</p>';
        }

        return $this->getView()->element('shared_photo_gallery', [
            'images' => array_slice($gallery->images, 0, 4),
            'gallery_id' => 'preview-' . $gallery->id,
            'grid_class' => 'row g-1',
            'image_class' => 'col-6',
            'theme' => 'admin',
        ]);
    }

    /**
     * Render placeholder for galleries with no images
     */
    private function _renderPlaceholder(array $config): string
    {
        $style = isset($config['style']) ? $config['style'] : 'width: 60px; height: 45px;';
        $borderStyle = $this->isAdminTheme() ? 'border: 1px solid #ddd;' : 'border: 1px solid #ccc;';
        
        return $this->Html->tag('div', 
            '<i class="fas fa-images"></i>',
            [
                'class' => 'text-center text-muted d-flex align-items-center justify-content-center',
                'style' => $style . ' ' . $borderStyle . ' border-radius: 4px;',
            ]
        );
    }

    /**
     * Render card body content (admin only)
     */
    private function _renderCardBody(ImageGallery $gallery): string
    {
        $content = '';

        if (!empty($gallery->images)) {
            // Preview overlay
            if ($gallery->hasPreviewImage()) {
                $content .= $this->Html->tag('div',
                    $this->Html->tag('img', '', [
                        'src' => h($gallery->getPreviewImageUrl()),
                        'alt' => h($gallery->name),
                        'class' => 'gallery-preview-image',
                    ]) . 
                    $this->Html->tag('div', '<i class="fas fa-images me-1"></i>' . $gallery->getImageCount(), [
                        'class' => 'gallery-image-count',
                    ]) . 
                    $this->Html->tag('div', '<i class="fas fa-play-circle fa-3x text-white"></i>', [
                        'class' => 'position-absolute top-50 start-50 translate-middle gallery-play-button',
                    ]),
                    [
                        'class' => 'gallery-preview-overlay',
                        'data-gallery-id' => 'gallery-' . $gallery->id,
                    ]
                );
            }

            // Hidden photo gallery for slideshow
            $content .= $this->Html->tag('div',
                $this->getView()->element('shared_photo_gallery', [
                    'images' => $gallery->images,
                    'title' => $gallery->name,
                    'gallery_id' => 'gallery-' . $gallery->id,
                    'theme' => 'admin',
                ]),
                ['class' => 'd-none']
            );
        } else {
            // No images state
            $content .= $this->Html->tag('div',
                '<i class="fas fa-images fa-2x mb-2"></i><p>' . __('No images') . '</p>',
                ['class' => 'text-center text-muted py-5']
            );
        }

        // Gallery info
        if ($gallery->description) {
            $content .= $this->Html->tag('div',
                $this->Html->tag('p', $this->Text->truncate(h($gallery->description), 100), ['class' => 'card-text small']),
                ['class' => 'p-3']
            );
        }

        return $content;
    }

    /**
     * Render card actions (admin only)
     */
    private function _renderCardActions(ImageGallery $gallery): string
    {
        $actions = [];

        // View button
        $actions[] = $this->Html->link(
            '<i class="fas fa-eye"></i> ' . __('View'),
            ['action' => 'view', $gallery->id],
            ['class' => 'btn btn-outline-primary btn-sm flex-fill', 'escape' => false]
        );

        // Edit button
        $actions[] = $this->Html->link(
            '<i class="fas fa-edit"></i> ' . __('Edit'),
            ['action' => 'edit', $gallery->id],
            ['class' => 'btn btn-outline-secondary btn-sm flex-fill', 'escape' => false]
        );

        // Dropdown with additional actions
        $dropdownItems = [
            $this->Html->link(
                __('Manage Images'),
                ['action' => 'manageImages', $gallery->id],
                ['class' => 'dropdown-item']
            ),
            '<hr class="dropdown-divider">',
            $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $gallery->id],
                [
                    'class' => 'dropdown-item text-danger',
                    'confirm' => __('Are you sure you want to delete this gallery?'),
                ]
            ),
        ];

        $dropdown = $this->Html->tag('button', '<i class="fas fa-ellipsis-v"></i>', [
            'class' => 'btn btn-outline-secondary btn-sm dropdown-toggle',
            'type' => 'button',
            'data-bs-toggle' => 'dropdown',
            'escape' => false,
        ]) . 
        $this->Html->tag('ul', 
            implode('', array_map(fn($item) => $this->Html->tag('li', $item), $dropdownItems)),
            ['class' => 'dropdown-menu']
        );

        $actions[] = $this->Html->tag('div', $dropdown, ['class' => 'dropdown']);

        return $this->Html->tag('div', implode('', $actions), ['class' => 'd-flex gap-2']);
    }
}