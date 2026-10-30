</main>
<footer class="site-footer">
    <div class="container">
        <div class="footer-top">
            <div>
                <h2><?= e(APP_NAME) ?></h2>
                <p>Greeting cards, stationery, bags and small gifts. Prices and stock on this site come from the shop database.</p>
            </div>
            <div class="social-row">
                <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 9h3V6h-3c-1.7 0-3 1.3-3 3v2H8v3h3v7h3v-7h3l1-3h-4V9c0-.6.4-1 1-1z"/></svg>
                </a>
                <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 3h8a5 5 0 0 1 5 5v8a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5V8a5 5 0 0 1 5-5zm8 2H8a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3zm-4 3.2A3.8 3.8 0 1 1 8.2 12 3.8 3.8 0 0 1 12 8.2zm0 2A1.8 1.8 0 1 0 13.8 12 1.8 1.8 0 0 0 12 10.2zM17.2 7.1a1 1 0 1 1-1 1 1 1 0 0 1 1-1z"/></svg>
                </a>
                <a href="https://wa.me/923000000000" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3a9 9 0 0 0-7.7 13.6L3 21l4.6-1.2A9 9 0 1 0 12 3zm5 12.4c-.2.6-1.2 1.1-1.7 1.2-.4.1-.9.1-1.5-.1-.3-.1-.7-.3-1.3-.5-2.2-1-3.6-3.2-3.7-3.3-.1-.2-.9-1.2-.9-2.3s.6-1.6.8-1.8.4-.3.6-.3h.4c.1 0 .3 0 .5.4.2.5.6 1.6.7 1.7.1.1.1.3 0 .4l-.3.4c-.1.1-.2.3-.1.4.2.3.7 1.1 1.5 1.8.9.8 1.7 1 1.9 1.1.2.1.4.1.5-.1l.4-.5c.1-.2.3-.1.5-.1l1.6.8c.2.1.3.2.4.3.1.3 0 .7-.2 1.1z"/></svg>
                </a>
            </div>
        </div>
        <div class="footer-grid">
            <div>
                <h2>Shop</h2>
                <ul>
                    <li><a href="<?= e(base_url('shop.php')) ?>">All products</a></li>
                    <li><a href="<?= e(base_url('brands.php')) ?>">Brands</a></li>
                    <li><a href="<?= e(base_url('cart.php')) ?>">Cart</a></li>
                    <li><a href="<?= e(base_url('about.php')) ?>">About</a></li>
                </ul>
            </div>
            <div>
                <h2>Help</h2>
                <ul>
                    <li><button class="footer-agent" type="button" data-open-feedback>Feedback</button></li>
                    <li><a href="<?= e(base_url('auth/register.php')) ?>">Create an account</a></li>
                    <li><a href="<?= e(base_url('auth/login.php')) ?>">Log in</a></li>
                </ul>
            </div>
            <div>
                <h2>Visit</h2>
                <p>18 Lakeview Road, Karachi</p>
                <p>Card, cheque and cash on delivery. Free delivery over <?= e(money(FREE_SHIPPING_OVER)) ?>.</p>
            </div>
        </div>
        <p class="fine-print">© <?= date('Y') ?> <?= e(APP_NAME) ?>. Academic shopping-cart project. Card payment on this site is simulated.</p>
    </div>
</footer>
<?php
$noteUser = $user ?? current_user();
$tabScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
$tabAccount = base_url('auth/login.php');
if ($noteUser && ($noteUser['role'] ?? '') === 'customer') {
    $tabAccount = base_url('customer/orders.php');
} elseif ($noteUser && ($noteUser['role'] ?? '') === 'admin') {
    $tabAccount = base_url('admin/index.php');
} elseif ($noteUser && ($noteUser['role'] ?? '') === 'employee') {
    $tabAccount = base_url('employee/index.php');
}
?>
<nav class="app-tab" aria-label="Shop">
    <a class="<?= $tabScript === 'index.php' ? 'is-on' : '' ?>" href="<?= e(base_url('index.php')) ?>">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-9.5z"/></svg>
        Home
    </a>
    <a class="<?= $tabScript === 'shop.php' ? 'is-on' : '' ?>" href="<?= e(base_url('shop.php')) ?>">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 7h15l-1.5 9h-12L6 7zm0 0L5 4H2m7 16a1.2 1.2 0 1 0 0.01 0M16 20a1.2 1.2 0 1 0 0.01 0"/></svg>
        Shop
    </a>
    <button type="button" data-open-nav>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        Menu
    </button>
    <a class="<?= $tabScript === 'cart.php' ? 'is-on' : '' ?>" href="<?= e(base_url('cart.php')) ?>">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.5 7h13l-1.4 8.2a1 1 0 0 1-1 .8H8.2a1 1 0 0 1-1-.8L5.6 4.8H3"/><circle cx="9" cy="20" r="1.2"/><circle cx="17" cy="20" r="1.2"/></svg>
        Cart
    </a>
    <a href="<?= e($tabAccount) ?>">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.2"/><path d="M5 19.2c1.4-3 3.8-4.4 7-4.4s5.6 1.4 7 4.4"/></svg>
        Account
    </a>
