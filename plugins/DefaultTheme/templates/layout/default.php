<?php
$this->extend('./site');
$this->append('main_menu');
echo $this->element('site/main_menu', ['mbAmount' => 3]);
$this->end();
?>

<div class="row g-5">
    <div class="col-md-12">
        <?= $this->fetch('content') ?>
    </div>
</div>
