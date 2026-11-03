<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/brands.php';
$pageTitle = 'Brands';
$bodyClass = 'brands-page';
$brands = shop_brands();
$bySlug = [];
foreach ($brands as $brand) {
    $bySlug[$brand['slug']] = $brand;
}
$frontSlugs = ['dollar', 'piano', 'naulakha', 'uhu'];
$front = [];
foreach ($frontSlugs as $slug) {
    if (isset($bySlug[$slug])) {
        $front[] = $bySlug[$slug];
    }
}

function brand_directory_card(array $brand, bool $clone = false): void
{
    $hidden = $clone ? ' data-occasion-clone tabindex="-1" aria-hidden="true"' : '';
    ?>
    <a class="brand-card" href="<?= e(base_url('shop.php?brand=' . urlencode($brand['slug']))) ?>"<?= $hidden ?>>
        <span class="brand-face">
            <?= brand_icon($brand['slug'], $brand['name']) ?>
            <h2><?= e($brand['name']) ?></h2>
        </span>
        <span class="brand-copy">
            <p class="brand-line"><?= e($brand['line']) ?></p>
            <p class="eyebrow"><?= e($brand['place']) ?> · <?= count($brand['codes']) ?> pieces</p>
        </span>
    </a>
    <?php
}

require __DIR__ . '/includes/header.php';
?>
<div class="container py-4 brand-directory">
    <header class="brand-hero">
        <p class="eyebrow"><?= count($brands) ?> names on the shelf</p>
        <h1>Brands students already know</h1>
        <span class="brand-rule" aria-hidden="true"></span>
        <p>Slide the shelf, or open a name. The four at the front are the ones a customer asks for first: a pen, a pencil, a copy and a glue.</p>
    </header>

    <section class="brand-block" aria-labelledby="brand-front-title">
        <div class="brand-block-head">
            <p class="eyebrow">At the front of the counter</p>
            <h2 id="brand-front-title">Names this shop puts first</h2>
            <p>These four stay where a customer looks first. This is the counter’s own order, not a star rating and not a sales rank.</p>
        </div>
        <div class="brand-front-grid">
            <?php foreach ($front as $brand): ?>
                <?php brand_directory_card($brand); ?>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="brand-block" aria-labelledby="brand-slide-title">
        <div class="brand-block-head">
            <p class="eyebrow">The moving shelf</p>
            <h2 id="brand-slide-title">Every name, sliding past</h2>
            <p>All <?= count($brands) ?> shelves. Use the arrows, or let the row move on its own.</p>
        </div>
        <div class="brand-slide-run" data-occasion-run>
            <button class="brand-slide-arrow prev" type="button" data-occasion-prev aria-label="Previous brands">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <div class="brand-slide-viewport">
                <div class="occasion-track brand-slide-track">
                    <?php foreach ([false, true] as $clone): ?>
                        <?php foreach ($brands as $brand): ?>
                            <?php brand_directory_card($brand, $clone); ?>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="brand-slide-arrow next" type="button" data-occasion-next aria-label="Next brands">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
