<?php
// Placeholder message log — replace with DB query once a wa_messages table exists
$sample_log = [
    ['to' => '+27 82 000 1111', 'message' => 'Your quotation QT-0042 has been sent.', 'status' => 'delivered', 'sent_at' => '2026-05-27 09:14'],
    ['to' => '+27 73 555 2222', 'message' => 'Hi, your quote is ready for review.',    'status' => 'read',      'sent_at' => '2026-05-26 16:02'],
    ['to' => '+27 61 333 3333', 'message' => 'Payment reminder: QT-0038 outstanding.',  'status' => 'sent',      'sent_at' => '2026-05-25 11:47'],
    ['to' => '+27 84 777 4444', 'message' => 'Your order is now in progress.',           'status' => 'failed',    'sent_at' => '2026-05-24 08:30'],
];
$status_styles = [
    'delivered' => ['bg' => '#d1e7dd', 'color' => '#0a5132', 'icon' => 'check2-all'],
    'read'      => ['bg' => '#cfe2ff', 'color' => '#0a58ca', 'icon' => 'eye-fill'],
    'sent'      => ['bg' => '#fff3cd', 'color' => '#856404', 'icon' => 'send-fill'],
    'failed'    => ['bg' => '#f8d7da', 'color' => '#842029', 'icon' => 'x-circle-fill'],
];
?>