</nav>
<div class="note-pop" id="feedback-pop" hidden>
    <button type="button" class="quick-backdrop" data-feedback-close aria-label="Close"></button>
    <form class="note-sheet note-split" method="post" action="<?= e(base_url('feedback.php')) ?>" role="dialog" aria-modal="true" aria-labelledby="feedback-title">
        <button type="button" class="quick-close" data-feedback-close aria-label="Close">×</button>
        <img class="note-photo" src="<?= e(base_url('assets/images/look/lahore-scarf.jpg')) ?>" alt="">
        <div>
            <p class="eyebrow">A note for the shop</p>
            <h2 id="feedback-title">How was Paperlane?</h2>
            <p>Tell us about an order, a product or the shop. An administrator reads these notes.</p>
            <?= csrf_field() ?>
            <div class="hp"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
            <label class="form-label" for="fb-name">Name</label>
            <input class="form-control mb-2" id="fb-name" name="name" value="<?= e($noteUser['full_name'] ?? '') ?>" required>
            <label class="form-label" for="fb-email">Email</label>
            <input class="form-control mb-2" id="fb-email" type="email" name="email" value="<?= e($noteUser['email'] ?? '') ?>" required>
            <label class="form-label" for="fb-subject">Subject</label>
            <input class="form-control mb-2" id="fb-subject" name="subject" required>
            <p class="form-label mb-1" id="fb-rating-label">Rating</p>
            <div class="star-row" role="radiogroup" aria-labelledby="fb-rating-label">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <label class="star-pick"><input type="radio" name="rating" value="<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?>><span><?= $i ?></span></label>
                <?php endfor; ?>
            </div>
            <label class="form-label" for="fb-message">Message</label>
            <textarea class="form-control mb-3" id="fb-message" name="message" rows="4" required></textarea>
            <button class="btn btn-clay" type="submit">Send feedback</button>
        </div>
    </form>
</div>
<div class="quick-view" id="quick-view" hidden>
    <button type="button" class="quick-backdrop" data-quick-close aria-label="Close"></button>
    <div class="quick-panel" role="dialog" aria-modal="true" aria-labelledby="quick-title">
        <button type="button" class="quick-close" data-quick-close aria-label="Close">×</button>
        <img id="quick-image" alt="">
        <div>
            <p class="eyebrow" id="quick-code"></p>
            <h2 id="quick-title"></h2>
            <p class="price fs-4" id="quick-price"></p>
            <p id="quick-stock"></p>
            <p id="quick-blurb"></p>
            <p class="small" id="quick-warranty"></p>
            <div id="quick-note" class="alert d-none" role="status"></div>
            <form id="quick-cart" method="post" action="<?= e(base_url('cart.php')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_code" id="quick-product-code" value="">
                <input type="hidden" name="back" value="shop.php">
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <div class="qty-box">
                        <button type="button" data-qty="-1" aria-label="Decrease quantity">−</button>
                        <input type="number" name="quantity" id="quick-qty" value="1" min="1" max="20" aria-label="Quantity">
                        <button type="button" data-qty="1" aria-label="Increase quantity">+</button>
                    </div>
                    <button class="btn btn-clay" id="quick-add" type="submit">Add to cart</button>
                </div>
            </form>
            <a class="btn btn-outline-dark mt-2" id="quick-details" href="#">Full details</a>
        </div>
    </div>
</div>
<?php
$agentFaqs = [];
try {
    $agentFaqs = db()->query("SELECT question, answer FROM faqs WHERE status = 'active' ORDER BY sort_order, id")->fetchAll();
} catch (Throwable $e) {
    $agentFaqs = [];
}
?>
<div class="agent-dock">
    <button class="agent-hello" type="button" data-open-agent>How can I help you?</button>
    <button class="agent-launcher" type="button" data-open-agent aria-label="How can I help you?">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 3l1.4 4.2L18 8.5l-4.6 1.3L12 14l-1.4-4.2L6 8.5l4.6-1.3L12 3z"/>
            <path d="M18 13l.7 2 2 .6-2 .6-.7 2-.7-2-2-.6 2-.6.7-2z"/>
            <path d="M6.5 14l.5 1.5L8.5 16l-1.5.5L6.5 18l-.5-1.5L4.5 16l1.5-.5.5-1.5z"/>
        </svg>
    </button>
</div>
<section class="agent-panel" id="agent-panel" hidden>
    <div class="agent-head">
        <img src="<?= e(base_url('assets/images/logo.svg')) ?>" alt="">
        <div>
            <strong>How can I help you?</strong>
            <span>Paperlane shop answers</span>
        </div>
        <button type="button" class="agent-close" data-agent-close aria-label="Close">×</button>
    </div>
    <div class="agent-log" id="agent-log"></div>
    <div class="agent-pick-wrap">
        <label class="visually-hidden" for="agent-pick">Suggested questions</label>
        <select id="agent-pick" class="form-select">
            <option value="">Suggested questions</option>
        </select>
    </div>
    <form class="agent-form" id="agent-form">
        <label class="visually-hidden" for="agent-input">Ask the agent</label>
        <input id="agent-input" type="text" maxlength="240" placeholder="Ask about delivery, payment, returns…" autocomplete="off">
        <button class="btn btn-clay" type="submit">Send</button>
    </form>
</section>
<script type="application/json" id="agent-faqs"><?= json_encode(array_map(static function (array $row): array {
    return ['q' => $row['question'], 'a' => $row['answer']];
}, $agentFaqs), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
<?php page_scripts(); ?>
</body>
</html>
