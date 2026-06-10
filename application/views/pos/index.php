<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS — Bulesi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root { --accent: #e94560; --dark: #12121f; }
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; background: #f2f4f8; font-size: .88rem; }

        /* ── Top bar ── */
        #pos-topbar {
            background: var(--dark); color: #fff;
            height: 50px; display: flex; align-items: center; justify-content: space-between;
            padding: 0 20px; position: fixed; top: 0; left: 0; right: 0; z-index: 100;
        }
        #pos-topbar .brand { font-weight: 700; font-size: 1rem; display: flex; align-items: center; gap: 8px; }
        #pos-topbar .brand i { color: var(--accent); }

        /* ── Layout ── */
        #pos-wrap { display: flex; height: 100vh; padding-top: 50px; }
        #products-panel { flex: 1; overflow-y: auto; padding: 16px; }
        #cart-panel {
            width: 370px; min-width: 330px; background: #fff;
            border-left: 1px solid #e2e6ea;
            display: flex; flex-direction: column; height: 100%;
            overflow: hidden;
        }

        /* ── Search & Categories ── */
        #search-wrap { position: sticky; top: 0; background: #f2f4f8; padding-bottom: 10px; z-index: 10; }
        #search-input { border-radius: 8px; }
        .cat-tab {
            display: inline-block; padding: 5px 13px; border-radius: 20px;
            background: #e9ecef; color: #555; font-size: .78rem; cursor: pointer;
            margin: 0 4px 6px 0; border: none; transition: all .15s;
            white-space: nowrap;
        }
        .cat-tab.active, .cat-tab:hover { background: var(--accent); color: #fff; }

        /* ── Product grid ── */
        #product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
        .prod-card {
            background: #fff; border-radius: 10px; padding: 14px 12px;
            cursor: pointer; border: 2px solid transparent;
            box-shadow: 0 1px 6px rgba(0,0,0,.07); transition: all .15s;
            display: flex; flex-direction: column;
        }
        .prod-card:hover { border-color: var(--accent); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(233,69,96,.18); }
        .prod-card.out-of-stock { opacity: .45; cursor: not-allowed; }
        .prod-name { font-weight: 600; font-size: .83rem; color: #1a1a2e; line-height: 1.3; margin-bottom: 4px; }
        .prod-code { font-size: .7rem; color: #aaa; margin-bottom: 6px; }
        .prod-price { font-size: 1rem; font-weight: 700; color: var(--accent); margin-top: auto; }
        .prod-stock { font-size: .7rem; color: #6c757d; margin-top: 3px; }
        .prod-stock.low { color: #f0932b; }
        .prod-stock.zero { color: #e74c3c; }
        .no-items { color: #aaa; padding: 40px 0; text-align: center; }

        /* ── Cart panel ── */
        #cart-header {
            padding: 14px 16px; border-bottom: 1px solid #f0f2f5;
            font-weight: 700; font-size: .95rem; display: flex; align-items: center; gap: 8px;
        }
        #cart-header .badge { background: var(--accent); font-size: .7rem; }
        #cart-items { flex: 1; overflow-y: auto; padding: 8px 12px; }
        .cart-empty { text-align: center; color: #bbb; padding: 40px 0; font-size: .85rem; }
        .cart-row {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 0; border-bottom: 1px solid #f5f5f5;
        }
        .cart-row:last-child { border-bottom: none; }
        .cart-item-name { flex: 1; font-weight: 600; font-size: .82rem; color: #1a1a2e; }
        .cart-item-price { font-size: .78rem; color: #888; }
        .qty-ctrl { display: flex; align-items: center; gap: 4px; }
        .qty-btn {
            width: 26px; height: 26px; border: 1px solid #dee2e6; background: #f8f9fa;
            border-radius: 6px; cursor: pointer; font-size: .8rem; display: flex;
            align-items: center; justify-content: center; transition: all .1s;
        }
        .qty-btn:hover { background: var(--accent); color: #fff; border-color: var(--accent); }
        .qty-val { width: 28px; text-align: center; font-weight: 600; font-size: .83rem; }
        .cart-line-total { font-weight: 700; font-size: .83rem; min-width: 60px; text-align: right; }
        .cart-remove { color: #ccc; cursor: pointer; font-size: .9rem; }
        .cart-remove:hover { color: var(--accent); }

        /* ── Cart footer ── */
        #cart-footer { padding: 12px 16px; border-top: 1px solid #e9ecef; }
        .total-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
        .total-row .lbl { color: #888; font-size: .82rem; }
        .total-row .val { font-weight: 600; }
        .total-row.grand { border-top: 2px solid #e9ecef; padding-top: 8px; margin-top: 6px; }
        .total-row.grand .val { font-size: 1.15rem; color: var(--accent); font-weight: 800; }
        #btn-checkout {
            background: var(--accent); color: #fff; border: none; width: 100%;
            padding: 11px; border-radius: 8px; font-weight: 700; font-size: .95rem;
            margin-top: 10px; cursor: pointer; transition: opacity .15s;
        }
        #btn-checkout:hover { opacity: .88; }
        #btn-checkout:disabled { opacity: .5; cursor: not-allowed; }
        #btn-clear {
            background: none; border: 1px solid #dee2e6; color: #888;
            width: 100%; padding: 7px; border-radius: 8px; font-size: .82rem;
            margin-top: 6px; cursor: pointer;
        }
        #btn-clear:hover { background: #f8d7da; border-color: var(--accent); color: var(--accent); }

        /* ── Checkout modal ── */
        #checkout-modal .modal-header { background: var(--dark); color: #fff; }
        #checkout-modal .modal-header .btn-close { filter: invert(1); }

        /* ── Success modal ── */
        #success-modal .receipt { background: #f8f9fa; border-radius: 8px; padding: 14px; font-family: monospace; font-size: .8rem; }
        #btn-print { background: #1a1a2e; color: #fff; border: none; border-radius: 8px; padding: 9px 20px; font-weight: 600; font-size: .88rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
        #btn-print:hover { background: #2d2d44; }

        /* ── Loading overlay ── */
        #loading-overlay {
            position: fixed; inset: 0; background: rgba(18,18,31,.7);
            display: flex; align-items: center; justify-content: center;
            z-index: 200; flex-direction: column; gap: 14px; color: #fff;
        }
        #loading-overlay.hidden { display: none; }

        @media (max-width: 768px) {
            #pos-wrap { flex-direction: column; }
            #cart-panel { width: 100%; min-width: unset; height: 45vh; border-left: none; border-top: 1px solid #e2e6ea; }
            #products-panel { height: 55vh; }
            #product-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 8px; }
        }
    </style>
</head>
<body>

<!-- Loading overlay -->
<div id="loading-overlay">
    <div class="spinner-border text-light" style="width:2.5rem;height:2.5rem"></div>
    <div style="font-size:.9rem;opacity:.8">Loading stock…</div>
</div>

<!-- Top bar -->
<div id="pos-topbar">
    <div class="brand">
        <i class="bi bi-hammer"></i> Bulesi POS
    </div>
    <div class="d-flex align-items-center gap-3">
        <span style="color:rgba(255,255,255,.5);font-size:.8rem">
            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($user['username']) ?>
            <span style="color:rgba(255,255,255,.3)">&bull; Cashier</span>
        </span>
        <a href="<?= base_url('pos/orders') ?>" class="btn btn-sm btn-outline-light" style="font-size:.75rem;padding:3px 10px">
            <i class="bi bi-clock-history"></i> Orders
        </a>
        <a href="<?= base_url('auth/logout') ?>" class="btn btn-sm btn-outline-light" style="font-size:.75rem;padding:3px 10px">
            <i class="bi bi-box-arrow-right"></i> Sign Out
        </a>
    </div>
</div>

<!-- Main layout -->
<div id="pos-wrap">

    <!-- Products panel -->
    <div id="products-panel">
        <div id="search-wrap">
            <div class="input-group mb-2">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="search-input" class="form-control border-start-0" placeholder="Search items by name or code…">
            </div>
            <div id="cat-tabs">
                <button class="cat-tab active" data-cat="all">All</button>
            </div>
        </div>
        <div id="product-grid"></div>
    </div>

    <!-- Cart panel -->
    <div id="cart-panel">
        <div id="cart-header">
            <i class="bi bi-cart3"></i> Cart
            <span class="badge rounded-pill ms-1" id="cart-count">0</span>
        </div>
        <div id="cart-items">
            <div class="cart-empty" id="cart-empty-msg">
                <i class="bi bi-cart-x d-block mb-2" style="font-size:2rem"></i>
                Cart is empty
            </div>
            <div id="cart-rows"></div>
        </div>
        <div id="cart-footer">
            <div class="total-row"><span class="lbl">Subtotal</span><span class="val" id="disp-subtotal">R 0.00</span></div>
            <div class="total-row"><span class="lbl">VAT (15%)</span><span class="val" id="disp-vat">R 0.00</span></div>
            <div class="total-row grand"><span class="lbl fw-bold text-dark">TOTAL</span><span class="val" id="disp-total">R 0.00</span></div>
            <button id="btn-checkout" disabled><i class="bi bi-bag-check-fill me-1"></i>Checkout</button>
            <button id="btn-clear"><i class="bi bi-trash me-1"></i>Clear Cart</button>
        </div>
    </div>
</div>

<!-- Checkout modal -->
<div class="modal fade" id="checkout-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-bag-check-fill me-2"></i>Complete Sale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Customer Name <small class="text-muted fw-normal">(optional)</small></label>
                    <input type="text" id="customer-name" class="form-control" placeholder="Walk-in Customer">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Notes <small class="text-muted fw-normal">(optional)</small></label>
                    <textarea id="order-notes" class="form-control" rows="2" placeholder="Any special instructions…"></textarea>
                </div>
                <div class="bg-light rounded p-3">
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Subtotal</span><span id="mo-subtotal">R 0.00</span></div>
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">VAT (15%)</span><span id="mo-vat">R 0.00</span></div>
                    <div class="d-flex justify-content-between fw-bold" style="border-top:1px solid #dee2e6;padding-top:8px;margin-top:4px">
                        <span>Total</span><span id="mo-total" style="color:var(--accent);font-size:1.1rem">R 0.00</span>
                    </div>
                </div>
                <div id="checkout-error" class="alert alert-danger mt-3 py-2 d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btn-place-order" class="btn text-white fw-bold px-4" style="background:var(--accent)">
                    <i class="bi bi-check2-circle me-1"></i>Place Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success modal -->
<div class="modal fade" id="success-modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div style="font-size:3rem;color:#2ecc71"><i class="bi bi-check-circle-fill"></i></div>
                <h5 class="mt-2 mb-1">Order Placed!</h5>
                <p class="text-muted mb-3" id="success-quote-number"></p>
                <div class="receipt" id="receipt-lines"></div>
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <button id="btn-print">
                        <i class="bi bi-printer-fill"></i> Print Receipt
                    </button>
                    <button class="btn text-white fw-bold px-4" style="background:var(--accent)" id="btn-new-sale">
                        <i class="bi bi-plus-circle me-1"></i>New Sale
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js"></script>
<script src="<?= base_url('assets/js/pos-print.js') ?>"></script>
<script>
const API_TOKEN = '<?= htmlspecialchars($api_token, ENT_QUOTES) ?>';
const BASE_URL  = '<?= base_url() ?>';

let ALL_ITEMS   = [];
let cart        = [];
let activecat   = 'all';
let searchQuery = '';

const fmt = n => 'R ' + parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

// ── Load stock from API ───────────────────────────────────────────────
async function loadStock() {
    document.getElementById('loading-overlay').classList.remove('hidden');
    try {
        const res  = await fetch(BASE_URL + 'api/stock', {
            headers: { 'Authorization': 'Bearer ' + API_TOKEN }
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Failed to load stock');

        // Flatten categories → items, attaching category metadata to each item
        ALL_ITEMS = data.data.flatMap(cat =>
            cat.items.map(item => Object.assign({}, item, {
                category_id:   cat.id,
                category_name: cat.name,
            }))
        );

        renderCatTabs(data.data);
        renderGrid();
    } catch (e) {
        document.getElementById('product-grid').innerHTML =
            '<div class="no-items"><i class="bi bi-exclamation-triangle d-block mb-2" style="font-size:2rem;color:var(--accent)"></i>' +
            'Could not load stock.<br><small>' + e.message + '</small></div>';
    } finally {
        document.getElementById('loading-overlay').classList.add('hidden');
    }
}

// ── Category tabs ─────────────────────────────────────────────────────
function renderCatTabs(categories) {
    const wrap = document.getElementById('cat-tabs');
    const extra = categories.map(cat =>
        `<button class="cat-tab" data-cat="${cat.id}">${cat.name}</button>`
    ).join('');
    wrap.innerHTML = '<button class="cat-tab active" data-cat="all">All</button>' + extra;
}

// ── Render product grid ───────────────────────────────────────────────
function renderGrid() {
    const q    = searchQuery.toLowerCase();
    const grid = document.getElementById('product-grid');

    const filtered = ALL_ITEMS.filter(item => {
        const matchCat    = activecat === 'all' || item.category_id == activecat;
        const matchSearch = !q || item.name.toLowerCase().includes(q) || (item.code || '').toLowerCase().includes(q);
        return matchCat && matchSearch;
    });

    if (filtered.length === 0) {
        grid.innerHTML = '<div class="no-items"><i class="bi bi-search d-block mb-2" style="font-size:2rem"></i>No items found</div>';
        return;
    }

    grid.innerHTML = filtered.map(item => {
        const stockClass = item.quantity_on_hand <= 0 ? 'zero' : item.quantity_on_hand <= 5 ? 'low' : '';
        const outClass   = item.quantity_on_hand <= 0 ? 'out-of-stock' : '';
        const stockText  = item.quantity_on_hand <= 0 ? 'Out of stock' : `${item.quantity_on_hand} ${item.unit || ''} in stock`;
        return `
        <div class="prod-card ${outClass}" onclick="addToCart(${item.id})" title="${item.description || item.name}">
            <div class="prod-code">${item.code || ''}</div>
            <div class="prod-name">${item.name}</div>
            <div class="prod-price">${fmt(item.unit_price)}</div>
            <div class="prod-stock ${stockClass}">${stockText}</div>
        </div>`;
    }).join('');
}

// ── Cart logic ────────────────────────────────────────────────────────
function addToCart(itemId) {
    const item = ALL_ITEMS.find(i => i.id === itemId);
    if (!item || item.quantity_on_hand <= 0) return;
    const existing = cart.find(r => r.item.id === itemId);
    if (existing) existing.quantity++;
    else cart.push({ item, quantity: 1 });
    renderCart();
}

function updateQty(itemId, delta) {
    const row = cart.find(r => r.item.id === itemId);
    if (!row) return;
    row.quantity += delta;
    if (row.quantity <= 0) cart = cart.filter(r => r.item.id !== itemId);
    renderCart();
}

function removeFromCart(itemId) {
    cart = cart.filter(r => r.item.id !== itemId);
    renderCart();
}

function clearCart() {
    cart = [];
    renderCart();
}

function cartTotals() {
    const subtotal = cart.reduce((s, r) => s + r.item.unit_price * r.quantity, 0);
    const vat      = subtotal * 0.15;
    return { subtotal: round2(subtotal), vat: round2(vat), total: round2(subtotal + vat) };
}

function round2(n) { return Math.round(n * 100) / 100; }

function renderCart() {
    const rows    = document.getElementById('cart-rows');
    const emptyMsg = document.getElementById('cart-empty-msg');
    const count   = document.getElementById('cart-count');
    const btnChk  = document.getElementById('btn-checkout');
    const { subtotal, vat, total } = cartTotals();

    count.textContent = cart.reduce((s, r) => s + r.quantity, 0);
    document.getElementById('disp-subtotal').textContent = fmt(subtotal);
    document.getElementById('disp-vat').textContent      = fmt(vat);
    document.getElementById('disp-total').textContent    = fmt(total);
    btnChk.disabled = cart.length === 0;

    emptyMsg.style.display = cart.length === 0 ? '' : 'none';

    rows.innerHTML = cart.map(r => `
        <div class="cart-row">
            <div class="flex-shrink-0">
                <div class="cart-item-name">${r.item.name}</div>
                <div class="cart-item-price">${fmt(r.item.unit_price)} / ${r.item.unit || 'each'}</div>
            </div>
            <div class="qty-ctrl ms-auto">
                <button class="qty-btn" onclick="updateQty(${r.item.id}, -1)">−</button>
                <span class="qty-val">${r.quantity}</span>
                <button class="qty-btn" onclick="updateQty(${r.item.id}, 1)">+</button>
            </div>
            <div class="cart-line-total">${fmt(r.item.unit_price * r.quantity)}</div>
            <span class="cart-remove" onclick="removeFromCart(${r.item.id})"><i class="bi bi-x-lg"></i></span>
        </div>
    `).join('');
}

// ── Checkout ──────────────────────────────────────────────────────────
function openCheckout() {
    const { subtotal, vat, total } = cartTotals();
    document.getElementById('mo-subtotal').textContent = fmt(subtotal);
    document.getElementById('mo-vat').textContent      = fmt(vat);
    document.getElementById('mo-total').textContent    = fmt(total);
    document.getElementById('checkout-error').classList.add('d-none');
    document.getElementById('customer-name').value = '';
    document.getElementById('order-notes').value   = '';
    new bootstrap.Modal(document.getElementById('checkout-modal')).show();
}

async function placeOrder() {
    const btn = document.getElementById('btn-place-order');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing…';

    const payload = {
        customer_name: document.getElementById('customer-name').value.trim() || 'Walk-in Customer',
        notes:         document.getElementById('order-notes').value.trim(),
        items: cart.map(r => ({
            name:       r.item.name,
            unit:       r.item.unit || 'each',
            quantity:   r.quantity,
            unit_price: r.item.unit_price,
        })),
    };

    try {
        const res = await fetch(BASE_URL + 'pos/place_order', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const data = await res.json();

        if (!data.success) {
            document.getElementById('checkout-error').textContent = data.error || 'Order failed.';
            document.getElementById('checkout-error').classList.remove('d-none');
            return;
        }

        bootstrap.Modal.getInstance(document.getElementById('checkout-modal')).hide();
        showSuccess(data);

    } catch (e) {
        document.getElementById('checkout-error').textContent = 'Network error. Please try again.';
        document.getElementById('checkout-error').classList.remove('d-none');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i>Place Order';
    }
}

function showSuccess(data) {
    lastOrder = {
        quoteNumber: data.quote_number,
        subtotal:    data.subtotal,
        vatAmount:   data.vat_amount,
        total:       data.total,
        customer:    document.getElementById('customer-name').value.trim(),
        notes:       document.getElementById('order-notes').value.trim(),
        createdAt:   new Date().toISOString(),
    };

    document.getElementById('success-quote-number').textContent = data.quote_number;
    const lines = cart.map(r => `${r.item.name.padEnd(22).substring(0,22)} x${r.quantity}  ${fmt(r.item.unit_price * r.quantity)}`).join('\n');
    document.getElementById('receipt-lines').textContent =
        `${lines}\n${'-'.repeat(38)}\nSubtotal           ${fmt(data.subtotal)}\nVAT (15%)          ${fmt(data.vat_amount)}\nTOTAL              ${fmt(data.total)}`;
    new bootstrap.Modal(document.getElementById('success-modal')).show();
}

// ── Receipt print ─────────────────────────────────────────────────────
let lastOrder = null;

function printReceipt() {
    if (!lastOrder) return;
    const items = cart.map(r => ({
        name:       r.item.name,
        unit:       r.item.unit || 'each',
        quantity:   r.quantity,
        unit_price: r.item.unit_price,
        line_total: r.item.unit_price * r.quantity,
    }));
    PosPrint.printReceipt(lastOrder, items);
}

// ── Event bindings ────────────────────────────────────────────────────
document.getElementById('search-input').addEventListener('input', e => {
    searchQuery = e.target.value;
    renderGrid();
});

document.getElementById('cat-tabs').addEventListener('click', e => {
    const btn = e.target.closest('.cat-tab');
    if (!btn) return;
    document.querySelectorAll('.cat-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activecat = btn.dataset.cat;
    renderGrid();
});

document.getElementById('btn-checkout').addEventListener('click', openCheckout);
document.getElementById('btn-clear').addEventListener('click', clearCart);
document.getElementById('btn-place-order').addEventListener('click', placeOrder);
document.getElementById('btn-print').addEventListener('click', printReceipt);
document.getElementById('btn-new-sale').addEventListener('click', () => {
    bootstrap.Modal.getInstance(document.getElementById('success-modal')).hide();
    clearCart();
});

// ── Boot ──────────────────────────────────────────────────────────────
renderCart();
loadStock();
</script>
</body>
</html>
