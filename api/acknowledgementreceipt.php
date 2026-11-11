<?php
declare(strict_types=1);

/**
 * Suntastic Solar Corporation: Acknowledgement Receipt
 * Fill in the form, press "Create receipt", then print or save as PDF.
 * No database needed. The logo is embedded, so this is one standalone file.
 */

function e(?string $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function peso(float $n): string
{
    return '₱' . number_format($n, 2);
}

function qtyFmt(float $n): string
{
    return rtrim(rtrim(number_format($n, 2), '0'), '.');
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
    $descs = (array)($_POST['desc'] ?? []);
    $qtys  = (array)($_POST['qty'] ?? []);
    $units = (array)($_POST['unit'] ?? []);
    $amts  = (array)($_POST['amount'] ?? []);

    foreach ($descs as $i => $d) {
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
$v    = fn(string $k, string $fallback = '') => e($_POST[$k] ?? $fallback);
$logo = 'data:image/jpeg;base64,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KKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKTnPT8c/wBKAOw8Mxi10tZH2xtcyNMzt0ESDapPtkufrxWNrmtyanJ5EGUtI2LKCPmkb++x7nrj0p2uavDcqunaYzLYwqiKSMNKVUDJ9BnJA9STWPntUqPvXPMw2BSxE8XWXvPReSD8SfqaKKKo9MKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA//2Q==';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $receipt ? 'Invoice ' . e($receipt['ar_no']) : 'Invoice' ?> | Suntastic</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --charcoal: #292929;          /* same flat grey as the logo artwork */
    --green: #23895a;             /* taken from the logo's bolt */
    --green-bright: #3dbb85;
    --desk: #141414;
    --font-head: 'Montserrat', 'Segoe UI', system-ui, sans-serif;
    --font-body: 'Outfit', 'Segoe UI', system-ui, sans-serif;
  }
  * { box-sizing: border-box; }
  body {
    margin: 0;
    padding: 32px 16px 72px;
    background: var(--desk);
    font: 16px/1.5 var(--font-body);
    -webkit-font-smoothing: antialiased;
  }

  /* ---------- Sheet (shared) ---------- */
  .sheet {
    max-width: 820px;
    margin: 0 auto;
    background: var(--bg);
    color: var(--fg);
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 12px 40px rgba(0,0,0,.45);
  }
  .sheet.paper {
    --bg: #ffffff; --fg: #1e2421; --muted: #66706b; --line: #dfe3e0; --soft: #f1f4f2;
  }
  .sheet.form {
    --bg: #1d1d1d; --fg: #ececec; --muted: #9aa39e; --line: #3a3a3a; --field: #292929;
  }

  .band {
    background: var(--charcoal);
    color: #fff;
    padding: 22px 44px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
  }
  .band img { display: block; width: 210px; height: auto; }
  .band-title { text-align: right; }
  .band-title h1 {
    margin: 0;
    font: 700 22px/1.2 var(--font-head);
    letter-spacing: .01em;
  }
  .band-title p { margin: 6px 0 0; color: #c7cdca; font-size: 15px; }
  .band-title a { color: #c7cdca; text-decoration: none; font-size: 15px; }
  .band-title a:hover { color: #fff; }
  .stripe { height: 5px; background: linear-gradient(90deg, var(--green), var(--green-bright)); }

  .body { padding: 34px 44px 40px; }

  h2 { margin: 30px 0 10px; font: 600 17px/1.3 var(--font-head); }
  .sub { margin: 0 0 22px; color: var(--muted); }

  /* ---------- Form ---------- */
  label { display: block; font-weight: 500; font-size: 14px; margin-bottom: 5px; color: var(--fg); }
  input, textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--line);
    border-radius: 6px;
    background: var(--field);
    color: var(--fg);
    font: 15px/1.4 var(--font-body);
  }
  input::placeholder { color: #6d7571; }
  input:focus, textarea:focus {
    outline: none;
    border-color: var(--green-bright);
    box-shadow: 0 0 0 3px rgba(61,187,133,.22);
  }
  button:focus-visible, .btn:focus-visible { outline: 2px solid var(--green-bright); outline-offset: 2px; }
  textarea { min-height: 80px; resize: vertical; }
  .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .full { grid-column: 1 / -1; }

  .rows { width: 100%; border-collapse: collapse; }
  .rows th { text-align: left; font-weight: 500; font-size: 14px; color: var(--muted); padding: 0 4px 6px; }
  .rows td { padding: 4px; vertical-align: middle; }

  .btn, button {
    font: 600 15px/1 var(--font-body);
    padding: 12px 20px;
    border-radius: 6px;
    border: 1px solid var(--green);
    background: linear-gradient(135deg, var(--green), #1c7a50);
    color: #fff;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: transform .15s, box-shadow .15s;
  }
  .btn:hover, button:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(35,137,90,.35); }
  .btn.ghost, button.ghost {
    background: transparent; color: var(--green-bright); border-color: rgba(61,187,133,.5);
  }
  .sheet.paper .btn.ghost { color: var(--green); border-color: var(--green); }
  button.rm {
    background: none; border: 0; color: #e07a73; padding: 6px 10px; font-size: 20px; box-shadow: none;
  }
  .actions { margin-top: 26px; display: flex; gap: 10px; flex-wrap: wrap; }
  .errors {
    border-left: 4px solid #e05a50;
    background: rgba(224,90,80,.12);
    color: #f3b3ae;
    padding: 10px 14px;
    margin-bottom: 22px;
    border-radius: 0 6px 6px 0;
  }
  .errors ul { margin: 0; padding-left: 18px; }

  /* ---------- Receipt ---------- */
  .parties { display: grid; grid-template-columns: 1fr 1fr; gap: 22px 32px; }
  .parties .label { color: var(--muted); font-size: 14px; margin-bottom: 2px; }
  .parties .value { font: 600 18px/1.35 var(--font-head); }
  .parties .ref { grid-column: 1 / -1; }
  .parties .ref .value { font-size: 16px; }

  .statement { margin: 28px 0 18px; color: var(--muted); }

  table.items { width: 100%; border-collapse: collapse; font-size: 15px; }
  table.items th {
    background: var(--soft);
    border-bottom: 2px solid var(--green);
    text-align: left; font-weight: 600; padding: 10px 10px;
  }
  table.items td { padding: 11px 10px; border-bottom: 1px solid var(--line); vertical-align: top; }
  .num { text-align: right; white-space: nowrap; }

  .total {
    margin: 18px 0 0 auto;
    width: min(100%, 320px);
    background: var(--charcoal);
    color: #fff;
    border-radius: 6px;
    padding: 14px 18px;
    display: flex; justify-content: space-between; align-items: baseline; gap: 16px;
  }
  .total span { color: #c7cdca; }
  .total strong { font: 700 24px/1.1 var(--font-head); color: var(--green-bright); }

  .remarks p { margin: 0; white-space: normal; }

  .sign { display: grid; grid-template-columns: 1fr 1fr; gap: 56px; margin-top: 70px; }
  .sign div { border-top: 1px solid #1e2421; padding-top: 7px; font-weight: 600; }
  .sign small { display: block; font-weight: 400; color: var(--muted); font-size: 13px; }

  .foot {
    background: var(--charcoal);
    color: #c7cdca;
    padding: 14px 44px;
    font-size: 13px;
    display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap;
  }
  .foot b { color: #fff; font-weight: 600; }

  .screen-actions { max-width: 820px; margin: 20px auto 0; display: flex; gap: 10px; flex-wrap: wrap; }

  @media (max-width: 640px) {
    .band, .body, .foot { padding-left: 20px; padding-right: 20px; }
    .band-title { text-align: left; }
    .grid, .parties, .sign { grid-template-columns: 1fr; }
    .sign { gap: 44px; }
    .band img { width: 170px; }
  }

  @page { margin: 10mm; }
  @media print {
    body { background: #fff; padding: 0; }
    .sheet { box-shadow: none; border-radius: 0; max-width: none; }
    .screen-actions { display: none; }
    .band, .foot, .stripe, .total, table.items th {
      -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
    tr, .sign, .total { break-inside: avoid; }
  }
</style>
</head>
<body>

<?php if ($receipt): ?>

  <main class="sheet paper">
    <header class="band">
      <img src="<?= $logo ?>" alt="Suntastic Solar Corporation">
      <div class="band-title">
        <h1>Invoice</h1>
        <p>No. <?= e($receipt['ar_no']) ?><br><?= e(date('F j, Y', strtotime($receipt['date']))) ?></p>
      </div>
    </header>
    <div class="stripe"></div>

    <div class="body">
      <section class="parties">
        <div>
          <div class="label">Received from</div>
          <div class="value"><?= e($receipt['received_from']) ?></div>
        </div>
        <div>
          <div class="label">Received by</div>
          <div class="value"><?= e($receipt['received_by']) ?></div>
        </div>
        <?php if ($receipt['reference'] !== ''): ?>
        <div class="ref">
          <div class="label">Reference</div>
          <div class="value"><?= e($receipt['reference']) ?></div>
        </div>
        <?php endif; ?>
      </section>

      <p class="statement">
        This confirms that the items, documents or payment listed below were received in good order.
      </p>

      <table class="items">
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
            <td class="num"><?= e(qtyFmt($it['qty'])) ?></td>
            <td><?= e($it['unit']) ?></td>
            <td class="num"><?= e(peso($it['amount'])) ?></td>
            <td class="num"><?= e(peso($it['total'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="total">
        <span>Total</span>
        <strong><?= e(peso($receipt['grand'])) ?></strong>
      </div>

      <?php if ($receipt['remarks'] !== ''): ?>
      <section class="remarks">
        <h2>Remarks</h2>
        <p><?= nl2br(e($receipt['remarks'])) ?></p>
      </section>
      <?php endif; ?>

      <div class="sign">
        <div><?= e($receipt['received_by']) ?><small>Received by. Signature over printed name</small></div>
        <div><?= e($receipt['received_from']) ?><small>Received from. Signature over printed name</small></div>
      </div>
    </div>

    <footer class="foot">
      <span><b>Suntastic Solar Corporation</b></span>
      <span>Invoice</span>
    </footer>
  </main>

  <div class="screen-actions">
    <button type="button" onclick="window.print()">Print or save as PDF</button>
    <a class="btn ghost" href="<?= e(strtok($_SERVER['REQUEST_URI'], '?')) ?>">New receipt</a>
    <a class="btn ghost" href="/index.php">Back to suppliers</a>
  </div>

<?php else: ?>

  <main class="sheet form">
    <header class="band">
      <img src="<?= $logo ?>" alt="Suntastic Solar Corporation">
      <div class="band-title">
        <h1>New acknowledgement receipt</h1>
        <p><a href="/index.php">&larr; Back to suppliers</a></p>
      </div>
    </header>
    <div class="stripe"></div>

    <div class="body">
      <p class="sub">Fill in the details, then print the result or save it as a PDF.</p>

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
            <input id="received_by" name="received_by" value="<?= $v('received_by') ?>" placeholder="Name of receiver" required>
          </div>
          <div class="full">
            <label for="reference">Reference (quotation or PO number)</label>
            <input id="reference" name="reference" value="<?= $v('reference') ?>">
          </div>
        </div>

        <h2>Items received</h2>
        <table class="rows">
          <thead>
            <tr>
              <th>Description</th>
              <th style="width:84px">Qty</th>
              <th style="width:96px">Unit</th>
              <th style="width:128px">Amount</th>
              <th style="width:44px"></th>
            </tr>
          </thead>
          <tbody id="rows">
            <?php foreach (($_POST['desc'] ?? ['']) as $i => $d): ?>
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
    </div>
  </main>

  <script>
    function addRow() {
      var body = document.getElementById('rows');
      var tr = body.rows[0].cloneNode(true);
      tr.querySelectorAll('input').forEach(function (i) {
        i.value = i.name === 'qty[]' ? '1' : (i.name === 'amount[]' ? '0' : '');
      });
      body.appendChild(tr);
      tr.querySelector('input').focus();
    }
    function removeRow(btn) {
      var body = document.getElementById('rows');
      if (body.rows.length > 1) btn.closest('tr').remove();
    }
  </script>

<?php endif; ?>

</body>
</html>z`