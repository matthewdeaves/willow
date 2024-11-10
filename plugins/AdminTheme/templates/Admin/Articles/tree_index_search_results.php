<?php
    if (!empty($articles)) {
        echo $this->element('page_tree', ['articles' => $articles, 'level' => 0]);
    } else {
        echo $this->Html->tag('p', __('No pages found.'));
    }
?>
