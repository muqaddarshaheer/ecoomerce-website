<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'About';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <section class="about-hero">
        <div>
            <p class="eyebrow">Karachi · since the first parcel</p>
            <h1>A lane for paper, gifts and the small things people keep.</h1>
            <p class="lead">Paperlane is a student shop. Greeting cards, files, bags, jewellery and everyday accessories sit in one catalogue. The prices and the stock come from the shop database, and an administrator is the person who changes them.</p>
        </div>
        <div class="about-mark" aria-hidden="true">
            <span>P</span>
            <small>18 Lakeview Road</small>
        </div>
    </section>

    <section class="about-points">
        <article>
            <p class="eyebrow">01</p>
            <h2>Look without an account</h2>
            <p>Search, filter and open a product before you register. The cart can wait in the browser until you decide to sign in.</p>
        </article>
        <article>
            <p class="eyebrow">02</p>
            <h2>Order in your own name</h2>
            <p>A customer account keeps the cart, the address and the orders. Card and cheque parcels stay put until payment is cleared. Cash on delivery is payable when the parcel arrives.</p>
        </article>
        <article>
            <p class="eyebrow">03</p>
            <h2>Staff stay in their lane</h2>
            <p>Employees pack confirmed orders. They do not change prices, add catalogue items or create other staff. Administrators do that from a separate login.</p>
        </article>
    </section>

    <section class="about-visit">
        <div>
            <p class="eyebrow">The counter</p>
            <h2>18 Lakeview Road, Karachi</h2>
            <p>Cancel from your order page before the parcel is dispatched. After delivery, a return or a replacement can be asked for within seven days.</p>
        </div>
        <button class="btn btn-clay" type="button" data-open-feedback>Leave a note</button>
    </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
