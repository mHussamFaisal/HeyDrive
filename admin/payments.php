<?php
require_once '../includes/config.php';
require_admin();
$pdo = db_connect();

$msg = ''; $msg_type = 'success';

// ── Auto-create tables if missing ──────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS td_payment_gateways (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    gateway    VARCHAR(50)  NOT NULL UNIQUE,
    label      VARCHAR(100) NOT NULL,
    icon       VARCHAR(100),
    enabled    TINYINT(1)   NOT NULL DEFAULT 0,
    test_mode  TINYINT(1)   NOT NULL DEFAULT 1,
    config     LONGTEXT,
    sort_order INT(3)       NOT NULL DEFAULT 99,
    created_at DATETIME,
    updated_at DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS td_payments (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    gateway    VARCHAR(50),
    amount     DECIMAL(10,2),
    currency   VARCHAR(5) DEFAULT 'EUR',
    status     VARCHAR(30) DEFAULT 'pending',
    txn_ref    VARCHAR(200),
    notes      TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Seed default gateways if empty
$cnt = $pdo->query("SELECT COUNT(*) FROM td_payment_gateways")->fetchColumn();
if ($cnt == 0) {
    $defaults = [
        ['stripe',  'Stripe (Credit/Debit Card)',  'fab fa-stripe-s',      1, 1, '{"publishable_key":"","secret_key":"","webhook_secret":""}', 1],
        ['paypal',  'PayPal',                       'fab fa-paypal',         1, 1, '{"client_id":"","client_secret":"","mode":"sandbox"}',        2],
        ['square',  'Square',                       'fas fa-square',         0, 1, '{"access_token":"","location_id":"","app_id":""}',            3],
        ['sumup',   'SumUp',                        'fas fa-credit-card',    0, 1, '{"api_key":"","merchant_code":""}',                           4],
        ['cash',    'Cash Payment',                 'fas fa-money-bill-wave',1, 0, '{}',                                                          5],
        ['invoice', 'Invoice / Bank Transfer',      'fas fa-file-invoice',   1, 0, '{"bank_name":"","account_name":"","iban":"","payment_terms":"14"}', 6],
    ];
    $ins = $pdo->prepare("INSERT INTO td_payment_gateways (gateway,label,icon,enabled,test_mode,config,sort_order,created_at,updated_at) VALUES (?,?,?,?,?,?,?,NOW(),NOW())");
    foreach ($defaults as $d) $ins->execute($d);
}

// ── POST: Toggle enable/disable ─────────────────────────────────────────────
if (isset($_POST['toggle_gateway'])) {
    $gw = sanitize($_POST['gateway'] ?? '');
    $en = intval($_POST['enabled'] ?? 0);
    $pdo->prepare("UPDATE td_payment_gateways SET enabled=?, updated_at=NOW() WHERE gateway=?")->execute([$en, $gw]);
    $msg = ($en ? '✅ ' : '🔴 ') . ucfirst($gw) . ' payment ' . ($en ? 'enabled' : 'disabled') . ' successfully.';
}

// ── POST: Save gateway config ───────────────────────────────────────────────
if (isset($_POST['save_config'])) {
    $gw   = sanitize($_POST['gateway'] ?? '');
    $mode = intval($_POST['test_mode'] ?? 1);
    $cfg_keys = array_filter(array_keys($_POST), fn($k) => str_starts_with($k, 'cfg_'));
    $cfg = [];
    foreach ($cfg_keys as $k) $cfg[substr($k, 4)] = $_POST[$k];
    $ex = $pdo->prepare("SELECT config FROM td_payment_gateways WHERE gateway=?");
    $ex->execute([$gw]);
    $ex_cfg = json_decode($ex->fetchColumn() ?? '{}', true) ?: [];
    $merged = array_merge($ex_cfg, $cfg);
    $pdo->prepare("UPDATE td_payment_gateways SET config=?, test_mode=?, updated_at=NOW() WHERE gateway=?")
        ->execute([json_encode($merged), $mode, $gw]);
    $msg = '✅ ' . ucfirst($gw) . ' settings saved successfully.';
}

// ── POST: Save display order ────────────────────────────────────────────────
if (isset($_POST['save_order'])) {
    foreach (($_POST['order'] ?? []) as $gw => $ord) {
        $pdo->prepare("UPDATE td_payment_gateways SET sort_order=? WHERE gateway=?")->execute([intval($ord), $gw]);
    }
    $msg = '✅ Payment method order updated.';
}

// ── Load data ───────────────────────────────────────────────────────────────
$gateways = $pdo->query("SELECT * FROM td_payment_gateways ORDER BY sort_order")->fetchAll();
foreach ($gateways as &$g) $g['config'] = json_decode($g['config'] ?? '{}', true) ?: [];

$recent_payments = $pdo->query("
    SELECT p.*, b.customer_name
    FROM td_payments p
    LEFT JOIN td_bookings b ON b.id = p.booking_id
    ORDER BY p.created_at DESC LIMIT 20
")->fetchAll();

$stats_rows = $pdo->query("
    SELECT gateway,
           COUNT(*) as total,
           SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
           SUM(CASE WHEN status IN('pending','processing') THEN 1 ELSE 0 END) as pending_cnt,
           SUM(CASE WHEN status='completed' THEN amount ELSE 0 END) as revenue
    FROM td_payments GROUP BY gateway
")->fetchAll();
$stats = [];
foreach ($stats_rows as $s) $stats[$s['gateway']] = $s;

$gateway_colors = [
    'stripe'=>'#635bff','paypal'=>'#003087','square'=>'#00b140',
    'sumup'=>'#1a1a2e','cash'=>'#198754','invoice'=>'#6c757d'
];

$page_title = 'Payment Gateways';
include 'header.php';
?>

<div class="container-fluid py-4">

<?php if ($msg): ?>
<div class="alert alert-<?= $msg_type ?> alert-dismissible fade show" role="alert">
    <?= $msg ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- ── Page Header ── -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><i class="fas fa-credit-card text-warning me-2"></i>Payment Gateways</h4>
        <small class="text-muted">Configure and manage payment methods for bookings</small>
    </div>
    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#orderModal">
        <i class="fas fa-sort me-1"></i>Reorder Methods
    </button>
</div>

<!-- ── Stats Row ── -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-1 fw-bold text-warning"><?= count(array_filter($gateways, fn($g)=>$g['enabled'])) ?></div>
            <div class="text-muted small">Active Gateways</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-1 fw-bold text-success"><?= array_sum(array_column($stats_rows,'completed')) ?></div>
            <div class="text-muted small">Completed Payments</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-1 fw-bold text-info"><?= array_sum(array_column($stats_rows,'pending_cnt')) ?></div>
            <div class="text-muted small">Pending Payments</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-1 fw-bold text-primary"><?= number_format(array_sum(array_column($stats_rows,'revenue')),2) ?> €</div>
            <div class="text-muted small">Total Revenue</div>
        </div>
    </div>
</div>

<!-- ── Gateway Cards ── -->
<div class="row g-4 mb-4">
<?php foreach ($gateways as $g):
    $color  = $gateway_colors[$g['gateway']] ?? '#6c757d';
    $cfg    = $g['config'];
    $st     = $stats[$g['gateway']] ?? ['total'=>0,'completed'=>0,'pending_cnt'=>0,'revenue'=>0];
    $hasKey = false;
    foreach ($cfg as $v) if (!empty($v)) { $hasKey = true; break; }
?>
<div class="col-md-6 col-xl-4">
<div class="card border-0 shadow-sm h-100">
    <div class="card-header d-flex justify-content-between align-items-center" style="background:<?= $color ?>18; border-left:4px solid <?= $color ?>">
        <div class="d-flex align-items-center gap-2">
            <i class="<?= htmlspecialchars($g['icon']) ?> fs-5" style="color:<?= $color ?>"></i>
            <div>
                <div class="fw-bold"><?= htmlspecialchars($g['label']) ?></div>
                <small class="text-muted">
                    <?= $st['total'] ?> transactions &bull; <?= number_format($st['revenue'],2) ?> € revenue
                </small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if ($g['test_mode'] && $g['enabled']): ?>
                <span class="badge bg-warning text-dark">TEST MODE</span>
            <?php endif; ?>
            <form method="post" class="mb-0">
                <input type="hidden" name="toggle_gateway" value="1">
                <input type="hidden" name="gateway" value="<?= $g['gateway'] ?>">
                <input type="hidden" name="enabled" value="<?= $g['enabled'] ? 0 : 1 ?>">
                <button type="submit" class="btn btn-sm <?= $g['enabled'] ? 'btn-success' : 'btn-outline-secondary' ?>">
                    <i class="fas fa-<?= $g['enabled'] ? 'toggle-on' : 'toggle-off' ?>"></i>
                    <?= $g['enabled'] ? 'Active' : 'Inactive' ?>
                </button>
            </form>
        </div>
    </div>

    <div class="card-body">
        <?php if (!$hasKey && !in_array($g['gateway'],['cash'])): ?>
        <div class="alert alert-warning py-2 small mb-3">
            <i class="fas fa-exclamation-triangle me-1"></i>
            API keys not configured — gateway won't process payments until keys are set.
        </div>
        <?php endif; ?>

        <!-- Config Form -->
        <form method="post">
            <input type="hidden" name="save_config" value="1">
            <input type="hidden" name="gateway" value="<?= $g['gateway'] ?>">

            <?php if ($g['gateway'] === 'stripe'): ?>
            <div class="mb-2">
                <label class="form-label small fw-bold">Publishable Key</label>
                <input type="text" name="cfg_publishable_key" class="form-control form-control-sm" placeholder="pk_live_..." value="<?= htmlspecialchars($cfg['publishable_key']??'') ?>">
            </div>
            <div class="mb-2">
                <label class="form-label small fw-bold">Secret Key</label>
                <input type="password" name="cfg_secret_key" class="form-control form-control-sm" placeholder="sk_live_..." value="<?= htmlspecialchars($cfg['secret_key']??'') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Webhook Secret</label>
                <input type="password" name="cfg_webhook_secret" class="form-control form-control-sm" placeholder="whsec_..." value="<?= htmlspecialchars($cfg['webhook_secret']??'') ?>">
            </div>

            <?php elseif ($g['gateway'] === 'paypal'): ?>
            <div class="mb-2">
                <label class="form-label small fw-bold">Client ID</label>
                <input type="text" name="cfg_client_id" class="form-control form-control-sm" value="<?= htmlspecialchars($cfg['client_id']??'') ?>">
            </div>
            <div class="mb-2">
                <label class="form-label small fw-bold">Client Secret</label>
                <input type="password" name="cfg_client_secret" class="form-control form-control-sm" value="<?= htmlspecialchars($cfg['client_secret']??'') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Mode</label>
                <select name="cfg_mode" class="form-select form-select-sm">
                    <option value="sandbox" <?= ($cfg['mode']??'')=='sandbox'?'selected':'' ?>>Sandbox (Test)</option>
                    <option value="live"    <?= ($cfg['mode']??'')=='live'?'selected':'' ?>>Live</option>
                </select>
            </div>

            <?php elseif ($g['gateway'] === 'square'): ?>
            <div class="mb-2">
                <label class="form-label small fw-bold">Access Token</label>
                <input type="password" name="cfg_access_token" class="form-control form-control-sm" value="<?= htmlspecialchars($cfg['access_token']??'') ?>">
            </div>
            <div class="mb-2">
                <label class="form-label small fw-bold">Location ID</label>
                <input type="text" name="cfg_location_id" class="form-control form-control-sm" value="<?= htmlspecialchars($cfg['location_id']??'') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">App ID</label>
                <input type="text" name="cfg_app_id" class="form-control form-control-sm" value="<?= htmlspecialchars($cfg['app_id']??'') ?>">
            </div>

            <?php elseif ($g['gateway'] === 'sumup'): ?>
            <div class="mb-2">
                <label class="form-label small fw-bold">API Key</label>
                <input type="password" name="cfg_api_key" class="form-control form-control-sm" value="<?= htmlspecialchars($cfg['api_key']??'') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Merchant Code</label>
                <input type="text" name="cfg_merchant_code" class="form-control form-control-sm" value="<?= htmlspecialchars($cfg['merchant_code']??'') ?>">
            </div>

            <?php elseif ($g['gateway'] === 'invoice'): ?>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label small fw-bold">Bank Name</label>
                    <input type="text" name="cfg_bank_name" class="form-control form-control-sm" value="<?= htmlspecialchars($cfg['bank_name']??'') ?>">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold">Account Name</label>
                    <input type="text" name="cfg_account_name" class="form-control form-control-sm" value="<?= htmlspecialchars($cfg['account_name']??'') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold">IBAN</label>
                    <input type="text" name="cfg_iban" class="form-control form-control-sm" placeholder="GB29 NWBK 6016 1331 9268 19" value="<?= htmlspecialchars($cfg['iban']??'') ?>">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold">Payment Terms (days)</label>
                    <input type="number" name="cfg_payment_terms" class="form-control form-control-sm" value="<?= htmlspecialchars($cfg['payment_terms']??'14') ?>">
                </div>
            </div>

            <?php elseif ($g['gateway'] === 'cash'): ?>
            <div class="alert alert-info py-2 small mb-3">
                <i class="fas fa-info-circle me-1"></i>
                Cash payment requires no API configuration. Drivers collect payment directly from the passenger.
            </div>
            <?php endif; ?>

            <?php if (!in_array($g['gateway'],['cash'])): ?>
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="test_mode" value="1" id="tm_<?= $g['gateway'] ?>" <?= $g['test_mode'] ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="tm_<?= $g['gateway'] ?>">Test / Sandbox Mode</label>
                </div>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-warning btn-sm w-100">
                <i class="fas fa-save me-1"></i>Save <?= htmlspecialchars($g['label']) ?> Settings
            </button>
        </form>
    </div>
</div>
</div>
<?php endforeach; ?>
</div>

<!-- ── Recent Transactions ── -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center fw-bold">
        <span><i class="fas fa-history me-2 text-warning"></i>Recent Transactions</span>
        <small class="text-muted fw-normal">Last 20 payments</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" id="paymentsTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Booking</th>
                        <th>Customer</th>
                        <th>Gateway</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Ref</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($recent_payments)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No payment records yet.</td></tr>
                <?php else: ?>
                <?php foreach ($recent_payments as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= format_datetime($p['created_at']) ?></td>
                    <td><a href="booking_edit.php?id=<?= $p['booking_id'] ?>">#<?= $p['booking_id'] ?></a></td>
                    <td><?= htmlspecialchars($p['customer_name'] ?? '-') ?></td>
                    <td>
                        <i class="<?= $gateway_icons[$p['gateway']] ?? 'fas fa-credit-card' ?>"></i>
                        <?= htmlspecialchars(ucfirst($p['gateway'])) ?>
                    </td>
                    <td class="fw-bold"><?= number_format($p['amount'],2) ?> €</td>
                    <td>
                        <?php
                        $bs = ['completed'=>'success','pending'=>'warning','failed'=>'danger','refunded'=>'info','processing'=>'primary'];
                        $bc = $bs[$p['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $bc ?>"><?= ucfirst($p['status']) ?></span>
                    </td>
                    <td><small class="text-muted"><?= htmlspecialchars(substr($p['txn_ref']??'—',0,20)) ?></small></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div><!-- /container -->

<!-- Reorder Modal -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-sort me-2"></i>Reorder Payment Methods</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <input type="hidden" name="save_order" value="1">
                <div class="modal-body">
                    <p class="text-muted small">Set the display order for customers at checkout (lower number = shown first).</p>
                    <table class="table table-sm">
                        <thead><tr><th>Gateway</th><th>Order</th></tr></thead>
                        <tbody>
                        <?php foreach ($gateways as $g): ?>
                        <tr>
                            <td><i class="<?= $g['icon'] ?> me-2"></i><?= htmlspecialchars($g['label']) ?></td>
                            <td><input type="number" name="order[<?= $g['gateway'] ?>]" value="<?= $g['sort_order'] ?>" class="form-control form-control-sm" style="width:80px"></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm">Save Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){ $('#paymentsTable').DataTable({pageLength:10,order:[[1,'desc']]}); });
</script>

<?php include 'footer.php'; ?>
