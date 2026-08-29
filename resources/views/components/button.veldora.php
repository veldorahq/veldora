<?php
// Veldora UI — Button Component
// Props: variant (primary|secondary|ghost|danger), size (sm|md|lg), disabled (bool), type (button|submit|reset)
$variant  = $variant  ?? 'primary';
$size     = $size     ?? 'md';
$disabled = $disabled ?? false;
$type     = $type     ?? 'button';

$variants = [
    'primary'   => 'vui-btn vui-btn-primary',
    'secondary' => 'vui-btn vui-btn-secondary',
    'ghost'     => 'vui-btn vui-btn-ghost',
    'danger'    => 'vui-btn vui-btn-danger',
];
$sizes = [
    'sm' => 'vui-btn-sm',
    'md' => 'vui-btn-md',
    'lg' => 'vui-btn-lg',
];

$classes  = ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
$disabledAttr = $disabled ? 'disabled aria-disabled="true"' : '';
?>
<button type="<?= htmlspecialchars($type) ?>" class="<?= $classes ?>" <?= $disabledAttr ?>>
    <?= $slot ?>
</button>