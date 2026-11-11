<?php
declare(strict_types=1);

/**
 * Acknowledgement Receipt
 * Fill in the form, press "Create receipt", then print or save as PDF.
 * No database needed. Put this file in your project and open it in the browser.
 */

function e(?string $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function peso(float $n): string
{
    return '₱' . number_format($n, 2);
}

$receipt = null;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in = [
        'ar_no'         => trim($_POST['ar_no'] ?? ''),
        'date'          => trim($_POST['date'] ?? ''),
        'received_from' => trim($_POST['received_from'] ?? ''),
        'received_by'   => trim($_POST['received_by'] ?? ''),
        'reference'     => trim($_POST['reference'] ?? ''),
        'remarks'       => trim($_POST['remarks'] ?? ''),
    ];

    if ($in['received_from'] === '') $errors[] = 'Enter who the items or payment were received from.';
    if ($in['received_by'] === '')   $errors[] = 'Enter who received them.';
    if ($in['date'] === '')          $errors[] = 'Choose the date received.';

    $items = [];
    $descs = $_POST['desc'] ?? [];
    $qtys  = $_POST['qty'] ?? [];
    $units = $_POST['unit'] ?? [];
    $amts  = $_POST['amount'] ?? [];

    foreach ((array)$descs as $i => $d) {
        $d = trim((string)$d);
        if ($d === '') continue;
        $qty = max(0.0, (float)($qtys[$i] ?? 0));
        $amt = max(0.0, (float)($amts[$i] ?? 0));
        $items[] = [
            'desc'   => $d,
            'qty'    => $qty,
            'unit'   => trim((string)($units[$i] ?? '')),
            'amount' => $amt,
            'total'  => $qty * $amt,
        ];
    }
    if (!$items) $errors[] = 'Add at least one item with a description.';

    if (!$errors) {
        $receipt = $in + [
            'items' => $items,
            'grand' => array_sum(array_column($items, 'total')),
        ];
    }
}

