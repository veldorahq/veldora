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