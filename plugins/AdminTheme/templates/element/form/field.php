<?php
/**
 * Reusable form field element
 *
 * Renders a Bootstrap 5 styled form field with validation error handling.
 *
 * Usage:
 *   <?= $this->element('form/field', ['name' => 'email']) ?>
 *   <?= $this->element('form/field', ['name' => 'status', 'options' => $statuses]) ?>
 *   <?= $this->element('form/field', ['name' => 'is_active', 'type' => 'checkbox']) ?>
 *   <?= $this->element('form/field', ['name' => 'description', 'type' => 'textarea']) ?>
 *
 * Parameters:
 *   - name: (required) The field name
 *   - type: (optional) Override field type (text, textarea, checkbox, select, number, date, etc.)
 *   - options: (optional) Options array for select fields
 *   - empty: (optional) Empty option for select fields (default: false)
 *   - label: (optional) Custom label text
 *   - class: (optional) Additional CSS classes for the input
 *   - wrapperClass: (optional) Additional CSS classes for the wrapper div
 *   - inputOptions: (optional) Additional options to pass to Form->control()
 *   - noWrapper: (optional) If true, skip the mb-3 wrapper div
 */

$name = $name ?? '';
$type = $type ?? null;
$options = $options ?? null;
$empty = $empty ?? false;
$label = $label ?? null;
$additionalClass = $class ?? '';
$wrapperClass = $wrapperClass ?? 'mb-3';
$inputOptions = $inputOptions ?? [];
$noWrapper = $noWrapper ?? false;

if (empty($name)) {
    return;
}

$hasError = $this->Form->isFieldError($name);
$errorClass = $hasError ? ' is-invalid' : '';

// Determine the base class based on type or options
$isCheckbox = ($type === 'checkbox' || $type === 'boolean');
$isSelect = ($options !== null || $type === 'select');

if ($isCheckbox) {
    $baseClass = 'form-check-input';
} elseif ($isSelect) {
    $baseClass = 'form-select';
} else {
    $baseClass = 'form-control';
}

$fieldClass = $baseClass . $errorClass;
if (!empty($additionalClass)) {
    $fieldClass .= ' ' . $additionalClass;
}

// Build the control options
$controlOptions = array_merge([
    'class' => $fieldClass,
], $inputOptions);

if ($type !== null && !$isSelect) {
    $controlOptions['type'] = $type;
}

if ($label !== null) {
    $controlOptions['label'] = $label;
}

if ($isSelect && $options !== null) {
    $controlOptions['options'] = $options;
    if ($empty) {
        $controlOptions['empty'] = $empty === true ? '' : $empty;
    }
}
?>
<?php if ($isCheckbox): ?>
<?php if (!$noWrapper): ?><div class="<?= h($wrapperClass) ?>"><?php endif; ?>
    <div class="form-check">
        <?= $this->Form->checkbox($name, $controlOptions) ?>
        <label class="form-check-label" for="<?= h(str_replace(['_', '.'], '-', $name)) ?>">
            <?= $label ?? __(ucwords(str_replace('_', ' ', $name))) ?>
        </label>
        <?php if ($hasError): ?>
            <div class="invalid-feedback">
                <?= $this->Form->error($name) ?>
            </div>
        <?php endif; ?>
    </div>
<?php if (!$noWrapper): ?></div><?php endif; ?>
<?php else: ?>
<?php if (!$noWrapper): ?><div class="<?= h($wrapperClass) ?>"><?php endif; ?>
    <?= $this->Form->control($name, $controlOptions) ?>
    <?php if ($hasError): ?>
        <div class="invalid-feedback">
            <?= $this->Form->error($name) ?>
        </div>
    <?php endif; ?>
<?php if (!$noWrapper): ?></div><?php endif; ?>
<?php endif; ?>
