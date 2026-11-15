<?php
// Sample data. Palitan ng galing sa database (hal. PDO query gamit ang DR id).
$company = [
    'name'    => 'Your Company Name',
    'address' => '123 Sample St., Quezon City',
    'contact' => '0917-000-0000',
    'tin'     => '000-000-000-000',
];

$dr = [
    'number'   => 'DR-0001',
    'date'     => date('F d, Y'),
    'customer' => 'Juan Dela Cruz',
    'address'  => '456 Customer Ave., Makati City',
    'contact'  => '0918-111-1111',
    'remarks'  => '',
    'prepared_by'  => 'Admin',
    'delivered_by' => 'Driver Name',
];

$items = [
    ['qty' => 10, 'unit' => 'pcs', 'description' => 'Item A', 'price' => 150.00],
    ['qty' => 5,  'unit' => 'box', 'description' => 'Item B', 'price' => 800.00],
];

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$grandTotal = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Receipt <?= e($dr['number']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; color: #000; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 22px; }
        .title { text-align: center; font-size: 20px; font-weight: bold; margin: 15px 0; }
        .info { display: flex; justify-content: space-between; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px 8px; font-size: 14px; }
        th { background: #eee; }
        .right { text-align: right; }
        .center { text-align: center; }
        .signatures { display: flex; justify-content: space-between; margin-top: 50px; }
        .sig { width: 30%; text-align: center; }
        .sig .line { border-top: 1px solid #000; margin-top: 40px; padding-top: 4px; }
        .no-print { margin-top: 20px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

    <div class="header">
        <h1><?= e($company['name']) ?></h1>
        <div><?= e($company['address']) ?></div>
        <div>Tel: <?= e($company['contact']) ?> | TIN: <?= e($company['tin']) ?></div>
    </div>

    <div class="title">DELIVERY RECEIPT</div>

    <div class="info">
        <div>
            <strong>Delivered to:</strong> <?= e($dr['customer']) ?><br>
            <strong>Address:</strong> <?= e($dr['address']) ?><br>
            <strong>Contact:</strong> <?= e($dr['contact']) ?>
        </div>
        <div>
            <strong>DR No.:</strong> <?= e($dr['number']) ?><br>
            <strong>Date:</strong> <?= e($dr['date']) ?>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Qty</th>
                <th>Unit</th>
                <th>Description</th>
                <th>Unit Price</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item):
                $amount = $item['qty'] * $item['price'];
                $grandTotal += $amount;
            ?>
            <tr>
                <td class="center"><?= e($item['qty']) ?></td>
                <td class="center"><?= e($item['unit']) ?></td>
                <td><?= e($item['description']) ?></td>
                <td class="right"><?= number_format($item['price'], 2) ?></td>
                <td class="right"><?= number_format($amount, 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="4" class="right"><strong>TOTAL</strong></td>
                <td class="right"><strong>₱<?= number_format($grandTotal, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>

    <p><strong>Remarks:</strong> <?= e($dr['remarks']) ?></p>
    <p><em>Received the above items in good order and condition.</em></p>

    <div class="signatures">
        <div class="sig">
            <div class="line">Prepared by: <?= e($dr['prepared_by']) ?></div>
        </div>
        <div class="sig">
            <div class="line">Delivered by: <?= e($dr['delivered_by']) ?></div>
        </div>
        <div class="sig">
            <div class="line">Received by (Signature over printed name / Date)</div>
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()">Print</button>
    </div>

</body>
</html>