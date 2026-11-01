<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/brands.php';
$pageTitle = 'Brands';
$brands = shop_brands();
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <p class="eyebrow"><?= count($brands) ?> names on the shelf</p>
    <h1>Brands students already know</h1>
    <p class="lead">These are Pakistani stationery names, plus UHU, which local shops stock. The marks are drawn for this shop. Open a name to see the pieces Paperlane keeps on that shelf.</p>
    <div class="reel" data-reel>
        <?php
        $slides = [
            ['lahore-green.jpg', 'A woman in Lahore in mint and white', 'Shawls on the Camel shelf', 'camel'],
            ['lahore-blue.jpg', 'A woman in Lahore wearing jhumka-style earrings', 'Jhumkas on the Mercury shelf', 'mercury'],
            ['gold-jewelry.jpg', 'Layered gold necklaces and earrings', 'Necklaces on the Winner shelf', 'winner'],
            ['lahore-purple.jpg', 'A woman in Lahore with pearls and a dupatta', 'Earrings on the Picasso shelf', 'picasso'],
            ['lahore-scarf.jpg', 'An embroidered suit and dupatta in Lahore', 'More shawls on the Camel shelf', 'camel'],
        ];
        foreach ($slides as $index => $slide):
        ?>
            <a class="reel-slide<?= $index === 0 ? ' is-on' : '' ?>" href="<?= e(base_url('shop.php?brand=' . $slide[3])) ?>">
                <img src="<?= e(base_url('assets/images/look/' . $slide[0])) ?>" alt="<?= e($slide[1]) ?>">
                <span><?= e($slide[2]) ?></span>
            </a>
        <?php endforeach; ?>
        <div class="reel-dots">
            <?php foreach ($slides as $index => $slide): ?>
                <button type="button" class="<?= $index === 0 ? 'is-on' : '' ?>" data-reel-go="<?= $index ?>" aria-label="Show slide <?= $index + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="brand-grid">
        <?php foreach ($brands as $brand): ?>
            <a class="brand-card rise" href="<?= e(base_url('shop.php?brand=' . urlencode($brand['slug']))) ?>">
                <?= brand_wordmark($brand['slug'], $brand['name']) ?>
                <span>
                    <p class="eyebrow"><?= e($brand['place']) ?> · <?= count($brand['codes']) ?> pieces</p>
                    <h2><?= e($brand['name']) ?></h2>
                    <p class="brand-line"><?= e($brand['line']) ?></p>
                    <p class="mb-0"><?= e($brand['about']) ?></p>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
