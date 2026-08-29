<?php

namespace Veldora\UI\Registry;

class ComponentRegistry
{
    /** @var array<string, array{description: string, usage: string, template: string}> */
    private array $components;

    public function __construct()
    {
        $this->components = $this->loadComponents();
    }

    public function has(string $name): bool
    {
        return isset($this->components[$name]);
    }

    /**
     * @return array{description: string, usage: string, template: string}|null
     */
    public function get(string $name): ?array
    {
        return $this->components[$name] ?? null;
    }

    /**
     * @return array<string, array{description: string, usage: string, template: string}>
     */
    public function all(): array
    {
        return $this->components;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Component definitions
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array<string, array{description: string, usage: string, template: string}>
     */
    private function loadComponents(): array
    {
        return [
            'button'      => $this->button(),
            'input'       => $this->input(),
            'textarea'    => $this->textarea(),
            'select'      => $this->select(),
            'checkbox'    => $this->checkbox(),
            'radio'       => $this->radio(),
            'badge'       => $this->badge(),
            'alert'       => $this->alert(),
            'card'        => $this->card(),
            'modal'       => $this->modal(),
            'spinner'     => $this->spinner(),
            'avatar'      => $this->avatar(),
            'dropdown'    => $this->dropdown(),
            'navbar'      => $this->navbar(),
            'toast'       => $this->toast(),
            'tabs'        => $this->tabs(),
            'accordion'   => $this->accordion(),
            'progress'    => $this->progress(),
            'tooltip'     => $this->tooltip(),
            'breadcrumb'  => $this->breadcrumb(),
            'table'       => $this->table(),
            // ── New components ──────────────────────────────────────────────
            'switch'      => $this->switch_(),
            'pagination'  => $this->pagination(),
            'skeleton'    => $this->skeleton(),
            'empty'       => $this->empty_(),
            'divider'     => $this->divider(),
            'drawer'      => $this->drawer(),
            'popover'     => $this->popover(),
            'confirm'     => $this->confirm(),
            'datepicker'  => $this->datepicker(),
            'fileupload'  => $this->fileupload(),
            'combobox'    => $this->combobox(),
            'inputgroup'  => $this->inputgroup(),
            'stat'        => $this->stat(),
            'datatable'   => $this->datatable(),
            'timeline'    => $this->timeline(),
            'stepper'     => $this->stepper(),
            'sidebar'     => $this->sidebar(),
            'container'   => $this->container(),
            // ── New in 0.5.0 ───────────────────────────────────────────────
            'footer'      => $this->footer(),
            'rating'      => $this->rating(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function button(): array
    {
        return [
            'description' => 'Clickable button — variant, size, disabled, outline, skeuomorphic, flat, neumorphic, and glass support',
            'usage'       => '<x-button variant="primary" size="md">Click me</x-button>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Button Component
// Props: variant (primary|secondary|ghost|danger|success|warning|skeuomorphic|flat|neumorphic|glass|outline-primary|outline-secondary|outline-danger), size (sm|md|lg), disabled (bool), type (button|submit|reset)
$variant  = $variant  ?? 'primary';
$size     = $size     ?? 'md';
$disabled = $disabled ?? false;
$type     = $type     ?? 'button';

$variants = [
    'primary'           => 'vui-btn vui-btn-primary',
    'secondary'         => 'vui-btn vui-btn-secondary',
    'ghost'             => 'vui-btn vui-btn-ghost',
    'danger'            => 'vui-btn vui-btn-danger',
    'success'           => 'vui-btn vui-btn-success',
    'warning'           => 'vui-btn vui-btn-warning',
    'outline-primary'   => 'vui-btn vui-btn-outline-primary',
    'outline-secondary' => 'vui-btn vui-btn-outline-secondary',
    'outline-danger'    => 'vui-btn vui-btn-outline-danger',
    'skeuomorphic'      => 'vui-btn-skeuo',
    'skeuo'             => 'vui-btn-skeuo',
    'skeuo-neutral'     => 'vui-btn-skeuo vui-btn-skeuo-neutral',
    'skeuo-danger'      => 'vui-btn-skeuo vui-btn-skeuo-danger',
    'flat'              => 'vui-btn-flat',
    'flat-outline'      => 'vui-btn-flat vui-btn-flat-outline',
    'flat-neutral'      => 'vui-btn-flat vui-btn-flat-neutral',
    'neumorphic'        => 'vui-btn-neumorphic',
    'soft'              => 'vui-btn-neumorphic',
    'neumorphic-accent' => 'vui-btn-neumorphic vui-btn-neumorphic-accent',
    'glass'             => 'vui-btn-glass',
    'glass-neutral'     => 'vui-btn-glass vui-btn-glass-neutral',
];
$sizes = [
    'sm' => 'vui-btn-sm',
    'md' => 'vui-btn-md',
    'lg' => 'vui-btn-lg',
];

$baseClass = $variants[$variant] ?? ('vui-btn vui-btn-' . $variant);
$sizeClass = ($sizes[$size] ?? $sizes['md']);
$classes   = str_contains($baseClass, 'vui-btn-skeuo') || str_contains($baseClass, 'vui-btn-neumorphic') || str_contains($baseClass, 'vui-btn-glass') || str_contains($baseClass, 'vui-btn-flat')
    ? $baseClass
    : ($baseClass . ' ' . $sizeClass);

$disabledAttr = $disabled ? 'disabled aria-disabled="true"' : '';
?>
<button type="<?= htmlspecialchars($type) ?>" class="<?= $classes ?>" <?= $disabledAttr ?>>
    <?= $slot ?>
</button>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function input(): array
    {
        return [
            'description' => 'Text input with label, error, and helper text support',
            'usage'       => '<x-input name="email" label="Email Address" type="email" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Input Component
// Props: name, label, type, value, placeholder, error, helper, required, disabled, id
$type        = $type        ?? 'text';
$value       = $value       ?? '';
$placeholder = $placeholder ?? '';
$required    = $required    ?? false;
$disabled    = $disabled    ?? false;
$error       = $error       ?? null;
$helper      = $helper      ?? null;
$id          = $id          ?? ($name ?? 'input_' . uniqid());
$label       = $label       ?? null;

$inputClass  = 'vui-input' . ($error ? ' vui-input-error' : '');
$requiredAttr = $required ? 'required' : '';
$disabledAttr = $disabled ? 'disabled' : '';
?>
<div class="vui-field">
    <?php if ($label): ?>
        <label class="vui-label" for="<?= htmlspecialchars($id) ?>">
            <?= htmlspecialchars($label) ?>
            <?php if ($required): ?><span class="vui-required" aria-hidden="true">*</span><?php endif; ?>
        </label>
    <?php endif; ?>

    <input
        id="<?= htmlspecialchars($id) ?>"
        type="<?= htmlspecialchars($type) ?>"
        name="<?= htmlspecialchars($name ?? '') ?>"
        value="<?= htmlspecialchars($value) ?>"
        placeholder="<?= htmlspecialchars($placeholder) ?>"
        class="<?= $inputClass ?>"
        <?= $requiredAttr ?> <?= $disabledAttr ?>
        <?php if ($error): ?>aria-invalid="true" aria-describedby="<?= $id ?>-error"<?php endif; ?>
    >

    <?php if ($error): ?>
        <p id="<?= $id ?>-error" class="vui-field-error" role="alert"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($helper): ?>
        <p class="vui-field-helper"><?= htmlspecialchars($helper) ?></p>
    <?php endif; ?>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function textarea(): array
    {
        return [
            'description' => 'Multi-line text area with label and error support',
            'usage'       => '<x-textarea name="bio" label="About You" rows="4" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Textarea Component
// Props: name, label, rows, placeholder, error, helper, required, disabled, id
$rows        = $rows        ?? 4;
$placeholder = $placeholder ?? '';
$required    = $required    ?? false;
$disabled    = $disabled    ?? false;
$error       = $error       ?? null;
$helper      = $helper      ?? null;
$id          = $id          ?? ($name ?? 'textarea_' . uniqid());
$label       = $label       ?? null;
$content     = $slot        ?? '';

$areaClass    = 'vui-textarea' . ($error ? ' vui-input-error' : '');
$requiredAttr = $required ? 'required' : '';
$disabledAttr = $disabled ? 'disabled' : '';
?>
<div class="vui-field">
    <?php if ($label): ?>
        <label class="vui-label" for="<?= htmlspecialchars($id) ?>">
            <?= htmlspecialchars($label) ?>
            <?php if ($required): ?><span class="vui-required" aria-hidden="true">*</span><?php endif; ?>
        </label>
    <?php endif; ?>

    <textarea
        id="<?= htmlspecialchars($id) ?>"
        name="<?= htmlspecialchars($name ?? '') ?>"
        rows="<?= (int) $rows ?>"
        placeholder="<?= htmlspecialchars($placeholder) ?>"
        class="<?= $areaClass ?>"
        <?= $requiredAttr ?> <?= $disabledAttr ?>
        <?php if ($error): ?>aria-invalid="true" aria-describedby="<?= $id ?>-error"<?php endif; ?>
    ><?= htmlspecialchars($content) ?></textarea>

    <?php if ($error): ?>
        <p id="<?= $id ?>-error" class="vui-field-error" role="alert"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($helper): ?>
        <p class="vui-field-helper"><?= htmlspecialchars($helper) ?></p>
    <?php endif; ?>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function select(): array
    {
        return [
            'description' => 'Dropdown select with label, options array, and error',
            'usage'       => '<x-select name="role" label="Role" :options="$roles" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Select Component
// Props: name, label, options (assoc or indexed), selected, placeholder, error, required, disabled, id
$options     = $options     ?? [];
$selected    = $selected    ?? '';
$placeholder = $placeholder ?? 'Select an option';
$required    = $required    ?? false;
$disabled    = $disabled    ?? false;
$error       = $error       ?? null;
$id          = $id          ?? ($name ?? 'select_' . uniqid());
$label       = $label       ?? null;

$selectClass  = 'vui-select' . ($error ? ' vui-input-error' : '');
$requiredAttr = $required ? 'required' : '';
$disabledAttr = $disabled ? 'disabled' : '';
?>
<div class="vui-field">
    <?php if ($label): ?>
        <label class="vui-label" for="<?= htmlspecialchars($id) ?>">
            <?= htmlspecialchars($label) ?>
            <?php if ($required): ?><span class="vui-required" aria-hidden="true">*</span><?php endif; ?>
        </label>
    <?php endif; ?>

    <select
        id="<?= htmlspecialchars($id) ?>"
        name="<?= htmlspecialchars($name ?? '') ?>"
        class="<?= $selectClass ?>"
        <?= $requiredAttr ?> <?= $disabledAttr ?>
        <?php if ($error): ?>aria-invalid="true"<?php endif; ?>
    >
        <?php if ($placeholder): ?>
            <option value="" disabled <?= $selected === '' ? 'selected' : '' ?>><?= htmlspecialchars($placeholder) ?></option>
        <?php endif; ?>
        <?php foreach ($options as $val => $label): ?>
            <option value="<?= htmlspecialchars((string) $val) ?>" <?= (string) $val === (string) $selected ? 'selected' : '' ?>>
                <?= htmlspecialchars((string) $label) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if ($error): ?>
        <p class="vui-field-error" role="alert"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function checkbox(): array
    {
        return [
            'description' => 'Checkbox input supporting standard, skeuomorphic 3D, flat minimal 2D, and neumorphic soft UI',
            'usage'       => '<x-checkbox variant="skeuomorphic" name="notify" label="Enable notifications" :checked="true" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Checkbox Component
// Props: name, label, value, checked, disabled, error, id, variant (default|skeuomorphic|flat|neumorphic)
$value    = $value    ?? '1';
$checked  = !empty($checked);
$disabled = !empty($disabled);
$error    = $error    ?? null;
$id       = $id       ?? ($name ?? 'checkbox_' . uniqid());
$label    = $label    ?? null;
$variant  = $variant  ?? 'default';

$checkedAttr  = $checked  ? 'checked'   : '';
$disabledAttr = $disabled ? 'disabled'  : '';

$variantClassMap = [
    'skeuomorphic' => 'vui-checkbox-custom vui-checkbox-skeuo',
    'skeuo'        => 'vui-checkbox-custom vui-checkbox-skeuo',
    'flat'         => 'vui-checkbox-custom vui-checkbox-flat',
    'neumorphic'   => 'vui-checkbox-custom vui-checkbox-neumorphic',
    'soft'         => 'vui-checkbox-custom vui-checkbox-neumorphic',
];
$isCustom = isset($variantClassMap[$variant]);
?>
<?php if ($isCustom): ?>
    <label class="<?= $variantClassMap[$variant] ?> <?= $disabled ? 'vui-checkbox-disabled' : '' ?>" for="<?= htmlspecialchars($id) ?>">
        <input
            type="checkbox"
            id="<?= htmlspecialchars($id) ?>"
            name="<?= htmlspecialchars($name ?? '') ?>"
            value="<?= htmlspecialchars($value) ?>"
            <?= $checkedAttr ?> <?= $disabledAttr ?>
        >
        <span class="vui-checkbox-box">
            <svg class="vui-checkbox-check" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </span>
        <?php if ($label): ?>
            <span class="vui-checkbox-label"><?= htmlspecialchars($label) ?></span>
        <?php endif; ?>
    </label>
<?php else: ?>
    <div class="vui-checkbox-wrap">
        <input
            type="checkbox"
            id="<?= htmlspecialchars($id) ?>"
            name="<?= htmlspecialchars($name ?? '') ?>"
            value="<?= htmlspecialchars($value) ?>"
            class="vui-checkbox"
            <?= $checkedAttr ?> <?= $disabledAttr ?>
        >
        <?php if ($label): ?>
            <label class="vui-checkbox-label" for="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($label) ?></label>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="vui-field-error" role="alert"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>
TEMPLATE,
        ];
    }


    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function radio(): array
    {
        return [
            'description' => 'Radio input supporting multiple aesthetics: standard, skeuomorphic 3D, flat minimal 2D, and neumorphic soft UI',
            'usage'       => '<x-radio variant="skeuomorphic" name="plan" value="pro" label="Pro Plan" :checked="true" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Radio Component
// Props: name, label, value, checked, disabled, id, variant (default|skeuomorphic|flat|neumorphic)
$checked  = !empty($checked);
$disabled = !empty($disabled);
$id       = $id       ?? ($name ?? 'radio') . '_' . ($value ?? uniqid());
$label    = $label    ?? null;
$variant  = $variant  ?? 'default';

$checkedAttr  = $checked  ? 'checked'  : '';
$disabledAttr = $disabled ? 'disabled' : '';

$variantClassMap = [
    'skeuomorphic' => 'vui-radio-custom vui-radio-skeuo',
    'skeuo'        => 'vui-radio-custom vui-radio-skeuo',
    'flat'         => 'vui-radio-custom vui-radio-flat',
    'neumorphic'   => 'vui-radio-custom vui-radio-neumorphic',
    'soft'         => 'vui-radio-custom vui-radio-neumorphic',
];
$isCustom = isset($variantClassMap[$variant]);
?>
<?php if ($isCustom): ?>
    <label class="<?= $variantClassMap[$variant] ?> <?= $disabled ? 'vui-radio-disabled' : '' ?>" for="<?= htmlspecialchars($id) ?>">
        <input
            type="radio"
            id="<?= htmlspecialchars($id) ?>"
            name="<?= htmlspecialchars($name ?? '') ?>"
            value="<?= htmlspecialchars($value ?? '') ?>"
            <?= $checkedAttr ?> <?= $disabledAttr ?>
        >
        <span class="vui-radio-disc"><span class="vui-radio-dot"></span></span>
        <?php if ($label): ?>
            <span class="vui-radio-label"><?= htmlspecialchars($label) ?></span>
        <?php endif; ?>
    </label>
<?php else: ?>
    <div class="vui-radio-wrap">
        <input
            type="radio"
            id="<?= htmlspecialchars($id) ?>"
            name="<?= htmlspecialchars($name ?? '') ?>"
            value="<?= htmlspecialchars($value ?? '') ?>"
            class="vui-radio"
            <?= $checkedAttr ?> <?= $disabledAttr ?>
        >
        <?php if ($label): ?>
            <label class="vui-radio-label" for="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($label) ?></label>
        <?php endif; ?>
    </div>
<?php endif; ?>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function badge(): array
    {
        return [
            'description' => 'Inline status badge — variant and dot indicator support',
            'usage'       => '<x-badge variant="success">Active</x-badge>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Badge Component
// Props: variant (default|success|warning|danger|info|purple), dot (bool)
$variant = $variant ?? 'default';
$dot     = $dot     ?? false;

$variants = [
    'default' => 'vui-badge vui-badge-default',
    'success' => 'vui-badge vui-badge-success',
    'warning' => 'vui-badge vui-badge-warning',
    'danger'  => 'vui-badge vui-badge-danger',
    'info'    => 'vui-badge vui-badge-info',
    'purple'  => 'vui-badge vui-badge-purple',
];
$class = $variants[$variant] ?? $variants['default'];
?>
<span class="<?= $class ?>">
    <?php if ($dot): ?><span class="vui-badge-dot" aria-hidden="true"></span><?php endif; ?>
    <?= $slot ?>
</span>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function alert(): array
    {
        return [
            'description' => 'Alert box — success, warning, danger, info variants',
            'usage'       => '<x-alert variant="success" title="Done!">Record saved.</x-alert>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Alert Component
// Props: variant (success|warning|danger|info), title, dismissible
$variant     = $variant     ?? 'info';
$title       = $title       ?? null;
$dismissible = $dismissible ?? false;

$icons = [
    'success' => '✓',
    'warning' => '⚠',
    'danger'  => '✕',
    'info'    => 'ℹ',
];

$variants = [
    'success' => 'vui-alert vui-alert-success',
    'warning' => 'vui-alert vui-alert-warning',
    'danger'  => 'vui-alert vui-alert-danger',
    'info'    => 'vui-alert vui-alert-info',
];

$class = $variants[$variant] ?? $variants['info'];
$icon  = $icons[$variant]    ?? $icons['info'];
?>
<div class="<?= $class ?>" role="alert">
    <span class="vui-alert-icon" aria-hidden="true"><?= $icon ?></span>
    <div class="vui-alert-body">
        <?php if ($title): ?>
            <p class="vui-alert-title"><?= htmlspecialchars($title) ?></p>
        <?php endif; ?>
        <p class="vui-alert-message"><?= $slot ?></p>
    </div>
    <?php if ($dismissible): ?>
        <button type="button" class="vui-alert-close" onclick="this.closest('.vui-alert').remove()" aria-label="Dismiss">✕</button>
    <?php endif; ?>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function card(): array
    {
        return [
            'description' => 'Content card with optional header, footer, and padding',
            'usage'       => '<x-card title="My Card"><p>Content here</p></x-card>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Card Component
// Props: title, subtitle, padding (bool, default true)
$title    = $title    ?? null;
$subtitle = $subtitle ?? null;
$padding  = $padding  ?? true;
$class    = 'vui-card' . (!$padding ? ' vui-card-flush' : '');
?>
<div class="<?= $class ?>">
    <?php if ($title || $subtitle): ?>
        <div class="vui-card-header">
            <?php if ($title): ?>
                <h3 class="vui-card-title"><?= htmlspecialchars($title) ?></h3>
            <?php endif; ?>
            <?php if ($subtitle): ?>
                <p class="vui-card-subtitle"><?= htmlspecialchars($subtitle) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="vui-card-body">
        <?= $slot ?>
    </div>

    <?php if (isset($footer)): ?>
        <div class="vui-card-footer"><?= $footer ?></div>
    <?php endif; ?>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function modal(): array
    {
        return [
            'description' => 'Accessible dialog modal with open/close JS',
            'usage'       => '<x-modal id="confirm-modal" title="Confirm Action">...</x-modal>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Modal Component
// Props: id (required), title, size (sm|md|lg|xl)
$id    = $id    ?? 'vui-modal-' . uniqid();
$title = $title ?? null;
$size  = $size  ?? 'md';

$sizes = [
    'sm' => 'vui-modal-sm',
    'md' => 'vui-modal-md',
    'lg' => 'vui-modal-lg',
    'xl' => 'vui-modal-xl',
];
$sizeClass = $sizes[$size] ?? $sizes['md'];
?>
<div id="<?= htmlspecialchars($id) ?>" class="vui-modal-overlay" role="dialog" aria-modal="true" aria-hidden="true" <?php if ($title): ?>aria-labelledby="<?= $id ?>-title"<?php endif; ?>>
    <div class="vui-modal-container <?= $sizeClass ?>">
        <div class="vui-modal-header">
            <?php if ($title): ?>
                <h2 id="<?= $id ?>-title" class="vui-modal-title"><?= htmlspecialchars($title) ?></h2>
            <?php endif; ?>
            <button type="button" class="vui-modal-close" onclick="document.getElementById('<?= $id ?>').setAttribute('aria-hidden','true')" aria-label="Close" style="display:inline-flex;align-items:center;justify-content:center;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="vui-modal-body">
            <?= $slot ?>
        </div>
        <?php if (isset($footer)): ?>
            <div class="vui-modal-footer"><?= $footer ?></div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('<?= $id ?>');
    if (!modal) return;
    // Close on overlay click
    modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.setAttribute('aria-hidden', 'true');
    });
    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') modal.setAttribute('aria-hidden', 'true');
    });
})();
</script>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function spinner(): array
    {
        return [
            'description' => 'Animated loading spinner with 12 pure CSS variants and sizes',
            'usage'       => '<x-spinner variant="dual-ring" size="md" label="Loading..." />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Spinner Component
// Props: variant (classic|dual-ring|bounce-dots|pulse|ring-pulse|wave-bars|dot-grid|spinning-bars|orbit), size (sm|md|lg), label, color
$variant = $variant ?? 'classic';
$size    = $size    ?? 'md';
$label   = $label   ?? 'Loading...';
$color   = $color   ?? null;
$styleAttr = $color ? 'style="--vui-spinner-color:' . htmlspecialchars($color) . '"' : '';

$sizes = ['sm' => 'vui-spinner-sm', 'md' => 'vui-spinner-md', 'lg' => 'vui-spinner-lg'];
$sizeClass = $sizes[$size] ?? $sizes['md'];
?>
<?php if ($variant === 'dual-ring'): ?>
<span class="vui-spinner-dual <?= $sizeClass ?>" role="status" aria-label="<?= htmlspecialchars($label) ?>" <?= $styleAttr ?>>
    <span></span><span></span>
    <span class="vui-sr-only"><?= htmlspecialchars($label) ?></span>
</span>
<?php elseif ($variant === 'bounce-dots'): ?>
<span class="vui-spinner-bounce <?= $sizeClass ?>" role="status" aria-label="<?= htmlspecialchars($label) ?>" <?= $styleAttr ?>>
    <span></span><span></span><span></span>
    <span class="vui-sr-only"><?= htmlspecialchars($label) ?></span>
</span>
<?php elseif ($variant === 'pulse'): ?>
<span class="vui-spinner-pulse <?= $sizeClass ?>" role="status" aria-label="<?= htmlspecialchars($label) ?>" <?= $styleAttr ?>>
    <span></span>
    <span class="vui-sr-only"><?= htmlspecialchars($label) ?></span>
</span>
<?php elseif ($variant === 'ring-pulse'): ?>
<span class="vui-spinner-ring-pulse <?= $sizeClass ?>" role="status" aria-label="<?= htmlspecialchars($label) ?>" <?= $styleAttr ?>>
    <span></span>
    <span class="vui-sr-only"><?= htmlspecialchars($label) ?></span>
</span>
<?php elseif ($variant === 'wave-bars'): ?>
<span class="vui-spinner-wave <?= $sizeClass ?>" role="status" aria-label="<?= htmlspecialchars($label) ?>" <?= $styleAttr ?>>
    <span></span><span></span><span></span><span></span><span></span>
    <span class="vui-sr-only"><?= htmlspecialchars($label) ?></span>
</span>
<?php elseif ($variant === 'dot-grid'): ?>
<span class="vui-spinner-dot-grid <?= $sizeClass ?>" role="status" aria-label="<?= htmlspecialchars($label) ?>" <?= $styleAttr ?>>
    <span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span>
    <span class="vui-sr-only"><?= htmlspecialchars($label) ?></span>
</span>
<?php elseif ($variant === 'spinning-bars'): ?>
<span class="vui-spinner-bars <?= $sizeClass ?>" role="status" aria-label="<?= htmlspecialchars($label) ?>" <?= $styleAttr ?>>
    <span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span>
    <span class="vui-sr-only"><?= htmlspecialchars($label) ?></span>
</span>
<?php elseif ($variant === 'orbit'): ?>
<span class="vui-spinner-orbit <?= $sizeClass ?>" role="status" aria-label="<?= htmlspecialchars($label) ?>" <?= $styleAttr ?>>
    <span class="orb-core"></span><span class="orb-satellite"></span>
    <span class="vui-sr-only"><?= htmlspecialchars($label) ?></span>
</span>
<?php else: ?>
<span class="vui-spinner <?= $sizeClass ?>" role="status" aria-label="<?= htmlspecialchars($label) ?>" <?= $styleAttr ?>>
    <span class="vui-spinner-ring"></span>
    <span class="vui-sr-only"><?= htmlspecialchars($label) ?></span>
</span>
<?php endif; ?>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function avatar(): array
    {
        return [
            'description' => 'User avatar — image or initials fallback, size variants',
            'usage'       => '<x-avatar src="/img/user.jpg" name="Jane Doe" size="md" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Avatar Component
// Props: src, name, size (xs|sm|md|lg|xl), shape (circle|square)
$src   = $src   ?? null;
$name  = $name  ?? '';
$size  = $size  ?? 'md';
$shape = $shape ?? 'circle';

$sizes  = ['xs' => 'vui-avatar-xs', 'sm' => 'vui-avatar-sm', 'md' => 'vui-avatar-md', 'lg' => 'vui-avatar-lg', 'xl' => 'vui-avatar-xl'];
$shapes = ['circle' => 'vui-avatar-circle', 'square' => 'vui-avatar-square'];
$class  = 'vui-avatar ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($shapes[$shape] ?? $shapes['circle']);

// Generate initials from name
$initials = '';
if ($name) {
    $parts = explode(' ', trim($name));
    $initials = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) $initials .= strtoupper(substr(end($parts), 0, 1));
}
?>
<span class="<?= $class ?>" aria-label="<?= htmlspecialchars($name) ?>">
    <?php if ($src): ?>
        <img src="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($name) ?>" class="vui-avatar-img">
    <?php else: ?>
        <span class="vui-avatar-initials" aria-hidden="true"><?= htmlspecialchars($initials) ?></span>
    <?php endif; ?>
</span>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function dropdown(): array
    {
        return [
            'description' => 'Click-triggered dropdown menu with item slot',
            'usage'       => '<x-dropdown label="Options">...</x-dropdown>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Dropdown Component
// Props: label, align (left|right), id
$label = $label ?? 'Options';
$align = $align ?? 'left';
$id    = $id    ?? 'vui-dropdown-' . uniqid();

$menuClass = 'vui-dropdown-menu' . ($align === 'right' ? ' vui-dropdown-right' : '');
?>
<div class="vui-dropdown" id="<?= htmlspecialchars($id) ?>">
    <button
        type="button"
        class="vui-dropdown-trigger"
        aria-haspopup="true"
        aria-expanded="false"
        onclick="(function(el){var open=el.getAttribute('aria-expanded')==='true';el.setAttribute('aria-expanded',!open);el.nextElementSibling.classList.toggle('vui-dropdown-open',!open);})(this)"
    >
        <?= htmlspecialchars($label) ?>
        <span class="vui-dropdown-caret" aria-hidden="true" style="display:inline-flex;align-items:center;">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
        </span>
    </button>
    <ul class="<?= $menuClass ?>" role="menu">
        <?= $slot ?>
    </ul>
</div>

<script>
document.addEventListener('click', function(e) {
    var d = document.getElementById('<?= $id ?>');
    if (d && !d.contains(e.target)) {
        var btn = d.querySelector('.vui-dropdown-trigger');
        var menu = d.querySelector('.vui-dropdown-menu');
        if (btn) btn.setAttribute('aria-expanded', 'false');
        if (menu) menu.classList.remove('vui-dropdown-open');
    }
});
</script>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function navbar(): array
    {
        return [
            'description' => 'Responsive top navigation bar with brand and slot',
            'usage'       => '<x-navbar brand="Veldora">...</x-navbar>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Navbar Component
// Props: brand (text or HTML), brandHref, sticky (bool)
$brand     = $brand     ?? '';
$brandHref = $brandHref ?? '/';
$sticky    = $sticky    ?? false;
$class     = 'vui-navbar' . ($sticky ? ' vui-navbar-sticky' : '');
$navId     = 'vui-nav-' . substr(md5($brand), 0, 6);
?>
<nav class="<?= $class ?>" role="navigation" aria-label="Main navigation">
    <div class="vui-navbar-inner">
        <a href="<?= htmlspecialchars($brandHref) ?>" class="vui-navbar-brand">
            <?= $brand ?>
        </a>

        <button
            type="button"
            class="vui-navbar-toggle"
            aria-controls="<?= $navId ?>"
            aria-expanded="false"
            onclick="(function(btn){var open=btn.getAttribute('aria-expanded')==='true';btn.setAttribute('aria-expanded',!open);btn.classList.toggle('vui-toggle-active',!open);document.getElementById('<?= $navId ?>').classList.toggle('vui-navbar-open',!open);})(this)"
            aria-label="Toggle navigation"
        >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>

        <div id="<?= $navId ?>" class="vui-navbar-menu">
            <?= $slot ?>
        </div>
    </div>
</nav>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function toast(): array
    {
        return [
            'description' => 'Auto-dismissing toast notification, JS-driven',
            'usage'       => '<x-toast id="save-toast" variant="success" message="Saved!" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Toast Component
// Props: id, variant (success|warning|danger|info), message, duration (ms, default 3500)
$id       = $id       ?? 'vui-toast-' . uniqid();
$variant  = $variant  ?? 'info';
$message  = $message  ?? ($slot ?? '');
$duration = $duration ?? 3500;

$icons = [
    'success' => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
    'warning' => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    'danger'  => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
    'info'    => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
];
$variants = ['success' => 'vui-toast-success', 'warning' => 'vui-toast-warning', 'danger' => 'vui-toast-danger', 'info' => 'vui-toast-info'];
$class    = 'vui-toast ' . ($variants[$variant] ?? $variants['info']);
$icon     = $icons[$variant] ?? $icons['info'];
?>
<div id="<?= htmlspecialchars($id) ?>" class="<?= $class ?>" role="status" aria-live="polite" aria-atomic="true">
    <span class="vui-toast-icon" aria-hidden="true"><?= $icon ?></span>
    <span class="vui-toast-message"><?= htmlspecialchars($message) ?></span>
    <button type="button" class="vui-toast-close" onclick="document.getElementById('<?= $id ?>').remove()" aria-label="Dismiss" style="display:inline-flex;align-items:center;justify-content:center;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
</div>

<script>
(function () {
    var el = document.getElementById('<?= htmlspecialchars($id) ?>');
    if (!el) return;
    setTimeout(function () {
        el.classList.add('vui-toast-fade');
        setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 400);
    }, <?= (int) $duration ?>);
})();
</script>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function tabs(): array
    {
        return [
            'description' => 'Interactive tabbed navigation with animated active pills and content panels',
            'usage'       => '<x-tabs id="profile-tabs" :tabs="[\'account\' => \'Account\', \'security\' => \'Security\']"> ... </x-tabs>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Tabs Component
// Props: id, tabs (array: key => Label), active (default first key)
$tabId  = $id ?? 'vui-tabs-' . uniqid();
$tabs   = $tabs ?? [];
$active = $active ?? (!empty($tabs) ? array_key_first($tabs) : '');
?>
<div id="<?= htmlspecialchars($tabId) ?>" class="vui-tabs-container">
    <div class="vui-tabs-list" role="tablist" aria-label="Tabs navigation">
        <?php foreach ($tabs as $key => $label): ?>
            <?php $isActive = ($key === $active); ?>
            <button
                type="button"
                role="tab"
                class="vui-tab-btn <?= $isActive ? 'vui-tab-active' : '' ?>"
                id="tab-btn-<?= htmlspecialchars($tabId . '-' . $key) ?>"
                aria-controls="tab-pane-<?= htmlspecialchars($tabId . '-' . $key) ?>"
                aria-selected="<?= $isActive ? 'true' : 'false' ?>"
                onclick="(function(btn){
                    var root = document.getElementById('<?= htmlspecialchars($tabId) ?>');
                    root.querySelectorAll('.vui-tab-btn').forEach(function(b){ b.classList.remove('vui-tab-active'); b.setAttribute('aria-selected','false'); });
                    root.querySelectorAll('.vui-tab-pane').forEach(function(p){ p.classList.remove('vui-tab-pane-active'); });
                    btn.classList.add('vui-tab-active');
                    btn.setAttribute('aria-selected','true');
                    var target = document.getElementById('tab-pane-<?= htmlspecialchars($tabId . '-') ?>' + '<?= htmlspecialchars($key) ?>');
                    if(target) target.classList.add('vui-tab-pane-active');
                })(this)"
            >
                <?= htmlspecialchars($label) ?>
            </button>
        <?php endforeach; ?>
    </div>
    <div class="vui-tabs-content">
        <?= $slot ?? '' ?>
    </div>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function accordion(): array
    {
        return [
            'description' => 'Smooth collapsible accordion / disclosure card with chevron toggle',
            'usage'       => '<x-accordion title="What is Veldora?" :open="true"> ... </x-accordion>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Accordion Component
// Props: id, title, open (bool, default false)
$accId  = $id ?? 'vui-acc-' . uniqid();
$title  = $title ?? 'Accordion Title';
$isOpen = !empty($open);
?>
<div id="<?= htmlspecialchars($accId) ?>" class="vui-accordion <?= $isOpen ? 'vui-accordion-open' : '' ?>">
    <button
        type="button"
        class="vui-accordion-header"
        aria-expanded="<?= $isOpen ? 'true' : 'false' ?>"
        onclick="(function(btn){
            var item = document.getElementById('<?= htmlspecialchars($accId) ?>');
            var open = item.classList.toggle('vui-accordion-open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        })(this)"
    >
        <span class="vui-accordion-title"><?= htmlspecialchars($title) ?></span>
        <svg class="vui-accordion-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </button>
    <div class="vui-accordion-body" role="region">
        <div class="vui-accordion-inner">
            <?= $slot ?? '' ?>
        </div>
    </div>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function progress(): array
    {
        return [
            'description' => 'Visual progress bar with color variants, striped gradient, and percentage label',
            'usage'       => '<x-progress :value="75" variant="primary" :striped="true" :showLabel="true" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Progress Component
// Props: value (0-100), max (default 100), variant (primary|success|warning|danger), size (sm|md|lg), striped (bool), animated (bool), showLabel (bool)
$val       = (int) ($value ?? 0);
$maxVal    = (int) ($max ?? 100);
$pct       = $maxVal > 0 ? min(100, max(0, round(($val / $maxVal) * 100))) : 0;
$variant   = $variant ?? 'primary';
$size      = $size ?? 'md';
$isStriped = !empty($striped);
$isAnim    = !empty($animated);
$showLbl   = !empty($showLabel);

$barClasses = 'vui-progress-bar vui-progress-' . htmlspecialchars($variant);
if ($isStriped) $barClasses .= ' vui-progress-striped';
if ($isAnim)    $barClasses .= ' vui-progress-animated';
?>
<div class="vui-progress vui-progress-<?= htmlspecialchars($size) ?>" role="progressbar" aria-valuenow="<?= $val ?>" aria-valuemin="0" aria-valuemax="<?= $maxVal ?>" aria-label="<?= $showLbl ? $pct . '%' : 'Progress bar' ?>">
    <div class="<?= $barClasses ?>" style="width: <?= $pct ?>%;">
        <?php if ($showLbl): ?>
            <span class="vui-progress-label"><?= $pct ?>%</span>
        <?php endif; ?>
    </div>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function tooltip(): array
    {
        return [
            'description' => 'Contextual hover tooltip bubble with smooth arrow positioning',
            'usage'       => '<x-tooltip text="Copied to clipboard!" position="top"><x-button>Hover me</x-button></x-tooltip>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Tooltip Component
// Props: text, position (top|bottom|left|right, default top)
$text = $text ?? '';
$pos  = $position ?? 'top';
?>
<span class="vui-tooltip-wrapper vui-tooltip-<?= htmlspecialchars($pos) ?>">
    <?= $slot ?? '' ?>
    <span class="vui-tooltip-bubble" role="tooltip" aria-hidden="true">
        <?= htmlspecialchars($text) ?>
    </span>
</span>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function breadcrumb(): array
    {
        return [
            'description' => 'Navigation trail breadcrumb with SVG chevron separators',
            'usage'       => '<x-breadcrumb :items="[[\'label\' => \'Home\', \'href\' => \'/\'], [\'label\' => \'Docs\', \'href\' => \'/docs\'], [\'label\' => \'Buttons\']]" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Breadcrumb Component
// Props: items (array of ['label' => string, 'href' => ?string])
$items = $items ?? [];
?>
<nav class="vui-breadcrumb" aria-label="Breadcrumb">
    <ol class="vui-breadcrumb-list">
        <?php foreach ($items as $idx => $item): ?>
            <?php $isLast = ($idx === count($items) - 1); ?>
            <li class="vui-breadcrumb-item <?= $isLast ? 'vui-breadcrumb-active' : '' ?>">
                <?php if (!$isLast && !empty($item['href'])): ?>
                    <a href="<?= htmlspecialchars($item['href']) ?>" class="vui-breadcrumb-link"><?= htmlspecialchars($item['label']) ?></a>
                    <svg class="vui-breadcrumb-sep" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                <?php else: ?>
                    <span aria-current="page"><?= htmlspecialchars($item['label']) ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function table(): array
    {
        return [
            'description' => 'Modern responsive data table with zebra striping, border styling, and hover elevation',
            'usage'       => '<x-table :striped="true" :hover="true"> <thead>...</thead> <tbody>...</tbody> </x-table>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Table Component
// Props: striped (bool), hover (bool), bordered (bool), compact (bool)
$isStriped  = !empty($striped);
$isHover    = !empty($hover);
$isBordered = !empty($bordered);
$isCompact  = !empty($compact);

$tableClasses = 'vui-table';
if ($isStriped)  $tableClasses .= ' vui-table-striped';
if ($isHover)    $tableClasses .= ' vui-table-hover';
if ($isBordered) $tableClasses .= ' vui-table-bordered';
if ($isCompact)  $tableClasses .= ' vui-table-compact';
?>
<div class="vui-table-responsive">
    <table class="<?= $tableClasses ?>">
        <?= $slot ?? '' ?>
    </table>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{description: string, usage: string, template: string} */
    private function switch_(): array
    {
        return [
            'description' => 'Toggle switch supporting standard pill, skeuomorphic 3D embossed, flat 2D, and neumorphic soft UI',
            'usage'       => '<x-switch variant="skeuomorphic" name="notifications" label="Enable notifications" :checked="true" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Switch Component
// Props: name, id, label, checked (bool), disabled (bool), size (sm|md|lg), variant (default|skeuomorphic|flat|neumorphic)
$name     = $name     ?? 'toggle';
$id       = $id       ?? ($name . '_' . uniqid());
$label    = $label    ?? null;
$checked  = !empty($checked);
$disabled = !empty($disabled);
$size     = $size     ?? 'md';
$variant  = $variant  ?? 'default';

$sizeClass = match($size) { 'sm' => 'vui-switch-sm', 'lg' => 'vui-switch-lg', default => 'vui-switch-md' };
$checkedAttr  = $checked  ? 'checked' : '';
$disabledAttr = $disabled ? 'disabled' : '';

$variantClassMap = [
    'skeuomorphic' => 'vui-switch-custom vui-switch-skeuo',
    'skeuo'        => 'vui-switch-custom vui-switch-skeuo',
    'flat'         => 'vui-switch-custom vui-switch-flat',
    'neumorphic'   => 'vui-switch-custom vui-switch-neumorphic',
    'soft'         => 'vui-switch-custom vui-switch-neumorphic',
];
$isCustom = isset($variantClassMap[$variant]);
?>
<?php if ($isCustom): ?>
    <label class="<?= $variantClassMap[$variant] ?> <?= $disabled ? 'vui-switch-disabled' : '' ?>" for="<?= htmlspecialchars($id) ?>">
        <input type="checkbox" id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($name) ?>"
               role="switch" aria-checked="<?= $checked ? 'true' : 'false' ?>"
               <?= $checkedAttr ?> <?= $disabledAttr ?>>
        <span class="vui-switch-track">
            <span class="vui-switch-thumb"></span>
        </span>
        <?php if ($label): ?>
            <span class="vui-switch-label"><?= htmlspecialchars($label) ?></span>
        <?php endif; ?>
    </label>
<?php else: ?>
    <label class="vui-switch-wrapper <?= $disabled ? 'vui-switch-disabled' : '' ?>">
        <input type="checkbox" id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($name) ?>"
               class="vui-switch-input" role="switch" aria-checked="<?= $checked ? 'true' : 'false' ?>"
               <?= $checkedAttr ?> <?= $disabledAttr ?>>
        <span class="vui-switch-track <?= $sizeClass ?>">
            <span class="vui-switch-thumb"></span>
        </span>
        <?php if ($label): ?>
            <span class="vui-switch-label"><?= htmlspecialchars($label) ?></span>
        <?php endif; ?>
    </label>
<?php endif; ?>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{description: string, usage: string, template: string} */
    private function pagination(): array
    {
        return [
            'description' => 'Pagination bar — current page, total pages, prev/next links',
            'usage'       => '<x-pagination :current="3" :total="10" url="/posts?page=" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Pagination Component
// Props: current (int), total (int), url (string base), window (int)
$current = (int)($current ?? 1);
$total   = (int)($total   ?? 1);
$url     = $url     ?? '?page=';
$window  = (int)($window  ?? 2);
$prev = max(1, $current - 1);
$next = min($total, $current + 1);
$start = max(1, $current - $window);
$end   = min($total, $current + $window);
?>
<nav class="vui-pagination" aria-label="Pagination">
    <a href="<?= htmlspecialchars($url . $prev) ?>" class="vui-page-btn <?= $current <= 1 ? 'vui-page-disabled' : '' ?>" aria-label="Previous" <?= $current <= 1 ? 'aria-disabled="true"' : '' ?>>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    <?php if ($start > 1): ?>
        <a href="<?= htmlspecialchars($url . '1') ?>" class="vui-page-btn">1</a>
        <?php if ($start > 2): ?><span class="vui-page-ellipsis">&hellip;</span><?php endif; ?>
    <?php endif; ?>
    <?php for ($i = $start; $i <= $end; $i++): ?>
        <a href="<?= htmlspecialchars($url . $i) ?>" class="vui-page-btn <?= $i === $current ? 'vui-page-active' : '' ?>" <?= $i === $current ? 'aria-current="page"' : '' ?>><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($end < $total): ?>
        <?php if ($end < $total - 1): ?><span class="vui-page-ellipsis">&hellip;</span><?php endif; ?>
        <a href="<?= htmlspecialchars($url . $total) ?>" class="vui-page-btn"><?= $total ?></a>
    <?php endif; ?>
    <a href="<?= htmlspecialchars($url . $next) ?>" class="vui-page-btn <?= $current >= $total ? 'vui-page-disabled' : '' ?>" aria-label="Next" <?= $current >= $total ? 'aria-disabled="true"' : '' ?>>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
    </a>
</nav>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{description: string, usage: string, template: string} */
    private function skeleton(): array
    {
        return [
            'description' => 'Skeleton loader — animated placeholder for loading content',
            'usage'       => '<x-skeleton lines="3" avatar="true" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Skeleton Component
// Props: lines (int), avatar (bool), width (string), height (string), type (text|circle|rect)
$lines  = (int)($lines ?? 3);
$avatar = !empty($avatar);
$width  = $width  ?? '100%';
$height = $height ?? '1rem';
$type   = $type   ?? 'text';
?>
<div class="vui-skeleton-wrap" aria-busy="true" aria-label="Loading...">
    <?php if ($avatar): ?>
        <div class="vui-skeleton vui-skeleton-circle" style="width:2.5rem;height:2.5rem"></div>
    <?php endif; ?>
    <?php if ($type === 'rect'): ?>
        <div class="vui-skeleton vui-skeleton-rect" style="width:<?= htmlspecialchars($width) ?>;height:<?= htmlspecialchars($height) ?>"></div>
    <?php else: ?>
        <?php for ($i = 0; $i < $lines; $i++): ?>
            <div class="vui-skeleton vui-skeleton-text" style="width:<?= $i === $lines - 1 ? '70%' : '100%' ?>"></div>
        <?php endfor; ?>
    <?php endif; ?>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{description: string, usage: string, template: string} */
    private function empty_(): array
    {
        return [
            'description' => 'Empty state — illustration, title, description and action slot for zero-data screens',
            'usage'       => '<x-empty title="No results found" description="Try a different search term." />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Empty State Component
// Props: title, description, icon
$title       = $title       ?? 'Nothing here yet';
$description = $description ?? 'Get started by adding something new.';
$icon        = $icon        ?? null;
?>
<div class="vui-empty">
    <div class="vui-empty-icon">
        <?php if ($icon): ?>
            <?= $icon ?>
        <?php else: ?>
            <svg width="64" height="64" viewBox="0 0 64 64" fill="none" aria-hidden="true">
                <circle cx="32" cy="32" r="30" stroke="currentColor" stroke-width="2" stroke-dasharray="6 4"/>
                <path d="M22 32h20M32 22v20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        <?php endif; ?>
    </div>
    <h3 class="vui-empty-title"><?= htmlspecialchars($title) ?></h3>
    <p class="vui-empty-desc"><?= htmlspecialchars($description) ?></p>
    <?php if (!empty($slot)): ?>
        <div class="vui-empty-action"><?= $slot ?></div>
    <?php endif; ?>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{description: string, usage: string, template: string} */
    private function divider(): array
    {
        return [
            'description' => 'Divider — horizontal or vertical separator with optional label',
            'usage'       => '<x-divider label="OR" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Divider Component
// Props: label, orientation (horizontal|vertical)
$label       = $label       ?? null;
$orientation = $orientation ?? 'horizontal';
?>
<?php if ($orientation === 'vertical'): ?>
    <div class="vui-divider-vertical" role="separator" aria-orientation="vertical"></div>
<?php elseif ($label): ?>
    <div class="vui-divider-labeled" role="separator">
        <span class="vui-divider-line"></span>
        <span class="vui-divider-label"><?= htmlspecialchars($label) ?></span>
        <span class="vui-divider-line"></span>
    </div>
<?php else: ?>
    <hr class="vui-divider" role="separator">
<?php endif; ?>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{description: string, usage: string, template: string} */
    private function drawer(): array
    {
        return [
            'description' => 'Slide-in drawer panel — left/right/top/bottom, with overlay and close button',
            'usage'       => '<x-drawer id="my-drawer" position="right" title="Settings">Content</x-drawer>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Drawer Component
// Props: id, title, position (left|right|top|bottom)
$id       = $id       ?? 'vui-drawer-' . uniqid();
$title    = $title    ?? null;
$position = $position ?? 'right';
$posClass = 'vui-drawer-' . htmlspecialchars($position);
?>
<div id="<?= htmlspecialchars($id) ?>" class="vui-drawer-backdrop" role="dialog" aria-modal="true" aria-hidden="true"
     onclick="if(event.target===this)this.setAttribute('aria-hidden','true')">
    <div class="vui-drawer <?= $posClass ?>">
        <?php if ($title): ?>
            <div class="vui-drawer-header">
                <h2 class="vui-drawer-title"><?= htmlspecialchars($title) ?></h2>
                <button class="vui-drawer-close" aria-label="Close"
                        onclick="document.getElementById('<?= htmlspecialchars($id) ?>').setAttribute('aria-hidden','true')">&times;</button>
            </div>
        <?php endif; ?>
        <div class="vui-drawer-body"><?= $slot ?? '' ?></div>
    </div>
</div>
<script>
window.vui = window.vui || {};
vui.openDrawer  = function(id) { document.getElementById(id).setAttribute('aria-hidden','false'); };
vui.closeDrawer = function(id) { document.getElementById(id).setAttribute('aria-hidden','true'); };
</script>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{description: string, usage: string, template: string} */
    private function popover(): array
    {
        return [
            'description' => 'Popover — floating content panel anchored to a trigger element',
            'usage'       => '<x-popover trigger="Click me" title="Info">Popover content here</x-popover>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Popover Component
// Props: trigger, title, placement (top|bottom|left|right)
$trigger   = $trigger   ?? 'Open';
$title     = $title     ?? null;
$placement = $placement ?? 'bottom';
$uid = 'pop-' . substr(md5(uniqid()), 0, 6);
?>
<div class="vui-popover-wrap">
    <button class="vui-popover-trigger" type="button"
            onclick="const p=document.getElementById('<?= $uid ?>');p.hidden=!p.hidden"
            aria-expanded="false" aria-controls="<?= $uid ?>">
        <?= htmlspecialchars($trigger) ?>
    </button>
    <div id="<?= $uid ?>" class="vui-popover vui-popover-<?= htmlspecialchars($placement) ?>" hidden role="tooltip">
        <?php if ($title): ?>
            <div class="vui-popover-title"><?= htmlspecialchars($title) ?></div>
        <?php endif; ?>
        <div class="vui-popover-body"><?= $slot ?? '' ?></div>
    </div>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{description: string, usage: string, template: string} */
    private function confirm(): array
    {
        return [
            'description' => 'Confirm dialog — modal with confirm/cancel for destructive operations',
            'usage'       => '<x-confirm id="del-confirm" title="Delete item?" confirm-label="Delete" :danger="true" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Confirm Dialog Component
// Props: id, title, message, confirmLabel, cancelLabel, danger (bool), action, method
$id           = $id           ?? 'vui-confirm-' . uniqid();
$title        = $title        ?? 'Are you sure?';
$message      = $message      ?? 'This action cannot be undone.';
$confirmLabel = $confirmLabel ?? 'Confirm';
$cancelLabel  = $cancelLabel  ?? 'Cancel';
$danger       = !empty($danger);
$action       = $action       ?? '#';
$method       = strtoupper($method ?? 'POST');
?>
<div id="<?= htmlspecialchars($id) ?>" class="vui-modal-backdrop" role="alertdialog" aria-modal="true"
     aria-hidden="true" aria-labelledby="<?= htmlspecialchars($id) ?>-title">
    <div class="vui-modal vui-confirm-dialog">
        <div class="vui-modal-header">
            <h3 id="<?= htmlspecialchars($id) ?>-title" class="vui-modal-title"><?= htmlspecialchars($title) ?></h3>
        </div>
        <div class="vui-modal-body">
            <p><?= htmlspecialchars($message) ?></p>
        </div>
        <div class="vui-modal-footer">
            <button type="button" class="vui-btn vui-btn-secondary"
                    onclick="document.getElementById('<?= htmlspecialchars($id) ?>').setAttribute('aria-hidden','true')">
                <?= htmlspecialchars($cancelLabel) ?>
            </button>
            <form action="<?= htmlspecialchars($action) ?>" method="POST" style="display:inline">
                <?php if ($method !== 'POST'): ?>
                    <input type="hidden" name="_method" value="<?= htmlspecialchars($method) ?>">
                <?php endif; ?>
                <button type="submit" class="vui-btn <?= $danger ? 'vui-btn-danger' : 'vui-btn-primary' ?>">
                    <?= htmlspecialchars($confirmLabel) ?>
                </button>
            </form>
        </div>
    </div>
</div>
<script>
window.vui = window.vui || {};
vui.confirm = function(id) { document.getElementById(id).setAttribute('aria-hidden','false'); };
</script>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{description: string, usage: string, template: string} */
    private function datepicker(): array
    {
        return [
            'description' => 'Date picker input — native date input with label and Veldora styling',
            'usage'       => '<x-datepicker name="dob" label="Date of Birth" min="2000-01-01" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — DatePicker Component
// Props: name, id, label, value, min, max, required (bool), disabled (bool), helper
$name     = $name     ?? 'date';
$id       = $id       ?? $name;
$label    = $label    ?? null;
$value    = $value    ?? '';
$min      = $min      ?? '';
$max      = $max      ?? '';
$required = !empty($required);
$disabled = !empty($disabled);
$helper   = $helper   ?? null;
?>
<div class="vui-field">
    <?php if ($label): ?>
        <label for="<?= htmlspecialchars($id) ?>" class="vui-label">
            <?= htmlspecialchars($label) ?>
            <?php if ($required): ?><span class="vui-required" aria-hidden="true">*</span><?php endif; ?>
        </label>
    <?php endif; ?>
    <input type="date" id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($name) ?>"
           class="vui-input vui-datepicker"
           value="<?= htmlspecialchars($value) ?>"
           <?= $min ? 'min="' . htmlspecialchars($min) . '"' : '' ?>
           <?= $max ? 'max="' . htmlspecialchars($max) . '"' : '' ?>
           <?= $required ? 'required' : '' ?>
           <?= $disabled ? 'disabled' : '' ?>>
    <?php if ($helper): ?>
        <p class="vui-helper"><?= htmlspecialchars($helper) ?></p>
    <?php endif; ?>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{description: string, usage: string, template: string} */
    private function fileupload(): array
    {
        return [
            'description' => 'File upload — styled drag-and-drop zone with file type and size hints',
            'usage'       => '<x-fileupload name="avatar" label="Profile Picture" accept="image/*" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — FileUpload Component
// Props: name, id, label, accept, multiple (bool), required (bool), helper, maxSize
$name     = $name     ?? 'file';
$id       = $id       ?? $name;
$label    = $label    ?? 'Choose file';
$accept   = $accept   ?? '*';
$multiple = !empty($multiple);
$required = !empty($required);
$helper   = $helper   ?? null;
$maxSize  = $maxSize  ?? null;
?>
<div class="vui-field">
    <label for="<?= htmlspecialchars($id) ?>" class="vui-label"><?= htmlspecialchars($label) ?></label>
    <label for="<?= htmlspecialchars($id) ?>" class="vui-fileupload-zone">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="17 8 12 3 7 8"/>
            <line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
        <span class="vui-fileupload-text">Drag &amp; drop or <strong>browse</strong></span>
        <?php if ($maxSize): ?>
            <span class="vui-fileupload-hint">Max <?= htmlspecialchars($maxSize) ?></span>
        <?php endif; ?>
        <input type="file" id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($name) ?>"
               accept="<?= htmlspecialchars($accept) ?>" class="vui-fileupload-input"
               <?= $multiple ? 'multiple' : '' ?> <?= $required ? 'required' : '' ?>>
    </label>
    <?php if ($helper): ?><p class="vui-helper"><?= htmlspecialchars($helper) ?></p><?php endif; ?>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{description: string, usage: string, template: string} */
    private function combobox(): array
    {
        return [
            'description' => 'Combobox — searchable select with autocomplete dropdown',
            'usage'       => '<x-combobox name="country" label="Country" :options="$countries" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Combobox Component
// Props: name, id, label, options (array value=>label), value, placeholder, required (bool)
$name        = $name        ?? 'combobox';
$id          = $id          ?? $name;
$label       = $label       ?? null;
$options     = $options      ?? [];
$value       = $value       ?? '';
$placeholder = $placeholder ?? 'Search...';
$required    = !empty($required);
$uid = 'cb-' . substr(md5($id), 0, 6);
?>
<div class="vui-field vui-combobox-wrap" id="<?= $uid ?>">
    <?php if ($label): ?>
        <label class="vui-label" for="<?= htmlspecialchars($id) ?>-input"><?= htmlspecialchars($label) ?></label>
    <?php endif; ?>
    <div class="vui-combobox">
        <input type="text" id="<?= htmlspecialchars($id) ?>-input" class="vui-input vui-combobox-input"
               placeholder="<?= htmlspecialchars($placeholder) ?>" autocomplete="off"
               oninput="vuiCbFilter('<?= $uid ?>')" onfocus="vuiCbOpen('<?= $uid ?>')">
        <input type="hidden" name="<?= htmlspecialchars($name) ?>" id="<?= htmlspecialchars($id) ?>"
               value="<?= htmlspecialchars($value) ?>" <?= $required ? 'required' : '' ?>>
        <ul class="vui-combobox-list" id="<?= $uid ?>-list" role="listbox" hidden>
            <?php foreach ($options as $val => $lbl): ?>
                <li class="vui-combobox-option" role="option"
                    data-value="<?= htmlspecialchars((string)$val) ?>"
                    onclick="vuiCbSelect('<?= $uid ?>','<?= htmlspecialchars((string)$val) ?>',this.textContent.trim())">
                    <?= htmlspecialchars((string)$lbl) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<script>
function vuiCbOpen(u){document.getElementById(u+'-list').hidden=false;}
function vuiCbFilter(u){
    var q=document.querySelector('#'+u+' .vui-combobox-input').value.toLowerCase();
    document.querySelectorAll('#'+u+'-list .vui-combobox-option').forEach(function(o){o.hidden=!o.textContent.toLowerCase().includes(q);});
    document.getElementById(u+'-list').hidden=false;
}
function vuiCbSelect(u,val,lbl){
    document.querySelector('#'+u+' .vui-combobox-input').value=lbl;
    document.querySelector('#'+u+' input[type=hidden]').value=val;
    document.getElementById(u+'-list').hidden=true;
}
document.addEventListener('click',function(e){
    document.querySelectorAll('.vui-combobox-list').forEach(function(l){
        if(!l.closest('.vui-combobox-wrap').contains(e.target))l.hidden=true;
    });
});
</script>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{description: string, usage: string, template: string} */
    private function inputgroup(): array
    {
        return [
            'description' => 'Input group — prefix/suffix addon (text or icon) attached to an input',
            'usage'       => '<x-inputgroup name="price" prefix="$" suffix=".00" placeholder="0" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — InputGroup Component
// Props: name, id, type, label, placeholder, value, prefix, suffix, required (bool), disabled (bool)
$name        = $name        ?? 'field';
$id          = $id          ?? $name;
$type        = $type        ?? 'text';
$label       = $label       ?? null;
$placeholder = $placeholder ?? '';
$value       = $value       ?? '';
$prefix      = $prefix      ?? null;
$suffix      = $suffix      ?? null;
$required    = !empty($required);
$disabled    = !empty($disabled);
?>
<div class="vui-field">
    <?php if ($label): ?>
        <label for="<?= htmlspecialchars($id) ?>" class="vui-label"><?= htmlspecialchars($label) ?></label>
    <?php endif; ?>
    <div class="vui-input-group <?= $disabled ? 'vui-input-group-disabled' : '' ?>">
        <?php if ($prefix): ?><span class="vui-input-addon vui-input-prefix"><?= htmlspecialchars($prefix) ?></span><?php endif; ?>
        <input type="<?= htmlspecialchars($type) ?>" id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($name) ?>"
               class="vui-input" placeholder="<?= htmlspecialchars($placeholder) ?>"
               value="<?= htmlspecialchars($value) ?>"
               <?= $required ? 'required' : '' ?> <?= $disabled ? 'disabled' : '' ?>>
        <?php if ($suffix): ?><span class="vui-input-addon vui-input-suffix"><?= htmlspecialchars($suffix) ?></span><?php endif; ?>
    </div>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{description: string, usage: string, template: string} */
    private function stat(): array
    {
        return [
            'description' => 'Stat card — metric display with value, label, icon, and optional trend indicator',
            'usage'       => '<x-stat label="Total Users" value="12,403" trend="+8.2%" :trend-up="true" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Stat Component
// Props: label, value, trend, trendUp (bool), icon, prefix, suffix
$label   = $label   ?? 'Metric';
$value   = $value   ?? '0';
$trend   = $trend   ?? null;
$trendUp = !empty($trendUp);
$icon    = $icon    ?? null;
$prefix  = $prefix  ?? '';
$suffix  = $suffix  ?? '';
?>
<div class="vui-stat">
    <?php if ($icon): ?>
        <div class="vui-stat-icon"><?= $icon ?></div>
    <?php endif; ?>
    <div class="vui-stat-body">
        <p class="vui-stat-label"><?= htmlspecialchars($label) ?></p>
        <p class="vui-stat-value"><?= htmlspecialchars($prefix) ?><?= htmlspecialchars($value) ?><?= htmlspecialchars($suffix) ?></p>
        <?php if ($trend !== null): ?>
            <span class="vui-stat-trend <?= $trendUp ? 'vui-trend-up' : 'vui-trend-down' ?>">
                <?php if ($trendUp): ?>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
                <?php else: ?>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                <?php endif; ?>
                <?= htmlspecialchars($trend) ?>
            </span>
        <?php endif; ?>
    </div>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{description: string, usage: string, template: string} */
    private function datatable(): array
    {
        return [
            'description' => 'DataTable — interactive table with client-side search, sort, and pagination',
            'usage'       => '<x-datatable :columns="$cols" :rows="$rows" :searchable="true" :per-page="10" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — DataTable Component
// Props: columns (assoc array key=>label), rows (array of assoc arrays), searchable (bool), perPage (int)
$columns    = $columns    ?? [];
$rows       = $rows       ?? [];
$searchable = !empty($searchable);
$perPage    = (int)($perPage ?? 10);
$uid = 'dt-' . substr(md5(uniqid()), 0, 6);
$colKeys = array_keys($columns);
?>
<div class="vui-datatable-wrap" id="<?= $uid ?>">
    <?php if ($searchable): ?>
        <div class="vui-datatable-toolbar">
            <input type="search" class="vui-input vui-datatable-search" placeholder="Search..."
                   oninput="vuiDt_<?= $uid ?>_search(this.value)">
        </div>
    <?php endif; ?>
    <div class="vui-table-responsive">
        <table class="vui-table vui-table-hover vui-table-striped">
            <thead>
                <tr>
                    <?php foreach ($columns as $key => $lbl): ?>
                        <th onclick="vuiDt_<?= $uid ?>_sort('<?= htmlspecialchars((string)$key) ?>')" style="cursor:pointer">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span><?= htmlspecialchars((string)$lbl) ?></span>
                                <svg class="vui-sort-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                            </div>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody id="<?= $uid ?>-tbody">
                <?php foreach ($rows as $row): ?>
                    <tr><?php foreach ($colKeys as $k): ?>
                        <td><?= htmlspecialchars((string)($row[$k] ?? '')) ?></td>
                    <?php endforeach; ?></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div id="<?= $uid ?>-pages" class="vui-datatable-pages"></div>
</div>
<script>
(function(){
    var uid='<?= $uid ?>',perPage=<?= $perPage ?>,page=1,q='',asc=true,sKey='';
    var tbody=document.getElementById(uid+'-tbody');
    var allRows=Array.from(tbody.querySelectorAll('tr'));
    function filtered(){return q?allRows.filter(function(r){return r.textContent.toLowerCase().includes(q);}):allRows.slice();}
    function render(){
        var f=filtered();
        var tot=Math.ceil(f.length/perPage)||1;
        if(page>tot)page=1;
        tbody.innerHTML='';
        f.slice((page-1)*perPage,page*perPage).forEach(function(r){tbody.appendChild(r);});
        var pg=document.getElementById(uid+'-pages');
        pg.innerHTML='';
        for(var i=1;i<=tot;i++){
            var b=document.createElement('button');
            b.textContent=i;b.className='vui-page-btn'+(i===page?' vui-page-active':'');
            b.setAttribute('data-p',i);
            b.onclick=(function(p){return function(){page=p;render();};})(i);
            pg.appendChild(b);
        }
    }
    window['vuiDt_'+uid+'_search']=function(v){q=v.toLowerCase();page=1;render();};
    window['vuiDt_'+uid+'_sort']=function(k){
        if(sKey===k)asc=!asc;else{sKey=k;asc=true;}
        var idx=<?= json_encode($colKeys) ?>.indexOf(k);
        allRows.sort(function(a,b){
            var av=(a.cells[idx]||{}).textContent||'';
            var bv=(b.cells[idx]||{}).textContent||'';
            return asc?av.localeCompare(bv,undefined,{numeric:true}):bv.localeCompare(av,undefined,{numeric:true});
        });
        render();
    };
    render();
}());
</script>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{description: string, usage: string, template: string} */
    private function timeline(): array
    {
        return [
            'description' => 'Timeline — vertical event list with icon, title, description, and timestamp',
            'usage'       => '<x-timeline :items="$events" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Timeline Component
// Props: items (array of {title, description, time, icon, color})
$items = $items ?? [];
?>
<ol class="vui-timeline">
    <?php foreach ($items as $item): ?>
        <li class="vui-timeline-item">
            <div class="vui-timeline-marker" style="<?= !empty($item['color']) ? 'background:' . htmlspecialchars($item['color']) : '' ?>">
                <?= $item['icon'] ?? '' ?>
            </div>
            <div class="vui-timeline-content">
                <p class="vui-timeline-title"><?= htmlspecialchars($item['title'] ?? '') ?></p>
                <?php if (!empty($item['description'])): ?>
                    <p class="vui-timeline-desc"><?= htmlspecialchars($item['description']) ?></p>
                <?php endif; ?>
                <?php if (!empty($item['time'])): ?>
                    <time class="vui-timeline-time"><?= htmlspecialchars($item['time']) ?></time>
                <?php endif; ?>
            </div>
        </li>
    <?php endforeach; ?>
</ol>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{description: string, usage: string, template: string} */
    private function stepper(): array
    {
        return [
            'description' => 'Stepper — multi-step wizard indicator showing completed, active, and upcoming steps',
            'usage'       => "<x-stepper :steps=\"['Account','Profile','Confirm']\" :current=\"2\" />",
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Stepper Component
// Props: steps (array of labels), current (1-indexed int)
$steps   = $steps   ?? [];
$current = (int)($current ?? 1);
?>
<ol class="vui-stepper" aria-label="Progress">
    <?php foreach ($steps as $i => $step): ?>
        <?php
        $num    = $i + 1;
        $status = $num < $current ? 'done' : ($num === $current ? 'active' : 'pending');
        ?>
        <li class="vui-stepper-step vui-step-<?= $status ?>" aria-current="<?= $status === 'active' ? 'step' : 'false' ?>">
            <span class="vui-step-circle">
                <?php if ($status === 'done'): ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                <?php else: ?>
                    <?= $num ?>
                <?php endif; ?>
            </span>
            <span class="vui-step-label"><?= htmlspecialchars((string)$step) ?></span>
            <?php if ($num < count($steps)): ?>
                <span class="vui-step-line" aria-hidden="true"></span>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ol>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{description: string, usage: string, template: string} */
    private function sidebar(): array
    {
        return [
            'description' => 'Sidebar — modern SaaS application navigation with workspace switcher, search trigger, categorized groups, badges, collapsible tree, and user profile footer',
            'usage'       => '<x-sidebar :brand="[\'name\' => \'Acme Corp\', \'plan\' => \'Pro\']" :groups="$navGroups" :user="$currentUser" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Sidebar Component
// Props: brand (array or string), groups (array of {title, items[]}), items (flat array fallback), user (array), search (bool), collapsed (bool)
$brand     = $brand     ?? ['name' => 'Veldora App', 'plan' => 'Pro', 'logo' => 'V'];
if (is_string($brand)) { $brand = ['name' => $brand, 'plan' => 'Pro', 'logo' => strtoupper(substr($brand, 0, 1))]; }
$groups    = $groups    ?? [];
$items     = $items     ?? [];
$user      = $user      ?? null;
$search    = $search    ?? true;
$collapsed = !empty($collapsed);
$extra     = $class     ?? '';

// If flat items passed without groups, wrap in single default group
if (empty($groups) && !empty($items)) {
    $groups = [['title' => 'Menu', 'items' => $items]];
}
?>
<aside class="vui-sidebar <?= $collapsed ? 'vui-sidebar-collapsed' : '' ?> <?= htmlspecialchars($extra) ?>" role="navigation">
    <!-- Brand / Workspace Switcher Header -->
    <div class="vui-sidebar-header">
        <a href="/" class="vui-sidebar-brand">
            <div class="vui-sidebar-logo"><?= htmlspecialchars($brand['logo'] ?? 'V') ?></div>
            <div style="display:flex;flex-direction:column;gap:2px;min-width:0;">
                <span class="vui-sidebar-brand-text"><?= htmlspecialchars($brand['name'] ?? 'App') ?></span>
                <?php if (!empty($brand['plan'])): ?>
                    <span class="vui-sidebar-brand-badge"><?= htmlspecialchars($brand['plan']) ?></span>
                <?php endif; ?>
            </div>
            <span class="vui-sidebar-brand-caret">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
            </span>
        </a>
    </div>

    <!-- Quick Search Command Trigger -->
    <?php if ($search): ?>
        <div class="vui-sidebar-search-box">
            <button type="button" class="vui-sidebar-search-btn" onclick="if(window.openSearchModal) window.openSearchModal(); else if(window.showToast) window.showToast('Search command triggered (⌘K)', 'info');">
                <div class="vui-sidebar-search-left">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <span class="vui-sidebar-search-text">Quick Search...</span>
                </div>
                <kbd class="vui-sidebar-search-kbd">⌘K</kbd>
            </button>
        </div>
    <?php endif; ?>

    <!-- Navigation Groups -->
    <div class="vui-sidebar-body">
        <?php foreach ($groups as $group): ?>
            <div class="vui-sidebar-group">
                <?php if (!empty($group['title'])): ?>
                    <p class="vui-sidebar-group-title"><?= htmlspecialchars($group['title']) ?></p>
                <?php endif; ?>
                <ul class="vui-sidebar-nav">
                    <?php foreach ($group['items'] ?? [] as $item): ?>
                        <?php
                            $active = !empty($item['active']);
                            $hasChildren = !empty($item['children']);
                        ?>
                        <li class="vui-sidebar-item <?= $hasChildren ? 'vui-sidebar-has-sub' : '' ?>">
                            <a href="<?= htmlspecialchars($item['href'] ?? '#') ?>" class="vui-sidebar-link <?= $active ? 'active' : '' ?>">
                                <?php if (!empty($item['icon'])): ?>
                                    <span class="vui-sidebar-icon" aria-hidden="true"><?= $item['icon'] ?></span>
                                <?php endif; ?>
                                <span class="vui-sidebar-label"><?= htmlspecialchars($item['label'] ?? '') ?></span>
                                <?php if (!empty($item['badge'])): ?>
                                    <span class="vui-sidebar-badge <?= !empty($item['badge_accent']) ? 'vui-sidebar-badge-accent' : '' ?>"><?= htmlspecialchars($item['badge']) ?></span>
                                <?php endif; ?>
                                <?php if ($hasChildren): ?>
                                    <span class="vui-sidebar-chevron">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <?php if ($hasChildren): ?>
                                <ul class="vui-sidebar-sub">
                                    <?php foreach ($item['children'] as $child): ?>
                                        <li>
                                            <a href="<?= htmlspecialchars($child['href'] ?? '#') ?>" class="vui-sidebar-sub-link <?= !empty($child['active']) ? 'active' : '' ?>">
                                                <?= htmlspecialchars($child['label'] ?? '') ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <?php if (!empty($slot)): ?>
            <div class="vui-sidebar-custom-slot"><?= $slot ?></div>
        <?php endif; ?>
    </div>

    <!-- User Profile Footer -->
    <?php if ($user): ?>
        <div class="vui-sidebar-footer">
            <a href="<?= htmlspecialchars($user['href'] ?? '#') ?>" class="vui-sidebar-user">
                <div class="vui-sidebar-user-avatar">
                    <?= htmlspecialchars($user['avatar'] ?? 'U') ?>
                    <span class="vui-sidebar-user-status"></span>
                </div>
                <div class="vui-sidebar-user-info">
                    <span class="vui-sidebar-user-name"><?= htmlspecialchars($user['name'] ?? 'User') ?></span>
                    <span class="vui-sidebar-user-role"><?= htmlspecialchars($user['role'] ?? 'Member') ?></span>
                </div>
                <span class="vui-sidebar-user-more">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                </span>
            </a>
        </div>
    <?php endif; ?>
</aside>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{description: string, usage: string, template: string} */
    private function container(): array
    {
        return [
            'description' => 'Container — responsive max-width wrapper with configurable size and padding',
            'usage'       => '<x-container size="lg">Page content</x-container>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Container Component
// Props: size (sm|md|lg|xl|full), center (bool)
$size   = $size  ?? 'lg';
$center = isset($center) ? !empty($center) : true;
$extra  = $class ?? '';
$sizeMap = [
    'sm'   => 'vui-container-sm',
    'md'   => 'vui-container-md',
    'lg'   => 'vui-container-lg',
    'xl'   => 'vui-container-xl',
    'full' => 'vui-container-full',
];
$cls = 'vui-container ' . ($sizeMap[$size] ?? $sizeMap['lg']) . ($center ? ' vui-container-center' : '') . ($extra ? ' ' . htmlspecialchars($extra) : '');
?>
<div class="<?= $cls ?>">
    <?= $slot ?? '' ?>
</div>
TEMPLATE,
        ];
    }

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function footer(): array
    {
        return [
            'description' => 'Responsive site footer with branding, links, and legal text',
            'usage'       => '<x-footer brand="My App" tagline="Built with Veldora"></x-footer>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Footer Component
// Props: brand (string), tagline (string), links (array), legal (string)
$brand   = $brand   ?? config('app.name', 'My App');
$tagline = $tagline ?? 'The PHP framework you actually own.';
$legal   = $legal   ?? '&copy; ' . date('Y') . ' ' . htmlspecialchars($brand) . '. All rights reserved.';
$links   = $links   ?? [
    ['label' => 'Home',      'url' => '/'],
    ['label' => 'About',     'url' => '/about'],
    ['label' => 'Privacy',   'url' => '/privacy'],
    ['label' => 'Contact',   'url' => '/contact'],
];
?>
<style>
.vui-footer{background:var(--vui-surface,#18181b);border-top:1px solid var(--vui-border,#27272a);padding:3rem 1.5rem 1.5rem;color:var(--vui-muted,#a1a1aa);font-family:inherit}
.vui-footer-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:1fr auto;gap:2rem;align-items:start}
.vui-footer-brand h3{margin:0 0 .35rem;font-size:1.125rem;font-weight:700;background:linear-gradient(135deg,#8b5cf6,#6366f1);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.vui-footer-brand p{margin:0;font-size:.85rem}
.vui-footer-links{display:flex;flex-wrap:wrap;gap:.5rem 1.5rem;justify-content:flex-end}
.vui-footer-links a{color:var(--vui-muted,#a1a1aa);text-decoration:none;font-size:.9rem;transition:color .2s}
.vui-footer-links a:hover{color:#fff}
.vui-footer-legal{border-top:1px solid var(--vui-border,#27272a);margin-top:2rem;padding-top:1.25rem;text-align:center;font-size:.8rem}
@media(max-width:640px){.vui-footer-inner{grid-template-columns:1fr}.vui-footer-links{justify-content:flex-start}}
</style>
<footer class="vui-footer" role="contentinfo">
    <div class="vui-footer-inner">
        <div class="vui-footer-brand">
            <h3><?= htmlspecialchars($brand) ?></h3>
            <p><?= htmlspecialchars($tagline) ?></p>
        </div>
        <nav class="vui-footer-links" aria-label="Footer navigation">
            <?php foreach ($links as $link): ?>
                <a href="<?= htmlspecialchars($link['url'] ?? '#') ?>"><?= htmlspecialchars($link['label'] ?? '') ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
    <div class="vui-footer-legal"><?= $legal ?></div>
</footer>
TEMPLATE,
        ];
    }

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function rating(): array
    {
        return [
            'description' => 'Interactive star rating component with half-star and read-only support',
            'usage'       => '<x-rating name="score" value="3" max="5"></x-rating>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Rating Component
// Props: name (string), value (int|float), max (int), readonly (bool), size (sm|md|lg), color (string)
$name     = $name     ?? 'rating';
$value    = (float)  ($value    ?? 0);
$max      = (int)    ($max      ?? 5);
$readonly = isset($readonly) ? !empty($readonly) : false;
$size     = $size     ?? 'md';
$color    = $color    ?? '#f59e0b';
$id       = 'vui-rating-' . substr(md5($name . uniqid()), 0, 8);
$sizes    = ['sm' => '1rem', 'md' => '1.5rem', 'lg' => '2rem'];
$starSize = $sizes[$size] ?? $sizes['md'];
?>
<style>
.vui-rating{display:inline-flex;flex-direction:row-reverse;gap:.15rem;align-items:center}
.vui-rating input{display:none}
.vui-rating label{cursor:pointer;font-size:<?= $starSize ?>;color:#3f3f46;transition:color .15s,transform .1s}
.vui-rating label:hover,.vui-rating label:hover~label,.vui-rating input:checked~label{color:<?= htmlspecialchars($color) ?>}
.vui-rating label:hover{transform:scale(1.15)}
.vui-rating-readonly .vui-rating label{cursor:default;pointer-events:none}
.vui-rating-value{font-size:.85rem;margin-left:.5rem;color:var(--vui-muted,#a1a1aa)}
</style>
<?php if ($readonly): ?>
<span class="vui-rating vui-rating-readonly" role="img" aria-label="Rating: <?= $value ?> out of <?= $max ?> stars">
    <?php for ($i = $max; $i >= 1; $i--): ?>
        <label aria-hidden="true" style="color:<?= $i <= $value ? htmlspecialchars($color) : '#3f3f46' ?>">&#9733;</label>
    <?php endfor; ?>
</span>
<?php else: ?>
<span class="vui-rating" id="<?= $id ?>" role="radiogroup" aria-label="Star rating">
    <?php for ($i = $max; $i >= 1; $i--): ?>
        <input type="radio" id="<?= $id ?>-<?= $i ?>" name="<?= htmlspecialchars($name) ?>" value="<?= $i ?>"
               <?= $i === (int) $value ? 'checked' : '' ?>>
        <label for="<?= $id ?>-<?= $i ?>" title="<?= $i ?> star<?= $i !== 1 ? 's' : '' ?>">&#9733;</label>
    <?php endfor; ?>
</span>
<?php endif; ?>
TEMPLATE,
        ];
    }
}

