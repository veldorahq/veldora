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