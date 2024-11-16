<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CookieConsent $cookieConsent
 */
?>
<div class="container my-4">
    <div class="row">
        <?php
        echo $this->element('actions_card', [
            'modelName' => 'Cookie Consent',
            'controllerName' => 'Cookie Consents',
            'entity' => $cookieConsent,
            'entityDisplayName' => $cookieConsent->ip_address
        ]);
        ?>
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($cookieConsent->ip_address) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($cookieConsent->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('User') ?></th>
                            <td><?= $cookieConsent->hasValue('user') ? $this->Html->link($cookieConsent->user->username, ['controller' => 'Users', 'action' => 'view', $cookieConsent->user->id], ['class' => 'btn btn-link']) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Session Id') ?></th>
                            <td><?= h($cookieConsent->session_id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Ip Address') ?></th>
                            <td><?= h($cookieConsent->ip_address) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('User Agent') ?></th>
                            <td><?= h($cookieConsent->user_agent) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($cookieConsent->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Updated') ?></th>
                            <td><?= h($cookieConsent->updated) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Analytics Consent') ?></th>
                            <td><?= $cookieConsent->analytics_consent ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Functional Consent') ?></th>
                            <td><?= $cookieConsent->functional_consent ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Marketing Consent') ?></th>
                            <td><?= $cookieConsent->marketing_consent ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Essential Consent') ?></th>
                            <td><?= $cookieConsent->essential_consent ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>