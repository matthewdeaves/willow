<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\BlockedIp $blockedIp
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <?php
            echo $this->element('actions_card', [
                'modelName' => 'BlockedIp',
                'controllerName' => 'BlockedIps',
                'entity' => $blockedIp,
                'entityDisplayName' => $blockedIp->ip_address
            ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= h($blockedIp->ip_address) ?></h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th class="w-25"><?= __('IP Address') ?></th>
                            <td><?= h($blockedIp->ip_address) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Blocked At') ?></th>
                            <td><?= $blockedIp->blocked_at ? h($blockedIp->blocked_at->format('Y-m-d H:i:s')) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Expires At') ?></th>
                            <td><?= $blockedIp->expires_at ? h($blockedIp->expires_at->format('Y-m-d H:i:s')) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= $blockedIp->created ? h($blockedIp->created->format('Y-m-d H:i:s')) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= $blockedIp->modified ? h($blockedIp->modified->format('Y-m-d H:i:s')) : '' ?></td>
                        </tr>
                    </table>
                    <div class="mt-4">
                        <h5><?= __('Reason') ?></h5>
                        <div class="border p-3 bg-light">
                            <?= $this->Text->autoParagraph(h($blockedIp->reason)); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>