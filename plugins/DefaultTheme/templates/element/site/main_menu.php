<?php $mbAmount = $mbAmount ?? 0; ?>
<?php $currentUrl = $this->request->getPath(); ?>

<div class="nav-scroller py-1 mb-<?= $mbAmount ?> border-bottom">
    <nav class="nav nav-underline justify-content-center" role="navigation" aria-label="<?= __('Main navigation') ?>">

        <?php $url = $this->Html->Url->build(['_name' => 'home']); ?>
        <?= $this->Html->link(__('Blog'), $url, [
            'class' => 'nav-item nav-link link-body-emphasis fw-medium px-3' . (($currentUrl == $url) ? ' active' : ''),
            'aria-current' => ($currentUrl == $url) ? 'page' : false
        ]) ?>

        <?php foreach ($menuPages as $menuPage) : ?>
            <?php $url = $this->Html->Url->build(['_name' => 'page-by-slug', 'slug' => $menuPage['slug']]); ?>
            <?=
                $this->Html->link(
                    htmlspecialchars_decode($menuPage['title']),
                    $url,
                    [
                        'class' => 'nav-item nav-link link-body-emphasis fw-medium px-3' . (($currentUrl == $url) ? ' active' : ''),
                        'escape' => false,
                        'aria-current' => ($currentUrl == $url) ? 'page' : false
                    ]
                );
            ?>
        <?php endforeach ?>
        <a class="nav-item nav-link link-body-emphasis fw-medium px-3" 
           href="https://www.github.com/matthewdeaves/willow" 
           target="_blank" 
           rel="noopener noreferrer"
           aria-label="<?= __('Visit GitHub repository (opens in new tab)') ?>">
           GitHub
           <svg class="bi ms-1" width="12" height="12" fill="currentColor">
               <use xlink:href="#external-link"/>
           </svg>
        </a>
    </nav>
</div>

<!-- Add external link icon -->
<svg style="display: none;">
    <symbol id="external-link" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
        <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
    </symbol>
</svg>