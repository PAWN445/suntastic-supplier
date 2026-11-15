<?php
// ---------------------------------------------------------------
// Sample data. Palitan ng galing sa database (PDO + prepared statement).
// ---------------------------------------------------------------
$company = [
    'name'    => 'Suntastic Solar Corporation',
    'address' => 'Palitan ng company address, Quezon City',
    'contact' => '0900-000-0000',
    'email'   => 'info@example.com',
    'tin'     => '000-000-000-000',
];

$dr = [
    'number'       => 'DR-0001',
    'date'         => date('F d, Y'),
    'customer'     => 'Juan Dela Cruz',
    'address'      => '456 Customer Ave., Makati City',
    'contact'      => '0918-111-1111',
    'po_number'    => 'PO-2026-045',
    'terms'        => 'Cash on delivery',
    'vehicle'      => 'ABC 1234',
    'prepared_by'  => 'Admin',
    'delivered_by' => 'Driver Name',
    'remarks'      => '',
];

$items = [
    ['qty' => 10, 'unit' => 'pcs', 'description' => 'Solar panel 550W monocrystalline', 'note' => 'Serial nos. attached', 'price' => 9500.00],
    ['qty' => 1,  'unit' => 'set', 'description' => 'Hybrid inverter 5kW',               'note' => '',                    'price' => 48000.00],
    ['qty' => 2,  'unit' => 'box', 'description' => 'Mounting rail kit',                 'note' => '',                    'price' => 3200.00],
];

