<?php
// Veldora UI — Badge Component
// Props: variant (default|primary|secondary|success|warning|danger|outline), size (sm|md), pill (bool)
$variant = $variant ?? 'default';
$size    = $size    ?? 'md';
$pill    = $pill    ?? false;

$classes = 'vui-badge vui-badge-' . htmlspecialchars($variant)
         . ' vui-badge-' . htmlspecialchars($size)
         . ($pill ? ' vui-badge-pill' : '');
?>
<span class="<?= $classes ?>">
    <?= $slot ?>
</span>
