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