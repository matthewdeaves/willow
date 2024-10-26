<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Internationalisation $internationalisation
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <?php
        echo $this->element('actions_card', [
            'modelName' => 'Internationalisation',
            'controllerName' => 'Internationalisations',
            'entity' => $internationalisation,
            'entityDisplayName' => $internationalisation->message_id
        ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= h($internationalisation->message_id) ?></h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($internationalisation->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Locale') ?></th>
                            <td><?= h($internationalisation->locale) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Message Id') ?></th>
                            <td><?= h($internationalisation->message_id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Message Str') ?></th>
                            <td><?= h($internationalisation->message_str) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created At') ?></th>
                            <td><?= h($internationalisation->created_at) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Updated At') ?></th>
                            <td><?= h($internationalisation->updated_at) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>