<style>
    .wa-header-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: #d1e7dd; color: #0a5132;
        border-radius: 20px; padding: 4px 14px; font-size: .78rem; font-weight: 600;
    }
    .wa-header-badge.disconnected { background: #f8d7da; color: #842029; }
    .wa-header-badge .dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: #198754; animation: pulse-dot 1.8s infinite;
    }
    .wa-header-badge.disconnected .dot { background: #dc3545; animation: none; }
    @keyframes pulse-dot {
        0%,100% { opacity: 1; } 50% { opacity: .3; }
    }
    .form-label { font-weight: 500; font-size: .85rem; }
    .section-sep { border-top: 1px solid #f0f2f5; margin: 18px 0 16px; }
    .log-badge { display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:600; }
    .copy-btn { cursor:pointer; }
    .copy-btn:hover { color: var(--accent); }
    textarea.form-control { resize: vertical; min-height: 80px; }
    #charCount { font-size: .75rem; color: #8896a4; }
</style>

<!-- Page title row -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <div style="width:42px;height:42px;border-radius:10px;background:#25d366;display:flex;align-items:center;justify-content:center">
            <i class="bi bi-whatsapp text-white" style="font-size:1.3rem"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold">WhatsApp Integration</h5>
            <span class="text-muted" style="font-size:.8rem">Meta WhatsApp Business Cloud API</span>
        </div>
    </div>
    <span class="wa-header-badge disconnected" id="connectionBadge">
        <span class="dot"></span> Not Connected
    </span>
</div>

<div class="row g-4">

    <!-- LEFT COLUMN: Credentials + Webhook -->
    <div class="col-lg-7">

        <!-- API Credentials -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-key-fill text-warning"></i> API Credentials
            </div>
            <div class="card-body">
                <form action="<?= base_url('whatsapp/save_settings') ?>" method="post" id="settingsForm">
                    <input type="hidden" name="<?= $this->config->item('csrf_token_name') ?>" value="<?= $this->security->get_csrf_hash() ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Phone Number ID</label>
                            <input type="text" name="phone_number_id" class="form-control"
                                   placeholder="e.g. 123456789012345"
                                   value="<?= htmlspecialchars($this->config->item('wa_phone_number_id') ?? '') ?>">
                            <div class="form-text">Found in Meta Business → WhatsApp → API Setup</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Business Account ID (WABA)</label>
                            <input type="text" name="waba_id" class="form-control"
                                   placeholder="e.g. 987654321098765"
                                   value="<?= htmlspecialchars($this->config->item('wa_waba_id') ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Access Token</label>
                            <div class="input-group">
                                <input type="password" name="access_token" id="accessToken" class="form-control"
                                       placeholder="Bearer token from Meta Developer Console"
                                       value="<?= htmlspecialchars($this->config->item('wa_access_token') ?? '') ?>">
                                <button type="button" class="btn btn-outline-secondary" id="toggleToken" title="Show/hide token">
                                    <i class="bi bi-eye" id="toggleTokenIcon"></i>
                                </button>
                            </div>
                            <div class="form-text">Permanent system user token recommended for production</div>
                        </div>
                    </div>

                    <div class="section-sep"></div>
                    <p class="fw-semibold mb-2" style="font-size:.85rem"><i class="bi bi-link-45deg me-1 text-muted"></i>Webhook Configuration</p>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Webhook URL <span class="text-muted fw-normal">(set this in Meta Developer Console)</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="webhookUrl" readonly
                                       value="<?= base_url('whatsapp/webhook') ?>">
                                <button type="button" class="btn btn-outline-secondary copy-btn" onclick="copyField('webhookUrl')" title="Copy">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Verify Token</label>
                            <div class="input-group">
                                <input type="text" name="verify_token" id="verifyToken" class="form-control"
                                       placeholder="Your custom verify token"
                                       value="<?= htmlspecialchars($this->config->item('wa_verify_token') ?? '') ?>">
                                <button type="button" class="btn btn-outline-secondary copy-btn" onclick="copyField('verifyToken')" title="Copy">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">API Version</label>
                            <select name="api_version" class="form-select">
                                <option value="v20.0" selected>v20.0 (recommended)</option>
                                <option value="v19.0">v19.0</option>
                                <option value="v18.0">v18.0</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-4">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bi bi-floppy-fill me-1"></i> Save Settings
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="testConnectionBtn">
                            <i class="bi bi-wifi me-1"></i> Test Connection
                        </button>
                        <span id="testResult" class="text-muted" style="font-size:.82rem"></span>
                    </div>
                </form>
            </div>
        </div>

        <!-- Subscribed Events -->
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-bell-fill text-primary"></i> Webhook Event Subscriptions
            </div>
            <div class="card-body">
                <p class="text-muted mb-3" style="font-size:.83rem">Enable the events you want to receive from Meta. Configure these in the Meta Developer Console under your app's WhatsApp → Configuration → Webhooks.</p>
                <div class="row g-2">
                    <?php
                    $events = [
                        ['messages',           'Incoming Messages',    'Receive texts, media, and reactions from customers'],
                        ['message_deliveries', 'Delivery Receipts',    'Track when messages are delivered to the device'],
                        ['message_reads',      'Read Receipts',        'Know when customers read your messages'],
                        ['message_echoes',     'Message Echoes',       'Copies of messages sent via the API'],
                        ['account_update',     'Account Updates',      'Phone number status and quality rating changes'],
                        ['template_status',    'Template Status',      'Approval/rejection of message templates'],
                    ];
                    foreach ($events as [$key, $label, $desc]):
                    ?>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start gap-2 p-2 rounded" style="background:#f8f9fa">
                            <div class="form-check form-switch mt-1 mb-0">
                                <input class="form-check-input" type="checkbox" id="ev_<?= $key ?>" checked>
                            </div>
                            <div>
                                <label class="form-check-label fw-semibold" for="ev_<?= $key ?>" style="font-size:.83rem;cursor:pointer"><?= $label ?></label>
                                <div class="text-muted" style="font-size:.75rem"><?= $desc ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- RIGHT COLUMN: Test + Log -->
    <div class="col-lg-5">

        <!-- Send Test Message -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-chat-dots-fill" style="color:#25d366"></i> Send Test Message
            </div>
            <div class="card-body">
                <form action="<?= base_url('whatsapp/send_test') ?>" method="post">
                    <input type="hidden" name="<?= $this->config->item('csrf_token_name') ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <div class="mb-3">
                        <label class="form-label">Recipient Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-phone-fill"></i></span>
                            <input type="text" name="test_phone" class="form-control"
                                   placeholder="+27 82 123 4567">
                        </div>
                        <div class="form-text">Include country code, e.g. +27 for South Africa</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-flex justify-content-between">
                            Message <span id="charCount">0 / 1024</span>
                        </label>
                        <textarea name="test_message" id="testMessage" class="form-control"
                                  placeholder="Type your test message…"
                                  maxlength="1024">Hello from Demo QT! 👋 This is a test message from your WhatsApp integration.</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message Type</label>
                        <select name="msg_type" class="form-select form-select-sm">
                            <option value="text">Plain Text</option>
                            <option value="template">Approved Template</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-send-fill me-1"></i> Send Test Message
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row g-3 mb-4">
            <?php
            $wa_stats = [
                ['label' => 'Sent Today',    'value' => '0',  'color' => '#0d6efd', 'icon' => 'send-fill'],
                ['label' => 'Delivered',     'value' => '0',  'color' => '#198754', 'icon' => 'check2-all'],
                ['label' => 'Read',          'value' => '0',  'color' => '#25d366', 'icon' => 'eye-fill'],
                ['label' => 'Failed',        'value' => '0',  'color' => '#dc3545', 'icon' => 'x-circle-fill'],
            ];
            foreach ($wa_stats as $s):
            ?>
            <div class="col-6">
                <div class="stat-card d-flex align-items-center gap-3" style="background:<?= $s['color'] ?>">
                    <i class="bi bi-<?= $s['icon'] ?>" style="font-size:1.4rem;opacity:.5"></i>
                    <div>
                        <div class="stat-val"><?= $s['value'] ?></div>
                        <div class="stat-lbl"><?= $s['label'] ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Message Log -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-journal-text me-2 text-muted"></i>Recent Messages</span>
                <span class="badge bg-secondary"><?= count($sample_log) ?></span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($sample_log as $log):
                        $st = $status_styles[$log['status']] ?? $status_styles['sent'];
                    ?>
                    <div class="list-group-item px-3 py-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div style="font-size:.83rem">
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($log['to']) ?></div>
                                <div class="text-muted text-truncate" style="max-width:200px"><?= htmlspecialchars($log['message']) ?></div>
                            </div>
                            <div class="text-end ms-2" style="white-space:nowrap">
                                <span class="log-badge" style="background:<?= $st['bg'] ?>;color:<?= $st['color'] ?>">
                                    <i class="bi bi-<?= $st['icon'] ?>"></i> <?= ucfirst($log['status']) ?>
                                </span>
                                <div class="text-muted mt-1" style="font-size:.7rem"><?= $log['sent_at'] ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="p-2 border-top text-center">
                    <a href="#" class="btn btn-sm btn-light text-muted" style="font-size:.8rem">
                        <i class="bi bi-arrow-down-circle me-1"></i> Load More
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// Toggle access token visibility
document.getElementById('toggleToken').addEventListener('click', function () {
    const inp  = document.getElementById('accessToken');
    const icon = document.getElementById('toggleTokenIcon');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        inp.type = 'password';
        icon.className = 'bi bi-eye';
    }
});

// Character counter for test message
const ta = document.getElementById('testMessage');
const cc = document.getElementById('charCount');
function updateCount() { cc.textContent = ta.value.length + ' / 1024'; }
ta.addEventListener('input', updateCount);
updateCount();

// Copy to clipboard helper
function copyField(id) {
    const val = document.getElementById(id).value;
    navigator.clipboard.writeText(val).then(function () {
        const btn = document.querySelector('[onclick="copyField(\'' + id + '\')"]');
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2"></i>';
        setTimeout(function () { btn.innerHTML = orig; }, 1500);
    });
}

// Simulated connection test (wire up real AJAX once credentials are stored)
document.getElementById('testConnectionBtn').addEventListener('click', function () {
    const result = document.getElementById('testResult');
    const btn    = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Testing…';
    result.textContent = '';
    setTimeout(function () {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-wifi me-1"></i> Test Connection';
        result.innerHTML = '<i class="bi bi-exclamation-circle text-warning me-1"></i>No credentials saved yet.';
    }, 1400);
});
</script>
