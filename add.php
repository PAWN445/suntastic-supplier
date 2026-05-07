<?php
require_once 'config.php';

$errors = [];
$formData = ['item_name' => '', 'quantity' => '', 'price' => '', 'supplier_name' => '', 'contact_number' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'item_name'      => trim($_POST['item_name'] ?? ''),
        'quantity'       => trim($_POST['quantity'] ?? ''),
        'price'          => trim($_POST['price'] ?? ''),
        'supplier_name'  => trim($_POST['supplier_name'] ?? ''),
        'contact_number' => trim($_POST['contact_number'] ?? ''),
    ];

    // Validation
    if (empty($formData['item_name'])) $errors[] = 'Kailangan ang pangalan ng item.';
    if (!is_numeric($formData['quantity']) || $formData['quantity'] < 0) $errors[] = 'Kailangan ang tamang quantity (numero).';
    if (!is_numeric($formData['price']) || $formData['price'] < 0) $errors[] = 'Kailangan ang tamang price (numero).';
    if (empty($formData['supplier_name'])) $errors[] = 'Kailangan ang pangalan ng supplier.';
    if (empty($formData['contact_number'])) $errors[] = 'Kailangan ang contact number.';

    if (empty($errors)) {
        $formData['quantity'] = (int)$formData['quantity'];
        $formData['price']    = (float)$formData['price'];
        $formData['amount']   = $formData['quantity'] * $formData['price'];
        $result = $db->insert($formData);

        if ($result['code'] === 201) {
            header('Location: index.php?success=Matagumpay+na+naidagdag+ang+item!');
            exit;
        } else {
            $errors[] = 'May error na naganap. Subukan ulit.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fil">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magdagdag ng Item — Suntastic Supplier</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="bg-orb orb-1"></div>
<div class="bg-orb orb-2"></div>

<header class="header">
    <div class="header-inner">
        <div class="brand">
            <div class="brand-icon">☀</div>
            <div>
                <h1 class="brand-name">Suntastic</h1>
                <span class="brand-sub">SUPPLIER MANAGEMENT</span>
            </div>
        </div>
        <a href="index.php" class="btn btn-outline">← Bumalik</a>
    </div>
</header>

<main class="main">
    <div class="form-container">
        <div class="form-header">
            <div class="form-header-icon">📦</div>
            <h2 class="form-title">Bagong Item</h2>
            <p class="form-subtitle">Punan ang lahat ng impormasyon ng item</p>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <span class="alert-icon">✕</span>
            <ul style="margin:0; padding-left:1.2rem;">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="add.php" class="form-card" novalidate>
            <div class="form-group">
                <label class="form-label" for="item_name">
                    <span class="label-dot" style="background:#f59e0b"></span>
                    Pangalan ng Item
                </label>
                <input
                    type="text"
                    id="item_name"
                    name="item_name"
                    class="form-input"
                    placeholder="hal. Laptop, T-Shirt, Bigas..."
                    value="<?= htmlspecialchars($formData['item_name']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="quantity">
                    <span class="label-dot" style="background:#10b981"></span>
                    Quantity
                </label>
                <input
                    type="number"
                    id="quantity"
                    name="quantity"
                    class="form-input"
                    placeholder="hal. 100"
                    value="<?= htmlspecialchars($formData['quantity']) ?>"
                    min="0"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="price">
                    <span class="label-dot" style="background:#f59e0b"></span>
                    Price (₱)
                </label>
                <div class="input-prefix-wrap">
                    <span class="input-prefix">₱</span>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        class="form-input with-prefix"
                        placeholder="hal. 1500.00"
                        value="<?= htmlspecialchars($formData['price']) ?>"
                        min="0"
                        step="0.01"
                        required
                    >
                </div>
            </div>

            <!-- AMOUNT — auto-computed, read-only -->
            <div class="form-group">
                <label class="form-label">
                    <span class="label-dot" style="background:#a78bfa"></span>
                    Amount (Quantity × Price)
                </label>
                <div class="amount-display" id="amountDisplay">
                    <span class="amount-equals">=</span>
                    <span class="amount-value" id="amountValue">₱0.00</span>
                </div>
                <!-- Hidden input para ma-submit sa server -->
                <input type="hidden" id="amount" name="amount" value="0">
            </div>

            <div class="form-group">
                <label class="form-label" for="supplier_name">
                    <span class="label-dot" style="background:#6366f1"></span>
                    Pangalan ng Supplier
                </label>
                <input
                    type="text"
                    id="supplier_name"
                    name="supplier_name"
                    class="form-input"
                    placeholder="hal. ABC Trading, XYZ Corp..."
                    value="<?= htmlspecialchars($formData['supplier_name']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="contact_number">
                    <span class="label-dot" style="background:#ef4444"></span>
                    Contact Number
                </label>
                <input
                    type="text"
                    id="contact_number"
                    name="contact_number"
                    class="form-input"
                    placeholder="hal. 09XX-XXX-XXXX"
                    value="<?= htmlspecialchars($formData['contact_number']) ?>"
                    required
                >
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn btn-outline">Kanselahin</a>
                <button type="submit" class="btn btn-primary">
                    <span>+ Idagdag ang Item</span>
                </button>
            </div>
        </form>
    </div>
</main>

<script src="assets/js/main.js"></script>
</body>
</html>
