<?php
require_once 'config.php';

$search = $_GET['search'] ?? '';
$result = $db->getAll($search);
$items  = $result['data'] ?? [];

// Flash messages
$success = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';

// Group by supplier
$grouped = [];
foreach ($items as $item) {
    $sn = $item['supplier_name'];
    if (!isset($grouped[$sn])) {
        $grouped[$sn] = [
            'contact'  => $item['contact_number'],
            'items'    => [],
            'totalQty' => 0,
            'totalAmt' => 0,
        ];
    }
    $amt = ($item['amount'] != 0)
        ? $item['amount']
        : (($item['quantity'] ?? 0) * ($item['price'] ?? 0));
    $grouped[$sn]['items'][]   = $item;
    $grouped[$sn]['totalQty'] += $item['quantity'];
    $grouped[$sn]['totalAmt'] += $amt;
}
ksort($grouped);

$grandTotal    = array_sum(array_column($grouped, 'totalAmt'));
$grandQty      = array_sum(array_column($grouped, 'totalQty'));
$supplierCount = count($grouped);
$totalItems    = count($items);
?>
<!DOCTYPE html>
<html lang="fil">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suntastic Supplier</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Semi+Condensed:wght@600;700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Quotation button */
        .btn-quotation {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 18px;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid rgba(139, 155, 117, 0.5);
            background: rgba(139, 155, 117, 0.1);
            color: #b8cc99;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-quotation:hover {
            background: rgba(139, 155, 117, 0.2);
            border-color: rgba(139, 155, 117, 0.8);
            color: #cde0b0;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<div class="bg-orb orb-1"></div>
<div class="bg-orb orb-2"></div>
<div class="bg-orb orb-3"></div>

<header class="header">
    <div class="header-inner">
        <div class="brand">
            <div class="brand-icon">
                <img src="/assets/images/suntastic_logo_png.png" alt="Logo" style="width:80px; height:80px; border-radius:6px; opacity: 0.5; z-index: -1; padding:4px">
            </div>
            <div>
                <h1 class="brand-name">Suntastic</h1>
                <span class="brand-sub">SUPPLIER MANAGEMENT</span>
            </div>
        </div>
        <div class="header-actions">
            <a href="/quotation.php" class="btn-quotation">
                📄 Quotation
            </a>
            <a href="/material.php" class="btn-quotation">
                📄 Item Cost
            </a>
            <a href="/material.php" class="btn-quotation">
                📄 Cost of Materials
            </a>
            <a href="add.php" class="btn btn-primary">
                <span class="btn-icon">+</span> Magdagdag
            </a>
        </div>
    </div>
</header>

<main class="main">

    <?php if ($success): ?>
    <div class="alert alert-success">
        <span class="alert-icon">✓</span> <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error">
        <span class="alert-icon">✕</span> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="stats-bar">
        <div class="stat-card">
            <span class="stat-number"><?= $supplierCount ?></span>
            <span class="stat-label">Mga Supplier</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= $totalItems ?></span>
            <span class="stat-label">Total Items</span>
        </div>
        <div class="stat-card">
            <span class="stat-number">₱<?= number_format($grandTotal, 0) ?></span>
            <span class="stat-label">Grand Total</span>
        </div>
    </div>

    <div class="search-wrap">
        <form method="GET" action="index.php" class="search-form">
            <div class="search-box">
                <span class="search-icon">⌕</span>
                <input
                    type="text"
                    name="search"
                    class="search-input"
                    placeholder="Hanapin ang supplier..."
                    value="<?= htmlspecialchars($search) ?>"
                >
                <?php if ($search): ?>
                    <a href="index.php" class="search-clear">✕</a>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-search">Hanapin</button>
        </form>
    </div>

    <?php if (empty($grouped)): ?>
        <div class="empty-state">
            <div class="empty-icon">📦</div>
            <h3>Walang supplier na makita</h3>
            <p><?= $search ? 'Walang nahanap na may keyword na "' . htmlspecialchars($search) . '"' : 'Simulan sa pagdagdag ng unang item!' ?></p>
            <?php if (!$search): ?>
                <a href="add.php" class="btn btn-primary">+ Magdagdag ng Item</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="supplier-grid">
            <?php $i = 0; foreach ($grouped as $supplierName => $data): ?>
            <a
                href="supplier.php?name=<?= urlencode($supplierName) ?>"
                class="supplier-card"
                style="animation-delay: <?= $i * 0.07 ?>s"
            >
                <div class="sc-left">
                    <div class="sc-icon">🏭</div>
                    <div class="sc-info">
                        <div class="sc-name"><?= htmlspecialchars($supplierName) ?></div>
                        <div class="sc-contact">📞 <?= htmlspecialchars($data['contact']) ?></div>
                    </div>
                </div>
                <div class="sc-right">
                    <div class="sc-pills">
                        <span class="sc-pill sc-pill-items"><?= count($data['items']) ?> item<?= count($data['items']) > 1 ? 's' : '' ?></span>
                        <span class="sc-pill sc-pill-qty">Qty: <?= number_format($data['totalQty']) ?></span>
                    </div>
                    <div class="sc-amount">₱<?= number_format($data['totalAmt'], 2) ?></div>
                    <div class="sc-arrow">→</div>
                </div>
            </a>
            <?php $i++; endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<script src="assets/js/main.js"></script>
</body>
</html>