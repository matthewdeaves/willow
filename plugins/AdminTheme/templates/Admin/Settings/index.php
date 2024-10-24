<?php
/**
 * @var \App\View\AppView $this
 * @var array $groupedSettings
 */
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0"><?= __('Edit Settings') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->Form->create(null, ['url' => ['action' => 'saveSettings'], 'class' => 'needs-validation', 'novalidate' => true]) ?>
                    <?php foreach ($groupedSettings as $category => $settings): ?>
                        <h4 class="mb-3 mt-4 text-secondary"><?= h($this->makeHumanReadable($category)) ?></h4>
                        <div class="row">
                        <?php foreach ($settings as $key => $setting): ?>
                            <div class="col-md-4 mb-3">
                                <?php
                                $value = $setting['value'];
                                $value_type = $setting['value_type'];
                                $obscure = isset($setting['value_obscure']) && $setting['value_obscure'] == 1;
                                ?>
                                <?php if ($value_type === 'bool'): ?>
                                    <div class="form-check form-switch">
                                        <?= $this->Form->checkbox("{$category}.{$key}", [
                                            'label' => false,
                                            'value' => 1,
                                            'class' => 'form-check-input',
                                            'checked' => (bool)$value,
                                            'type' => 'checkbox'
                                        ]) ?>
                                        <label class="form-check-label" for="<?= "{$category}-{$key}" ?>">
                                            <?= $this->makeHumanReadable($key) ?>
                                        </label>
                                    </div>
                                <?php elseif ($value_type === 'select'): ?>
                                    <?php $options = json_decode($setting['data'], true); ?>
                                    <?= $this->Form->select("{$category}.{$key}", $options, [
                                        'label' => $this->makeHumanReadable($key),
                                        'value' => $value,
                                        'class' => 'form-control'
                                    ]) ?>
                                <?php elseif ($obscure): ?>
                                    <label for="<?= "{$category}-{$key}" ?>"><?= $this->makeHumanReadable($key) ?></label>
                                    <div class="input-group">
                                        <?= $this->Form->text("{$category}.{$key}", [
                                            'value' => str_repeat('•', strlen($value)),
                                            'class' => 'form-control obscured-field obscured',
                                            'id' => "{$category}-{$key}",
                                            'autocomplete' => 'off',
                                            'data-real-value' => $value
                                        ]) ?>
                                        <button class="btn btn-outline-secondary toggle-obscured" type="button" data-target="<?= "{$category}-{$key}" ?>">
                                            Show
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <?= $this->Form->control("{$category}.{$key}", [
                                        'label' => $this->makeHumanReadable($key),
                                        'value' => $value,
                                        'class' => 'form-control' . ($value_type === 'numeric' ? ' is-numeric' : ''),
                                        'type' => $value_type === 'numeric' ? 'number' : 'text',
                                        'min' => $value_type === 'numeric' ? 0 : null,
                                        'step' => $value_type === 'numeric' ? 1 : null,
                                        'placeholder' => $value_type === 'numeric' ? __('Enter a number') : __('Enter text')
                                    ]) ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center">
                        <?= $this->Form->button(__('Save All Settings'), ['class' => 'btn btn-primary mt-3']) ?>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
(function() {
    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    ready(function() {
        console.log('DOM fully loaded and parsed');

        function toggleObscured(button) {
            console.log('Toggle obscured called');
            const targetId = button.getAttribute('data-target');
            const input = document.getElementById(targetId);

            if (input.classList.contains('obscured')) {
                // Show the real value
                input.value = input.getAttribute('data-real-value');
                input.classList.remove('obscured');
                button.textContent = 'Hide';
            } else {
                // Hide the value
                input.value = '•'.repeat(input.getAttribute('data-real-value').length);
                input.classList.add('obscured');
                button.textContent = 'Show';
            }
        }

        // Use event delegation for dynamically added elements
        document.body.addEventListener('click', function(event) {
            if (event.target.classList.contains('toggle-obscured')) {
                toggleObscured(event.target);
            }
        });

        // Ensure the real values are submitted
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                document.querySelectorAll('.obscured-field').forEach(function(input) {
                    if (input.classList.contains('obscured')) {
                        input.value = input.getAttribute('data-real-value');
                    }
                });
            });
        }

        // Log the number of toggle buttons found
        const toggleButtons = document.querySelectorAll('.toggle-obscured');
        console.log('Number of toggle buttons found:', toggleButtons.length);
    });
})();
<?php $this->Html->scriptEnd(); ?>