$defaultNo = 'AR-' . date('Ymd') . '-' . str_pad((string)random_int(1, 9999), 4, '0', STR_PAD_LEFT);
$v = fn(string $k, string $fallback = '') => e($_POST[$k] ?? $fallback);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $receipt ? 'Acknowledgement Receipt ' . e($receipt['ar_no']) : 'New acknowledgement receipt' ?></title>
<style>
  :root {
    --paper: #fbfaf7;
    --desk: #e7e9ec;
    --ink: #1d2733;
    --muted: #5f6b7a;
    --line: #c9ced6;
    --accent: #1f5f8b;
    --danger: #a3261f;
  }
  * { box-sizing: border-box; }
  body {
    margin: 0;
    padding: 32px 16px 64px;
    background: var(--desk);
    color: var(--ink);
    font: 16px/1.5 Georgia, "Times New Roman", serif;
  }
  .sheet {
    max-width: 780px;
    margin: 0 auto;
    background: var(--paper);
    border: 1px solid var(--line);
    padding: 40px 44px;
    box-shadow: 0 1px 0 var(--line);
  }
  h1 { margin: 0; font-size: 28px; font-weight: 700; letter-spacing: .01em; }
  h2 { margin: 28px 0 8px; font-size: 18px; }
  .sub { color: var(--muted); margin: 4px 0 24px; }
  label { display: block; font: 600 14px/1.3 system-ui, sans-serif; margin-bottom: 4px; }
  input, textarea {
    width: 100%;
    padding: 9px 10px;
    border: 1px solid var(--line);
    border-radius: 3px;
    background: #fff;
    color: var(--ink);
    font: 15px/1.4 system-ui, sans-serif;
  }
  input:focus, textarea:focus, button:focus-visible {
    outline: 2px solid var(--accent);
    outline-offset: 1px;
  }
  textarea { min-height: 72px; resize: vertical; }
  .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .full { grid-column: 1 / -1; }
  table { width: 100%; border-collapse: collapse; font: 15px/1.4 system-ui, sans-serif; }
  th, td { padding: 8px 6px; text-align: left; vertical-align: top; }
  th { border-bottom: 2px solid var(--ink); font-weight: 600; }
  td { border-bottom: 1px solid var(--line); }
  .num { text-align: right; white-space: nowrap; }
  .rows td { padding: 6px 4px; }
  .rows input { padding: 7px 8px; }
  button, .btn {
    font: 600 15px/1 system-ui, sans-serif;
    padding: 11px 18px;
    border-radius: 3px;
    border: 1px solid var(--accent);
    background: var(--accent);
    color: #fff;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
  }
  button.ghost, .btn.ghost { background: transparent; color: var(--accent); }
  .actions { margin-top: 24px; display: flex; gap: 10px; flex-wrap: wrap; }
  .errors {
    border-left: 4px solid var(--danger);
    background: #fdf1f0;
    padding: 10px 14px;
    margin-bottom: 20px;
    font: 15px/1.4 system-ui, sans-serif;
  }
  .errors ul { margin: 0; padding-left: 18px; }
  .rm { background: none; border: 0; color: var(--danger); padding: 6px; font-size: 18px; }

  /* Receipt view */
  .head { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px;
          border-bottom: 2px solid var(--ink); padding-bottom: 12px; }
  .meta { text-align: right; font: 15px/1.5 system-ui, sans-serif; }
  .meta strong { font-size: 17px; }
  dl { display: grid; grid-template-columns: 150px 1fr; gap: 6px 14px; margin: 22px 0 0;
       font: 15px/1.5 system-ui, sans-serif; }
  dt { color: var(--muted); }
  dd { margin: 0; }
  .total td { border-bottom: 0; border-top: 2px solid var(--ink); font-weight: 700; font-size: 17px; }
  .statement { margin: 26px 0 0; }
  .sign { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; margin-top: 64px;
          font: 14px/1.4 system-ui, sans-serif; }
  .sign div { border-top: 1px solid var(--ink); padding-top: 6px; }
  .sign small { color: var(--muted); display: block; }
  .note { margin-top: 28px; color: var(--muted); font: 13px/1.4 system-ui, sans-serif; }

  @media (max-width: 600px) {
    .sheet { padding: 24px 18px; }
    .grid, .sign { grid-template-columns: 1fr; }
    dl { grid-template-columns: 1fr; gap: 0; }
    dd { margin-bottom: 10px; }
  }
  @media print {
    body { background: #fff; padding: 0; }
    .sheet { border: 0; box-shadow: none; max-width: none; padding: 0; }
    .actions { display: none; }
  }
  @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
</style>
</head>
<body>
<main class="sheet">

<?php if ($receipt): ?>

  <div class="head">
    <h1>Acknowledgement Receipt</h1>
    <div class="meta">
      <strong>No. <?= e($receipt['ar_no']) ?></strong><br>
      Date: <?= e(date('F j, Y', strtotime($receipt['date']))) ?>
    </div>
  </div>

  <dl>
    <dt>Received from</dt><dd><?= e($receipt['received_from']) ?></dd>
    <dt>Received by</dt><dd><?= e($receipt['received_by']) ?></dd>
    <?php if ($receipt['reference'] !== ''): ?>
      <dt>Reference</dt><dd><?= e($receipt['reference']) ?></dd>
    <?php endif; ?>
  </dl>

  <p class="statement">
    This confirms that the items, documents or payment listed below were received in good order.
  </p>

  <table>
    <thead>
      <tr>
        <th>Description</th>
        <th class="num">Qty</th>
        <th>Unit</th>
        <th class="num">Amount</th>
        <th class="num">Total</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($receipt['items'] as $it): ?>
        <tr>
          <td><?= e($it['desc']) ?></td>
          <td class="num"><?= e(rtrim(rtrim(number_format($it['qty'], 2), '0'), '.')) ?></td>
          <td><?= e($it['unit']) ?></td>
          <td class="num"><?= e(peso($it['amount'])) ?></td>
          <td class="num"><?= e(peso($it['total'])) ?></td>
        </tr>
      <?php endforeach; ?>
      <tr class="total">
        <td colspan="4" class="num">Total</td>
        <td class="num"><?= e(peso($receipt['grand'])) ?></td>
      </tr>
    </tbody>
  </table>

  <?php if ($receipt['remarks'] !== ''): ?>
    <h2>Remarks</h2>
    <p><?= nl2br(e($receipt['remarks'])) ?></p>
  <?php endif; ?>

  <div class="sign">
    <div>
      <?= e($receipt['received_by']) ?>
      <small>Received by, signature over printed name</small>
    </div>
    <div>
      <?= e($receipt['received_from']) ?>
      <small>Received from, signature over printed name</small>
    </div>
  </div>

  <p class="note">This is an acknowledgement receipt only. It is not an official receipt (OR) and cannot be used for tax claims.</p>

  <div class="actions">
    <button type="button" onclick="window.print()">Print or save as PDF</button>
    <a class="btn ghost" href="<?= e(strtok($_SERVER['REQUEST_URI'], '?')) ?>">New receipt</a>
  </div>

<?php else: ?>

  <h1>New acknowledgement receipt</h1>
  <p class="sub">Fill in the details. You can print or save the result as a PDF.</p>

  <?php if ($errors): ?>
    <div class="errors" role="alert">
      <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" autocomplete="off">
    <div class="grid">
      <div>
        <label for="ar_no">Receipt number</label>
        <input id="ar_no" name="ar_no" value="<?= $v('ar_no', $defaultNo) ?>" required>
      </div>
      <div>
        <label for="date">Date received</label>
        <input id="date" name="date" type="date" value="<?= $v('date', date('Y-m-d')) ?>" required>
      </div>
      <div>
        <label for="received_from">Received from</label>
        <input id="received_from" name="received_from" value="<?= $v('received_from') ?>" placeholder="Supplier or person's name" required>
      </div>
      <div>
        <label for="received_by">Received by</label>
        <input id="received_by" name="received_by" value="<?= $v('received_by') ?>" placeholder="Your name or company" required>
      </div>
      <div class="full">
        <label for="reference">Reference (quotation or PO number)</label>
        <input id="reference" name="reference" value="<?= $v('reference') ?>">
      </div>
    </div>

    <h2>Items received</h2>
    <table class="rows">
      <thead>
        <tr><th>Description</th><th style="width:80px">Qty</th><th style="width:90px">Unit</th><th style="width:120px">Amount</th><th style="width:36px"></th></tr>
      </thead>
      <tbody id="rows">
        <?php
          $descs = $_POST['desc'] ?? [''];
          foreach ($descs as $i => $d):
        ?>
        <tr>
          <td><input name="desc[]" value="<?= e((string)$d) ?>" aria-label="Description"></td>
          <td><input name="qty[]" type="number" min="0" step="any" value="<?= e((string)($_POST['qty'][$i] ?? '1')) ?>" aria-label="Quantity"></td>
          <td><input name="unit[]" value="<?= e((string)($_POST['unit'][$i] ?? '')) ?>" placeholder="pcs" aria-label="Unit"></td>
          <td><input name="amount[]" type="number" min="0" step="0.01" value="<?= e((string)($_POST['amount'][$i] ?? '0')) ?>" aria-label="Amount"></td>
          <td><button type="button" class="rm" onclick="removeRow(this)" aria-label="Remove item">&times;</button></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="actions">
      <button type="button" class="ghost" onclick="addRow()">Add item</button>
    </div>

    <h2>Remarks</h2>
    <textarea name="remarks" aria-label="Remarks"><?= $v('remarks') ?></textarea>

    <div class="actions">
      <button type="submit">Create receipt</button>
    </div>
  </form>

  <script>
    function addRow() {
      const body = document.getElementById('rows');
      const tr = body.rows[0].cloneNode(true);
      tr.querySelectorAll('input').forEach(i => {
        i.value = i.name === 'qty[]' ? '1' : (i.name === 'amount[]' ? '0' : '');
      });
      body.appendChild(tr);
      tr.querySelector('input').focus();
    }
    function removeRow(btn) {
      const body = document.getElementById('rows');
      if (body.rows.length > 1) btn.closest('tr').remove();
    }
  </script>

<?php endif; ?>

</main>
</body>
</html>