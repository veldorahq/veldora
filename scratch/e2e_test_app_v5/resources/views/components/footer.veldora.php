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