<?php
use Cake\Core\Configure;

$this->layout = 'error';

if (Configure::read('debug')) :
    $this->layout = 'dev_error';

    $this->assign('title', $message);
    $this->assign('templateName', 'error429.php');

    $this->start('file');
?>
<p>The requested address was not found on this server.</p>
<?php
    $this->end();
endif;
?>
<h2><?= h($message) ?></h2>
<p>Too many requests. Please try again later.</p>