$showPrices = false; // gawing false kung walang presyo sa DR

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
    <title>Delivery Receipt <?= e($dr['number']) ?> - <?= e($company['name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --forest: #0e3b2e;
            --emerald: #1f9d63;
            --mint: #eef6f1;
            --ink: #1a2321;
            --muted: #5b6b66;
            --line: #d6e2dc;
            --paper: #ffffff;
        }
        * { box-sizing: border-box; }
        html, body { background: var(--paper); }
        body {
            margin: 0;
            padding: 32px 16px;
            color: var(--ink);
            font-family: "Source Sans 3", "Segoe UI", Arial, sans-serif;
            font-size: 15px;
            line-height: 1.45;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        h1, h2, th, .brand-font { font-family: Montserrat, "Segoe UI", Arial, sans-serif; }

        .sheet { max-width: 794px; margin: 0 auto; }

        /* Header */
        .head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
            padding-bottom: 20px;
            border-bottom: 3px solid var(--emerald);
        }
        .logo { display: block; width: 250px; max-width: 55%; height: auto; }
        .doc { text-align: right; }
        .doc h1 { margin: 0 0 8px; font-size: 26px; font-weight: 800; color: var(--forest); letter-spacing: -0.01em; }
        .doc dl { margin: 0; display: grid; grid-template-columns: auto auto; gap: 2px 14px; justify-content: end; }
        .doc dt { color: var(--muted); }
        .doc dd { margin: 0; font-weight: 700; }

        /* Info blocks */
        .info { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin: 24px 0; }
        .box { border: 1px solid var(--line); border-radius: 6px; padding: 14px 16px; }
        .box h2 { margin: 0 0 8px; font-size: 14px; font-weight: 700; color: var(--forest); }
        .box p { margin: 0; }
        .box .name { font-weight: 700; font-size: 17px; }
        .pairs { display: grid; grid-template-columns: auto 1fr; gap: 3px 14px; margin: 0; }
        .pairs dt { color: var(--muted); }
        .pairs dd { margin: 0; font-weight: 600; }

        /* Table */
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: var(--forest);
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            text-align: left;
            padding: 10px 12px;
        }
        tbody td { padding: 11px 12px; border-bottom: 1px solid var(--line); vertical-align: top; }
        .num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .ctr { text-align: center; }
        .note { display: block; color: var(--muted); font-size: 13px; }

        .total {
            display: flex;
            justify-content: flex-end;
            align-items: baseline;
            gap: 24px;
            margin-top: 14px;
            padding: 12px 14px;
            background: var(--mint);
            border-left: 4px solid var(--emerald);
        }
        .total span { font-weight: 600; }
        .total strong { font-family: Montserrat, Arial, sans-serif; font-size: 22px; font-weight: 800; color: var(--forest); }

        .remarks { margin: 22px 0 6px; }
        .remarks strong { color: var(--forest); }
        .ack { color: var(--muted); font-size: 14px; margin: 0; }

        /* Signatures */
        .sign { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; margin-top: 58px; }
        .sign div { border-top: 1.5px solid var(--forest); padding-top: 6px; font-size: 13px; color: var(--muted); }
        .sign b { display: block; color: var(--ink); font-size: 14px; }

        /* Footer */
        .foot {
            margin-top: 40px;
            padding-top: 12px;
            border-top: 1px solid var(--line);
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 4px 22px;
            font-size: 13px;
            color: var(--muted);
            text-align: center;
        }

        .actions { margin-top: 24px; text-align: center; }
        .actions button {
            font: 600 15px Montserrat, Arial, sans-serif;
            background: var(--forest);
            color: #fff;
            border: 0;
            border-radius: 6px;
            padding: 11px 26px;
            cursor: pointer;
        }
        .actions button:hover { background: var(--emerald); }
        .actions button:focus-visible { outline: 3px solid var(--emerald); outline-offset: 2px; }

        @media (max-width: 640px) {
            .head { flex-direction: column; align-items: flex-start; }
            .doc { text-align: left; }
            .doc dl { justify-content: start; }
            .logo { max-width: 80%; }
            .info, .sign { grid-template-columns: 1fr; }
            .table-wrap { overflow-x: auto; }
        }

        @page { size: A4; margin: 12mm; }
        @media print {
            body { padding: 0; }
            .actions { display: none; }
            tr { break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="sheet">

    <header class="head">
        <img class="logo" src="assets/images/logo_suntastic.png" alt="<?= e($company['name']) ?>">
        <div class="doc">
            <h1>Delivery Receipt</h1>
            <dl>
                <dt>DR no.</dt><dd><?= e($dr['number']) ?></dd>
                <dt>Date</dt><dd><?= e($dr['date']) ?></dd>
            </dl>
        </div>
    </header>

    <section class="info">
        <div class="box">
            <h2>Delivered to</h2>
            <p class="name"><?= e($dr['customer']) ?></p>
            <p><?= e($dr['address']) ?></p>
            <p><?= e($dr['contact']) ?></p>
        </div>
        <div class="box">
            <h2>Delivery details</h2>
            <dl class="pairs">
                <dt>P.O. no.</dt><dd><?= e($dr['po_number']) ?></dd>
                <dt>Terms</dt><dd><?= e($dr['terms']) ?></dd>
                <dt>Vehicle plate</dt><dd><?= e($dr['vehicle']) ?></dd>
                <dt>Delivered by</dt><dd><?= e($dr['delivered_by']) ?></dd>
            </dl>
        </div>
    </section>

    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th class="ctr" style="width:64px">Qty</th>
                <th class="ctr" style="width:64px">Unit</th>
                <th>Description</th>
                <?php if ($showPrices): ?>
                <th class="num" style="width:120px">Unit price</th>
                <th class="num" style="width:130px">Amount</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item):
                $amount = $item['qty'] * $item['price'];
                $grandTotal += $amount;
            ?>
            <tr>
                <td class="ctr"><?= e($item['qty']) ?></td>
                <td class="ctr"><?= e($item['unit']) ?></td>
                <td>
                    <?= e($item['description']) ?>
                    <?php if (!empty($item['note'])): ?>
                        <span class="note"><?= e($item['note']) ?></span>
                    <?php endif; ?>
                </td>
                <?php if ($showPrices): ?>
                <td class="num"><?= number_format($item['price'], 2) ?></td>
                <td class="num"><?= number_format($amount, 2) ?></td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <?php if ($showPrices): ?>
    <div class="total">
        <span>Total amount</span>
        <strong>₱<?= number_format($grandTotal, 2) ?></strong>
    </div>
    <?php endif; ?>

    <p class="remarks"><strong>Remarks:</strong> <?= e($dr['remarks'] !== '' ? $dr['remarks'] : 'None') ?></p>
    <p class="ack">Received the above items in good order and condition.</p>

    <section class="sign">
        <div><b><?= e($dr['prepared_by']) ?></b>Prepared by</div>
        <div><b><?= e($dr['delivered_by']) ?></b>Delivered by</div>
        <div><b>&nbsp;</b>Received by (signature over printed name and date)</div>
    </section>

    <footer class="foot">
        <span><?= e($company['address']) ?></span>
        <span>Tel: <?= e($company['contact']) ?></span>
        <span><?= e($company['email']) ?></span>
        <span>TIN: <?= e($company['tin']) ?></span>
    </footer>

    <div class="actions">
        <button type="button" onclick="window.print()">Print receipt</button>
    </div>

</div>
</body>
</html>