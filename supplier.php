<?php
require_once 'config.php';

$supplierName = $_GET['name'] ?? '';
if (!$supplierName) {
    header('Location: index.php');
    exit;
}

$result = $db->getBySupplier($supplierName);
$items  = $result['data'] ?? [];

// Flash messages
$success = $_GET['success'] ?? '';
$error   = $_GET['error']   ?? '';

// Compute totals
$totalQty = 0;
$totalAmt = 0;
$contact  = '';
foreach ($items as $item) {
    $amt = ($item['amount'] != 0)
        ? $item['amount']
        : (($item['quantity'] ?? 0) * ($item['price'] ?? 0));
    $totalQty += $item['quantity'];
    $totalAmt += $amt;
    $contact   = $item['contact_number'];
}
?>
<!DOCTYPE html>
<html lang="fil">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($supplierName) ?> — Suntastic</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="bg-orb orb-1"></div>
<div class="bg-orb orb-2"></div>
<div class="bg-orb orb-3"></div>

<header class="header">
    <div class="header-inner">
        <div class="brand">
            <a href="index.php" class="back-btn" title="Bumalik">←</a>
            <div class="brand-icon">🏭</div>
            <div>
                <h1 class="brand-name"><?= htmlspecialchars($supplierName) ?></h1>
                <span class="brand-sub">SUPPLIER DETAILS</span>
            </div>
        </div>
        <div class="header-actions">
            <?php if (!empty($items)): ?>
            <button
                class="btn btn-danger-outline"
                onclick="confirmBulkDelete('<?= htmlspecialchars(addslashes($supplierName)) ?>', <?= count($items) ?>)"
                title="I-delete lahat ng items ng supplier na ito"
            >
                🗑 I-delete Lahat
            </button>
            <?php endif; ?>
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

    <!-- SUPPLIER STATS -->
    <div class="stats-bar">
        <div class="stat-card">
            <span class="stat-number"><?= count($items) ?></span>
            <span class="stat-label">Total Items</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= number_format($totalQty) ?></span>
            <span class="stat-label">Total Quantity</span>
        </div>
        <div class="stat-card">
            <span class="stat-number">₱<?= number_format($totalAmt, 0) ?></span>
            <span class="stat-label">Subtotal</span>
        </div>
    </div>

    <!-- SUPPLIER META -->
    <?php if ($contact): ?>
    <div class="supplier-meta-bar">
        <span class="supplier-meta-icon">📞</span>
        <span class="supplier-meta-contact"><?= htmlspecialchars($contact) ?></span>
    </div>
    <?php endif; ?>

    <!-- TABLE -->
    <div class="table-wrap">
        <?php if (empty($items)): ?>
            <div class="empty-state">
                <div class="empty-icon">📦</div>
                <h3>Walang items para sa supplier na ito</h3>
                <a href="add.php" class="btn btn-primary">+ Magdagdag ng Item</a>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pangalan ng Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Amount</th>
                        <th>Contact Number</th>
                        <th>Aksyon</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $item): ?>
                    <?php
                        $amt = ($item['amount'] != 0)
                            ? $item['amount']
                            : (($item['quantity'] ?? 0) * ($item['price'] ?? 0));
                    ?>
                    <tr class="table-row" style="animation-delay: <?= $i * 0.05 ?>s">
                        <td class="td-num"><?= $i + 1 ?></td>
                        <td class="td-item">
                            <span class="item-dot"></span>
                            <?= htmlspecialchars($item['item_name']) ?>
                        </td>
                        <td class="td-qty">
                            <span class="qty-badge <?= $item['quantity'] < 10 ? 'qty-low' : ($item['quantity'] < 50 ? 'qty-mid' : 'qty-high') ?>">
                                <?= number_format($item['quantity']) ?>
                            </span>
                        </td>
                        <td class="td-price">
                            <span class="price-tag">
                                ₱<?= number_format($item['price'] ?? 0, 2) ?>
                            </span>
                        </td>
                        <td class="td-amount">
                            <span class="amount-tag">
                                ₱<?= number_format($amt, 2) ?>
                            </span>
                        </td>
                        <td class="td-contact">
                            <a href="tel:<?= htmlspecialchars($item['contact_number']) ?>" class="contact-link">
                                📞 <?= htmlspecialchars($item['contact_number']) ?>
                            </a>
                        </td>
                        <td class="td-action">
                            <a href="edit.php?id=<?= $item['id'] ?>" class="btn-action btn-edit" title="I-edit">✎</a>
                            <button
                                class="btn-action btn-delete"
                                title="I-delete"
                                onclick="confirmDelete(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['item_name'])) ?>', '<?= htmlspecialchars(addslashes($supplierName)) ?>')"
                            >✕</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <!-- SUBTOTAL ROW -->
                    <tr class="subtotal-row">
                        <td colspan="2" class="subtotal-label">
                            <span class="subtotal-icon">∑</span>
                            Subtotal
                        </td>
                        <td class="subtotal-qty"><?= number_format($totalQty) ?></td>
                        <td></td>
                        <td class="subtotal-amount" colspan="3">
                            ₱<?= number_format($totalAmt, 2) ?>
                        </td>
                    </tr>

                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- BACK LINK -->
    <div class="back-nav">
        <a href="index.php" class="btn btn-outline">← Bumalik sa lahat ng Supplier</a>
    </div>

</main>

<!-- SINGLE DELETE MODAL -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon">🗑</div>
        <h3 class="modal-title">I-delete ang Item?</h3>
        <p class="modal-msg">Sigurado ka bang gusto mong i-delete ang <strong id="deleteItemName"></strong>? Hindi na ito maibabalik.</p>
        <div class="modal-actions">
            <button onclick="closeModal()" class="btn btn-outline">Kanselahin</button>
            <a id="deleteConfirmBtn" href="#" class="btn btn-danger">Oo, I-delete</a>
        </div>
    </div>
</div>

<!-- BULK DELETE MODAL -->
<div id="bulkDeleteModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon">⚠️</div>
        <h3 class="modal-title">I-delete Lahat?</h3>
        <p class="modal-msg">
            Sigurado ka bang gusto mong i-delete ang lahat ng
            <strong id="bulkDeleteCount"></strong> items ni
            <strong id="bulkDeleteSupplier"></strong>?
            <br><br>
            <span style="color: var(--danger); font-size:0.85rem; font-weight:600;">⚠ Hindi na ito maibabalik!</span>
        </p>
        <div class="modal-actions">
            <button onclick="closeBulkModal()" class="btn btn-outline">Kanselahin</button>
            <a id="bulkDeleteConfirmBtn" href="#" class="btn btn-danger">Oo, I-delete Lahat</a>
        </div>
    </div>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>
