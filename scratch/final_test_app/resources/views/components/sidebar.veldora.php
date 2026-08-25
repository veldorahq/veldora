<?php
// Veldora UI — Sidebar Component
// Props: items (array of {label, href, icon, active, children[]}), logo, collapsed (bool)
$items     = $items     ?? [];
$logo      = $logo      ?? 'App';
$collapsed = !empty($collapsed);
?>
<aside class="vui-sidebar <?= $collapsed ? 'vui-sidebar-collapsed' : '' ?>" role="navigation">
    <div class="vui-sidebar-header">
        <span class="vui-sidebar-logo"><?= htmlspecialchars($logo) ?></span>
    </div>
    <nav>
        <ul class="vui-sidebar-nav">
            <?php foreach ($items as $item): ?>
                <?php $active = !empty($item['active']); ?>
                <li class="vui-nav-item <?= $active ? 'vui-nav-active' : '' ?>">
                    <a href="<?= htmlspecialchars($item['href'] ?? '#') ?>" class="vui-nav-link">
                        <?php if (!empty($item['icon'])): ?>
                            <span class="vui-nav-icon" aria-hidden="true"><?= $item['icon'] ?></span>
                        <?php endif; ?>
                        <span class="vui-nav-label"><?= htmlspecialchars($item['label'] ?? '') ?></span>
                    </a>
                    <?php if (!empty($item['children'])): ?>
                        <ul class="vui-nav-sub">
                            <?php foreach ($item['children'] as $child): ?>
                                <li>
                                    <a href="<?= htmlspecialchars($child['href'] ?? '#') ?>" class="vui-nav-link vui-nav-sub-link">
                                        <?= htmlspecialchars($child['label'] ?? '') ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <?php if (!empty($slot)): ?><div class="vui-sidebar-footer"><?= $slot ?></div><?php endif; ?>
</aside>