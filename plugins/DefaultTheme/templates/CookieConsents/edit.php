<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CookieConsent $cookieConsent
 */
?>
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title"><?= __('Cookie Preferences') ?></h5>
            </div>
            <div class="card-body">
                <?= $this->Form->create($cookieConsent, [
                    'class' => 'needs-validation',
                    'novalidate' => true
                ]) ?>
                <fieldset>
                <p class="mb-4"><?= __('Please select your cookie preferences below. Essential cookies are required for the website to function and cannot be disabled.') ?></p>

                <div class="mb-3">
                    <div class="form-check">
                        <?= $this->Form->checkbox('essential_consent', [
                            'class' => 'form-check-input' . ($this->Form->isFieldError('essential_consent') ? ' is-invalid' : ''),
                            'disabled' => true,
                            'checked' => true
                        ]) ?>
                        <label class="form-check-label" for="essential-consent">
                            <?= __('Essential Cookies') ?>
                        </label>
                        <div class="form-text">
                            <?= __('Required for the website to function properly. These cannot be disabled.') ?>
                        </div>
                        <?php if ($this->Form->isFieldError('essential_consent')): ?>
                            <div class="invalid-feedback">
                                <?= $this->Form->error('essential_consent') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <?= $this->Form->checkbox('functional_consent', [
                            'class' => 'form-check-input' . ($this->Form->isFieldError('functional_consent') ? ' is-invalid' : '')
                        ]) ?>
                        <label class="form-check-label" for="functional-consent">
                            <?= __('Functional Cookies') ?>
                        </label>
                        <div class="form-text">
                            <?= __('Enable enhanced functionality and personalization.') ?>
                        </div>
                        <?php if ($this->Form->isFieldError('functional_consent')): ?>
                            <div class="invalid-feedback">
                                <?= $this->Form->error('functional_consent') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <?= $this->Form->checkbox('analytics_consent', [
                            'class' => 'form-check-input' . ($this->Form->isFieldError('analytics_consent') ? ' is-invalid' : '')
                        ]) ?>
                        <label class="form-check-label" for="analytics-consent">
                            <?= __('Analytics Cookies') ?>
                        </label>
                        <div class="form-text">
                            <?= __('Help us understand how visitors interact with our website.') ?>
                        </div>
                        <?php if ($this->Form->isFieldError('analytics_consent')): ?>
                            <div class="invalid-feedback">
                                <?= $this->Form->error('analytics_consent') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <?= $this->Form->checkbox('marketing_consent', [
                            'class' => 'form-check-input' . ($this->Form->isFieldError('marketing_consent') ? ' is-invalid' : '')
                        ]) ?>
                        <label class="form-check-label" for="marketing-consent">
                            <?= __('Marketing Cookies') ?>
                        </label>
                        <div class="form-text">
                            <?= __('Used to deliver personalized advertisements.') ?>
                        </div>
                        <?php if ($this->Form->isFieldError('marketing_consent')): ?>
                            <div class="invalid-feedback">
                                <?= $this->Form->error('marketing_consent') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                </fieldset>
                </fieldset>
                <div class="form-group">
                    <?= $this->Form->button(__('Save Preferences'), ['class' => 'btn btn-primary']) ?>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>