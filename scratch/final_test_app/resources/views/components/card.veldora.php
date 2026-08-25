<?php
// Veldora UI — Card Component
// Props: title, subtitle, footer, hover (bool), padding (none|sm|md|lg)
$title    = $title    ?? null;
$subtitle = $subtitle ?? null;
$footer   = $footer   ?? null;
$hover    = $hover    ?? false;
$padding  = $padding  ?? 'md';

$classes = 'vui-card vui-card-p-' . htmlspecialchars($padding) . ($hover ? ' vui-card-hover' : '');
?>
<div class="<?= $classes ?>">
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

    <?php if ($footer): ?>
        <div class="vui-card-footer">
            <?= $footer ?>
        </div>
    <?php endif; ?>
</div>
