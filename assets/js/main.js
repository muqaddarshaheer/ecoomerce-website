$(function () {
    function showNote(message, ok) {
        var box = $('#ajax-note');
        if (!box.length) {
            return;
        }
        box.removeClass('d-none alert-success alert-danger')
            .addClass(ok ? 'alert-success' : 'alert-danger')
            .text(message);
    }

    function openQuick(button) {
        var box = $('#quick-view');
        if (!box.length) {
            return;
        }
        var stock = parseInt(button.attr('data-stock'), 10) || 0;
        var image = button.attr('data-image') || '';
        $('#quick-title').text(button.attr('data-name') || '');
        $('#quick-code').text('Product ID ' + (button.attr('data-code') || ''));
        $('#quick-price').text(button.attr('data-price') || '');
        $('#quick-blurb').text(button.attr('data-blurb') || '');
        $('#quick-warranty').text(button.attr('data-warranty') ? 'Warranty: ' + button.attr('data-warranty') : '');
        $('#quick-stock').text(stock > 0 ? stock + ' in stock' : 'Out of stock');
        $('#quick-product-code').val(button.attr('data-code') || '');
        $('#quick-details').attr('href', button.attr('data-url') || '#');
        $('#quick-qty').val(1).attr('max', Math.max(1, Math.min(20, stock)));
        $('#quick-add').prop('disabled', stock < 1);
        $('#quick-cart').toggle(stock > 0);
        if (image) {
            $('#quick-image').attr('src', image).attr('alt', button.attr('data-name') || '').show();
        } else {
            $('#quick-image').hide();
        }
        $('#quick-note').addClass('d-none').text('');
        box.removeAttr('hidden');
        $('body').css('overflow', 'hidden');
    }

    $(document).on('click', '.quick-open', function () {
        openQuick($(this));
    });

    $(document).on('click', '[data-quick-close]', function () {
        $('#quick-view').attr('hidden', 'hidden');
        $('body').css('overflow', '');
    });

    $(document).on('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }
        if (!$('#quick-view').is('[hidden]')) {
            $('#quick-view').attr('hidden', 'hidden');
            $('body').css('overflow', '');
            return;
        }
        if (!$('#feedback-pop').is('[hidden]')) {
            $('#feedback-pop').attr('hidden', 'hidden');
            $('body').css('overflow', '');
            return;
        }
        $('#agent-panel').attr('hidden', 'hidden');
        $('.agent-hello').removeAttr('hidden');
    });

    $('#quick-cart').on('submit', function (event) {
        event.preventDefault();
        var form = $(this);
        $.post(form.attr('action'), form.serialize() + '&ajax=1')
            .done(function (res) {
                var note = $('#quick-note');
                if (res && res.ok) {
                    $('#cart-count').text(res.count).addClass('bump');
                    setTimeout(function () { $('#cart-count').removeClass('bump'); }, 350);
                    note.removeClass('d-none alert-danger').addClass('alert-success').text(res.message);
                } else {
                    note.removeClass('d-none alert-success').addClass('alert-danger').text((res && res.message) ? res.message : 'Could not add this product.');
                }
            })
            .fail(function () {
                $('#quick-note').removeClass('d-none alert-success').addClass('alert-danger').text('Could not reach the shop.');
            });
    });

    $('#add-cart-form').on('submit', function (event) {
        event.preventDefault();
        var form = $(this);
        $.post(form.attr('action'), form.serialize() + '&ajax=1')
            .done(function (res) {
                if (res && res.ok) {
                    $('#cart-count').text(res.count).addClass('bump');
                    setTimeout(function () { $('#cart-count').removeClass('bump'); }, 350);
                    showNote(res.message, true);
                } else {
                    showNote((res && res.message) ? res.message : 'Could not add this product.', false);
                }
            })
            .fail(function () {
                showNote('Could not reach the shop. The form will be sent normally if you try again without a script blocker.', false);
            });
    });

    $('[data-gallery-src]').on('click', function () {
        var src = $(this).attr('data-gallery-src');
        $('#gallery-main').attr('src', src);
        $('[data-gallery-src]').removeClass('is-selected');
        $(this).addClass('is-selected');
    });

    function syncPay() {
        var value = $('input[name="delivery_type"]:checked').val() || '1';
        $('[data-pay-panel]').each(function () {
            $(this).toggleClass('d-none', $(this).attr('data-pay-panel') !== value);
        });
    }

    $('input[name="delivery_type"]').on('change', syncPay);
    syncPay();

    function syncAddress() {
        var box = $('#new-address');
        if (!box.length) {
            return;
        }
        var choice = $('input[name="address_choice"]:checked').val();
        box.toggleClass('d-none', choice !== 'new' && choice !== undefined && $('input[name="address_choice"]').length > 0 && choice !== 'new');
        if (!$('input[name="address_choice"]').length) {
            box.removeClass('d-none');
        }
    }

    $('input[name="address_choice"]').on('change', syncAddress);
    syncAddress();

    $(document).on('click', '[data-qty]', function () {
        var input = $(this).siblings('input[type="number"]');
        if (!input.length) {
            input = $(this).closest('.qty-box').find('input[type="number"]');
        }
        var step = parseInt($(this).attr('data-qty'), 10) || 0;
        var value = (parseInt(input.val(), 10) || 1) + step;
        var min = parseInt(input.attr('min') || '1', 10);
        var max = parseInt(input.attr('max') || '20', 10);
        if (value < min) {
            value = min;
        }
        if (value > max) {
            value = max;
        }
        input.val(value);
    });

    $(document).on('submit', 'form', function (event) {
        var btn = event.originalEvent && event.originalEvent.submitter;
        if (btn && btn.getAttribute('data-confirm') && !window.confirm(btn.getAttribute('data-confirm'))) {
            event.preventDefault();
        }
    });

    $(document).on('click', 'a[data-confirm]', function (event) {
        if (!window.confirm($(this).attr('data-confirm'))) {
            event.preventDefault();
        }
    });

    var agentFaqs = [];
    try {
        agentFaqs = JSON.parse($('#agent-faqs').text() || '[]');
    } catch (ignore) {
        agentFaqs = [];
    }

    function agentWords(text) {
        return (String(text).toLowerCase().match(/[a-z0-9]{3,}/g) || []);
    }

    function agentReply(message) {
        var plain = String(message).trim().toLowerCase();
        var onlyShop = 'I only answer questions about the Paperlane shop: products, delivery, payment, orders, cancellation and returns.';
        if (!plain) {
            return onlyShop;
        }
        if (/^(hi|hello|hey|hy|salam|aoa|assalam|help)$/.test(plain)) {
            return 'Hello. ' + onlyShop;
        }
        var facts = [
            { test: /categor|what do you sell|departments|kinds of product|what you sell/, answer: 'Paperlane sells four kinds of goods: greeting cards and gifts, dolls files and stationery, handbags and wallets, and beauty and accessories.' },
            { test: /\blogin\b|sign in|admin panel|employee panel|my account/, answer: 'Log in has three doors: Customer, Employee and Admin. A password works only on its own door. A new employee stays pending until an administrator approves the account.' }
        ];
        var fact;
        for (fact = 0; fact < facts.length; fact++) {
            if (facts[fact].test.test(plain)) {
                return facts[fact].answer;
            }
        }
        var skip = { the: 1, and: 1, for: 1, you: 1, your: 1, how: 1, what: 1, can: 1, does: 1, did: 1, are: 1, was: 1, who: 1, why: 1, when: 1, where: 1, with: 1, this: 1, that: 1, from: 1, have: 1, has: 1, not: 1, but: 1, about: 1, please: 1, tell: 1, much: 1, many: 1 };
        var asked = agentWords(message).filter(function (word) { return !skip[word]; });
        if (!asked.length) {
            return onlyShop;
        }
        var shopWords = { delivery: 1, shipping: 1, payment: 1, payments: 1, card: 1, cheque: 1, vpp: 1, cash: 1, order: 1, orders: 1, cancel: 1, cancellation: 1, return: 1, returns: 1, refund: 1, replacement: 1, dispatch: 1, dispatched: 1, account: 1, stock: 1, warranty: 1, price: 1, prices: 1, browse: 1, product: 1, products: 1 };
        var best = null;
        var bestScore = 0;
        agentFaqs.forEach(function (item) {
            var answerWords = agentWords(item.a);
            var questionWords = agentWords(item.q);
            var score = 0;
            asked.forEach(function (word) {
                if (questionWords.indexOf(word) !== -1) {
                    score += 8;
                } else if (shopWords[word] && answerWords.indexOf(word) !== -1) {
                    score += 4;
                }
            });
            if (score > bestScore) {
                bestScore = score;
                best = item;
            }
        });
        if (!best || bestScore < 4) {
            return onlyShop;
        }
        return best.a;
    }

    function addBubble(text, who) {
        var log = $('#agent-log');
        $('<p class="agent-bubble"></p>').addClass(who).text(text).appendTo(log);
        if (log[0]) {
            log.scrollTop(log[0].scrollHeight);
        }
    }

    function openAgent() {
        var panel = $('#agent-panel');
        if (!panel.length) {
            return;
        }
        panel.removeAttr('hidden');
        $('.agent-hello').attr('hidden', 'hidden');
        if (!$('#agent-log').children().length) {
            addBubble('Hello. I only answer Paperlane shop questions: products, delivery, payment, orders, cancellation and returns.', 'bot');
            var pick = $('#agent-pick');
            agentFaqs.forEach(function (item) {
                pick.append($('<option></option>').val(item.q).text(item.q));
            });
        }
        $('#agent-input').trigger('focus');
    }

    function askAgent(text) {
        text = String(text || '').trim();
        if (!text) {
            return;
        }
        addBubble(text, 'me');
        addBubble(agentReply(text), 'bot');
    }

    $('#agent-pick').on('change', function () {
        var question = $(this).val();
        if (!question) {
            return;
        }
        askAgent(question);
        $(this).val('');
    });

    $(document).on('click', '[data-open-agent]', function (event) {
        event.preventDefault();
        openAgent();
    });

    $(document).on('click', '[data-agent-close]', function () {
        $('#agent-panel').attr('hidden', 'hidden');
        $('.agent-hello').removeAttr('hidden');
    });

    $('#agent-form').on('submit', function (event) {
        event.preventDefault();
        var input = $('#agent-input');
        askAgent(input.val());
        input.val('');
    });

    $(document).on('click', '[data-door]', function () {
        var door = $(this).attr('data-door');
        $('[data-door]').removeClass('is-on').attr('aria-selected', 'false');
        $(this).addClass('is-on').attr('aria-selected', 'true');
        $('.door-form').attr('hidden', 'hidden');
        $('#door-' + door).removeAttr('hidden');
        $('.gate-sheet').attr('data-role', door);
        if (window.history && window.history.replaceState) {
            var url = new URL(window.location.href);
            url.searchParams.set('role', door);
            window.history.replaceState({}, '', url);
        }
    });

    $(document).on('click', '[data-toggle-pass]', function () {
        var input = $(this).closest('.pass-field').find('input');
        var show = input.attr('type') === 'password';
        input.attr('type', show ? 'text' : 'password');
        $(this).attr('aria-label', show ? 'Hide password' : 'Show password');
    });

    $(document).on('click', '[data-forgot]', function () {
        $(this).closest('form').find('.forgot-note').toggleClass('d-none');
    });

    $(document).on('click', '[data-demo-email]', function () {
        var form = $(this).closest('form');
        form.find('[name=email]').val($(this).attr('data-demo-email'));
        form.find('[name=password]').val($(this).attr('data-demo-pass'));
    });

    if (window.URLSearchParams && new URLSearchParams(window.location.search).get('agent') === '1') {
        openAgent();
    }

    $(document).on('click', '.has-menu > button', function () {
        if (window.matchMedia('(min-width: 992px)').matches) {
            return;
        }
        var item = $(this).parent();
        var open = item.hasClass('is-open');
        $('.has-menu').removeClass('is-open').children('button').attr('aria-expanded', 'false');
        if (!open) {
            item.addClass('is-open');
            $(this).attr('aria-expanded', 'true');
        }
    });

    $(document).on('click', '[data-open-feedback]', function () {
        $('#feedback-pop').removeAttr('hidden');
        $('body').css('overflow', 'hidden');
    });

    $(document).on('click', '[data-feedback-close]', function () {
        $('#feedback-pop').attr('hidden', 'hidden');
        $('body').css('overflow', '');
    });

    $(document).on('click', '[data-open-nav]', function () {
        var toggler = document.querySelector('.navbar-toggler');
        if (toggler) {
            toggler.click();
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    var reel = document.querySelector('[data-reel]');
    if (reel) {
        var reelSlides = reel.querySelectorAll('.reel-slide');
        var reelDots = reel.querySelectorAll('[data-reel-go]');
        var reelIndex = 0;
        var reelTimer = null;
        var showReel = function (next) {
            reelIndex = (next + reelSlides.length) % reelSlides.length;
            reelSlides.forEach(function (slide, index) {
                slide.classList.toggle('is-on', index === reelIndex);
            });
            reelDots.forEach(function (dot, index) {
                dot.classList.toggle('is-on', index === reelIndex);
            });
        };
        var armReel = function () {
            clearInterval(reelTimer);
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            reelTimer = setInterval(function () { showReel(reelIndex + 1); }, 4200);
        };
        reelDots.forEach(function (dot) {
            dot.addEventListener('click', function (event) {
                event.preventDefault();
                showReel(parseInt(dot.getAttribute('data-reel-go'), 10) || 0);
                armReel();
            });
        });
        reel.addEventListener('mouseenter', function () { clearInterval(reelTimer); });
        reel.addEventListener('mouseleave', armReel);
        armReel();
    }

    var hello = document.getElementById('hello-pop');
    if (hello && !window.sessionStorage.getItem('paperlaneHello')) {
        window.setTimeout(function () {
            hello.removeAttribute('hidden');
        }, 700);
    }
    $(document).on('click', '[data-hello-close]', function () {
        window.sessionStorage.setItem('paperlaneHello', '1');
        $('#hello-pop').attr('hidden', 'hidden');
    });

    var giftPop = document.getElementById('gift-pop');
    if (giftPop && !giftPop.hasAttribute('hidden') && hello) {
        hello.setAttribute('hidden', 'hidden');
    }
    $(document).on('click', '[data-gift-open]', function () {
        if (hello) {
            hello.setAttribute('hidden', 'hidden');
        }
        $('#gift-pop').removeAttr('hidden');
    });
    $(document).on('click', '[data-gift-close]', function () {
        $('#gift-pop').attr('hidden', 'hidden');
    });
    var giftSum = document.getElementById('gift-sum');
    var giftQty = document.getElementById('gift-qty');
    if (giftSum && giftQty) {
        var paintGift = function () {
            var unit = parseFloat(giftSum.getAttribute('data-unit')) || 0;
            var ship = parseFloat(giftSum.getAttribute('data-ship')) || 0;
            var freeAt = parseFloat(giftSum.getAttribute('data-free')) || 1500;
            var qty = Math.max(1, parseInt(giftQty.value, 10) || 1);
            var goods = unit * qty;
            var fee = goods >= freeAt ? 0 : ship;
            giftSum.textContent = qty + (qty === 1 ? ' box' : ' boxes') + ' · Rs ' + goods.toFixed(2) + (fee > 0 ? ' plus delivery Rs ' + fee.toFixed(2) : ' with free delivery') + '. Cash on delivery in Karachi.';
        };
        giftQty.addEventListener('input', paintGift);
    }

    var themeButton = document.querySelector('[data-theme-switch]');
    var paintTheme = function () {
        var mode = document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
        if (!themeButton) {
            return;
        }
        var next = mode === 'light' ? 'dark' : 'light';
        var label = themeButton.querySelector('[data-theme-label]');
        themeButton.setAttribute('aria-pressed', mode === 'light' ? 'true' : 'false');
        themeButton.setAttribute('aria-label', next === 'light' ? 'Switch to light mode' : 'Switch to dark mode');
        if (label) {
            label.textContent = next === 'light' ? 'Light' : 'Dark';
        }
    };
    paintTheme();
    if (themeButton) {
        themeButton.addEventListener('click', function () {
            var next = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', next);
            try { localStorage.setItem('paperlaneTheme', next); } catch (e) {}
            paintTheme();
        });
    }

    var dealClocks = document.querySelectorAll('[data-deal-clock]');
    if (dealClocks.length) {
        var paintClocks = function () {
            dealClocks.forEach(function (dealClock) {
                var dealUntil = (parseInt(dealClock.getAttribute('data-until'), 10) || 0) * 1000;
                var left = Math.max(0, dealUntil - Date.now());
                var hours = Math.floor(left / 3600000);
                var minutes = Math.floor((left % 3600000) / 60000);
                var seconds = Math.floor((left % 60000) / 1000);
                var pad = function (n) { return (n < 10 ? '0' : '') + n; };
                dealClock.textContent = pad(hours) + ':' + pad(minutes) + ':' + pad(seconds);
            });
        };
        paintClocks();
        window.setInterval(paintClocks, 1000);
    }

    var brandMenu = document.querySelector('[data-brand-menu]');
    if (brandMenu) {
        var brandFind = brandMenu.querySelector('[data-brand-find]');
        var filterBrands = function () {
            var query = (brandFind.value || '').trim().toLowerCase();
            brandMenu.querySelectorAll('[data-brand-name]').forEach(function (item) {
                var name = item.getAttribute('data-brand-name') || '';
                item.classList.toggle('is-dim', query !== '' && name.indexOf(query) === -1);
            });
        };
        if (brandFind) {
            brandFind.addEventListener('input', filterBrands);
            brandFind.addEventListener('click', function (event) { event.stopPropagation(); });
        }
    }
});
