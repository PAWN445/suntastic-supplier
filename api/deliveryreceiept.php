<?php
// Company details. Palitan ng totoong impormasyon.
$company = [
    'name'    => 'Suntastic Solar Corporation',
    'address' => '1053 zapote rd, camarin caloocan city, metro manila, philippines',
    'contact' => '(02) 8641 6976',
    'email'   => 'suntasticsolarcorporation@gmail.com',
];

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            --danger: #a32121;
        }
        * { box-sizing: border-box; }
        html, body { background: #fff; }
        body {
            margin: 0; color: var(--ink);
            font-family: "Source Sans 3", "Segoe UI", Arial, sans-serif; font-size: 15px; line-height: 1.45;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        h1, h2, legend, th, .btn { font-family: Montserrat, "Segoe UI", Arial, sans-serif; }

        /* ---------- App layout ---------- */
        .app { display: grid; grid-template-columns: 360px minmax(0, 1fr); gap: 28px; padding: 24px; align-items: start; }
        .form-pane { position: sticky; top: 24px; max-height: calc(100vh - 48px); overflow-y: auto; padding-right: 4px; }
        .form-pane h1 { margin: 0 0 14px; font-size: 19px; font-weight: 800; color: var(--forest); }
        .preview-pane { min-width: 0; }
        .paper { border: 1px solid var(--line); border-radius: 4px; padding: 24px; overflow-x: auto; background: #fff; }

        fieldset { border: 1px solid var(--line); border-radius: 6px; padding: 6px 14px 14px; margin: 0 0 14px; }
        legend { padding: 0 6px; font-weight: 700; font-size: 14px; color: var(--forest); }
        .fld { margin-top: 10px; }
        .two { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 3px; }
        input[type=text], input[type=tel], input[type=number], input[type=date], textarea {
            width: 100%; font: inherit; font-size: 15px; color: var(--ink); background: #fff;
            border: 1px solid #b9c9c1; border-radius: 6px; padding: 8px 10px;
        }
        textarea { resize: vertical; min-height: 58px; }
        input:focus-visible, textarea:focus-visible, button:focus-visible, a:focus-visible {
            outline: 3px solid var(--emerald); outline-offset: 1px; border-color: var(--emerald);
        }

        .row {
            display: grid; grid-template-columns: 1fr 1fr 40px; grid-template-areas: "q u x" "d d d";
            gap: 8px; align-items: center; margin-top: 10px; padding-bottom: 10px; border-bottom: 1px dashed var(--line);
        }
        .row .q { grid-area: q; } .row .u { grid-area: u; } .row .d { grid-area: d; } .row .rm { grid-area: x; }
        .rm {
            width: 40px; height: 38px; font-size: 22px; line-height: 1; cursor: pointer;
            border: 1px solid var(--line); border-radius: 6px; background: #fff; color: var(--muted);
        }
        .rm:hover:not(:disabled) { color: var(--danger); border-color: var(--danger); }
        .rm:disabled { opacity: .35; cursor: not-allowed; }

        .btn {
            font-size: 14px; font-weight: 600; border-radius: 6px; padding: 10px 18px; cursor: pointer;
            border: 2px solid var(--forest); background: #fff; color: var(--forest);
        }
        .btn:hover:not(:disabled) { background: var(--mint); }
        .btn.primary { background: var(--forest); color: #fff; }
        .btn.primary:hover:not(:disabled) { background: var(--emerald); border-color: var(--emerald); }
        .btn:disabled { opacity: .6; cursor: wait; }
        .add { margin-top: 10px; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .actions .primary { flex: 1 1 100%; padding: 12px 18px; font-size: 15px; }
        #status { min-height: 1.4em; margin: 10px 0 0; font-size: 14px; color: var(--muted); }
        #status.err { color: var(--danger); font-weight: 600; }

        /* ---------- Receipt (ito ang lumalabas sa PDF) ---------- */
        .receipt { width: 100%; max-width: 794px; min-width: 640px; margin: 0 auto; background: #fff; }
        .receipt .head {
            display: flex; justify-content: space-between; align-items: center; gap: 24px;
            padding-bottom: 20px; border-bottom: 3px solid var(--emerald);
        }
        .receipt .logo { display: block; width: 250px; height: auto; }
        .receipt .doc { text-align: right; }
        .receipt .doc h1 { margin: 0 0 8px; font-size: 26px; font-weight: 800; color: var(--forest); letter-spacing: -0.01em; }
        .receipt .doc dl { margin: 0; display: grid; grid-template-columns: auto auto; gap: 2px 14px; justify-content: end; }
        .receipt .doc dt { color: var(--muted); }
        .receipt .doc dd { margin: 0; font-weight: 700; min-height: 1.4em; }

        .receipt .info { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin: 24px 0; }
        .receipt .box { border: 1px solid var(--line); border-radius: 6px; padding: 14px 16px; }
        .receipt .box h2 { margin: 0 0 8px; font-size: 14px; font-weight: 700; color: var(--forest); }
        .receipt .box p { margin: 0; min-height: 1.4em; white-space: pre-line; }
        .receipt .box .name { font-weight: 700; font-size: 17px; }
        .receipt .pairs { display: grid; grid-template-columns: auto 1fr; gap: 3px 14px; margin: 0; }
        .receipt .pairs dt { color: var(--muted); }
        .receipt .pairs dd { margin: 0; font-weight: 600; min-height: 1.4em; }

        .receipt table { width: 100%; border-collapse: collapse; }
        .receipt thead th { background: var(--forest); color: #fff; font-size: 13px; font-weight: 600; text-align: left; padding: 10px 12px; }
        .receipt tbody td { padding: 11px 12px; border-bottom: 1px solid var(--line); vertical-align: top; }
        .receipt tr { break-inside: avoid; }
        .receipt .ctr { text-align: center; }

        .receipt .ack { color: var(--muted); font-size: 14px; margin: 22px 0 0; }
        .receipt .sign { display: grid; grid-template-columns: repeat(2, 1fr); gap: 28px; margin-top: 58px; }
        .receipt .sign div { border-top: 1.5px solid var(--forest); padding-top: 6px; font-size: 13px; color: var(--muted); }
        .receipt .sign b { display: block; color: var(--ink); font-size: 14px; min-height: 1.4em; }
        .receipt .foot {
            margin-top: 40px; padding-top: 12px; border-top: 1px solid var(--line);
            display: flex; flex-wrap: wrap; justify-content: center; gap: 4px 22px;
            font-size: 13px; color: var(--muted); text-align: center;
        }

        @media (max-width: 1100px) {
            .app { grid-template-columns: 1fr; padding: 16px; }
            .form-pane { position: static; max-height: none; overflow: visible; padding-right: 0; }
            .paper { padding: 12px; }
        }

        /* margin: 0 = nawawala ang date, time, at title na nilalagay ng browser sa print */
        @page { size: A4; margin: 0; }
        @media print {
            .form-pane { display: none; }
            .app { display: block; padding: 12mm; }
            .paper { border: 0; padding: 0; overflow: visible; }
            .receipt { max-width: none; min-width: 0; }
        }
    </style>
</head>
<body>
<div class="app">

    <!-- ===== Form ===== -->
    <aside class="form-pane">
        <h1>Delivery receipt</h1>
        <form id="dr-form" autocomplete="off" onsubmit="return false;">

            <fieldset>
                <legend>Receipt</legend>
                <div class="two">
                    <div class="fld">
                        <label for="dr_no">DR no.</label>
                        <input type="text" id="dr_no" maxlength="30">
                    </div>
                    <div class="fld">
                        <label for="dr_date">Date</label>
                        <input type="date" id="dr_date">
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Customer</legend>
                <div class="fld">
                    <label for="customer_name">Name</label>
                    <input type="text" id="customer_name" maxlength="150">
                </div>
                <div class="fld">
                    <label for="address">Address</label>
                    <textarea id="address" maxlength="255"></textarea>
                </div>
                <div class="fld">
                    <label for="contact">Contact number</label>
                    <input type="tel" id="contact" maxlength="50">
                </div>
            </fieldset>

            <fieldset>
                <legend>Delivery</legend>
                <div class="fld">
                    <label for="terms">Terms</label>
                    <input type="text" id="terms" list="terms-list" maxlength="100">
                    <datalist id="terms-list">
                        <option value="Cash on delivery">
                        <option value="7 days">
                        <option value="15 days">
                        <option value="30 days">
                    </datalist>
                </div>
                <div class="fld">
                    <label for="delivered_by">Delivered by</label>
                    <input type="text" id="delivered_by" maxlength="100">
                </div>
            </fieldset>

            <fieldset>
                <legend>Items</legend>
                <div id="items"></div>
                <datalist id="units">
                    <option value="pcs"><option value="set"><option value="box"><option value="roll">
                    <option value="meter"><option value="unit"><option value="lot">
                </datalist>
                <button type="button" class="btn add" id="add-item">Add item</button>
            </fieldset>

            <div class="actions">
                <button type="button" class="btn primary" id="btn-save">Save as PDF</button>
                <button type="button" class="btn" id="btn-print">Print</button>
                <button type="button" class="btn" id="btn-new">New receipt</button>
            </div>
            <p id="status" role="status" aria-live="polite"></p>
        </form>
    </aside>

    <!-- ===== Live preview (ito ang ma-se-save) ===== -->
    <main class="preview-pane">
        <div class="paper">
            <div class="receipt">
                <header class="head">
                    <img class="logo" src="../assets/images/logo_suntastic.png" alt="<?= e($company['name']) ?>">
                    <div class="doc">
                        <h1>Delivery Receipt</h1>
                        <dl>
                            <dt>DR no.</dt><dd id="p-dr"></dd>
                            <dt>Date</dt><dd id="p-date"></dd>
                        </dl>
                    </div>
                </header>

                <section class="info">
                    <div class="box">
                        <h2>Delivered to</h2>
                        <p class="name" id="p-name"></p>
                        <p id="p-address"></p>
                        <p id="p-contact"></p>
                    </div>
                    <div class="box">
                        <h2>Delivery details</h2>
                        <dl class="pairs">
                            <dt>Terms</dt><dd id="p-terms"></dd>
                            <dt>Delivered by</dt><dd id="p-by"></dd>
                        </dl>
                    </div>
                </section>

                <table>
                    <thead>
                        <tr>
                            <th class="ctr" style="width:80px">Qty</th>
                            <th class="ctr" style="width:90px">Unit</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody id="p-items"></tbody>
                </table>

                <p class="ack">Received the above items in good order and condition.</p>

                <section class="sign">
                    <div><b id="p-by2"></b>Delivered by</div>
                    <div><b id="p-recv"></b>Received by (signature over printed name)</div>
                </section>

                <footer class="foot">
                    <span><?= e($company['address']) ?></span>
                    <span>Tel: <?= e($company['contact']) ?></span>
                    <span><?= e($company['email']) ?></span>
                </footer>
            </div>
        </div>
    </main>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var KEY = 'suntastic_dr_next';   // naka-save lang sa browser ng computer mo
    var itemsBox = $('items');

    /* ---------- DR number counter (local lang) ---------- */
    function pad(n) { return 'DR-' + String(n).padStart(4, '0'); }
    function getNext() {
        try { var n = parseInt(localStorage.getItem(KEY), 10); return n > 0 ? n : 1; } catch (e) { return 1; }
    }
    function setNext(n) { try { localStorage.setItem(KEY, String(n)); } catch (e) {} }
    function trailingNumber(s) { var m = /(\d+)\s*$/.exec(s); return m ? parseInt(m[1], 10) : null; }

    /* ---------- Helpers ---------- */
    function todayISO() {
        var d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }
    function fmtDate(iso) {
        var p = (iso || '').split('-');
        if (p.length !== 3) return '';
        return new Date(+p[0], +p[1] - 1, +p[2]).toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' });
    }
    function fmtQty(v) { var n = parseFloat(v); return isNaN(n) ? '' : String(+n.toFixed(2)); }
    function val(id) { return $(id).value.trim(); }
    function setStatus(msg, isErr) { var s = $('status'); s.textContent = msg || ''; s.className = isErr ? 'err' : ''; }

    /* ---------- Item rows ---------- */
    function addRow() {
        var row = document.createElement('div');
        row.className = 'row';
        row.innerHTML =
            '<input type="number" class="q" min="0.01" step="any" inputmode="decimal" placeholder="Qty" aria-label="Quantity">' +
            '<input type="text" class="u" list="units" maxlength="30" placeholder="Unit" aria-label="Unit">' +
            '<input type="text" class="d" maxlength="255" placeholder="Description" aria-label="Description">' +
            '<button type="button" class="rm" aria-label="Remove item">&times;</button>';
        itemsBox.appendChild(row);
        syncRemove();
        return row;
    }
    function syncRemove() {
        var rows = itemsBox.querySelectorAll('.row');
        rows.forEach(function (r) { r.querySelector('.rm').disabled = rows.length === 1; });
    }
    function readItems() {
        var out = [];
        itemsBox.querySelectorAll('.row').forEach(function (r) {
            out.push({
                qty: r.querySelector('.q').value.trim(),
                unit: r.querySelector('.u').value.trim(),
                desc: r.querySelector('.d').value.trim()
            });
        });
        return out;
    }

    /* ---------- Live preview (textContent lang, ligtas sa XSS) ---------- */
    function render() {
        $('p-dr').textContent      = val('dr_no');
        $('p-date').textContent    = fmtDate($('dr_date').value);
        $('p-name').textContent    = val('customer_name');
        $('p-address').textContent = val('address');
        $('p-contact').textContent = val('contact');
        $('p-terms').textContent   = val('terms');
        $('p-by').textContent      = val('delivered_by');
        $('p-by2').textContent     = val('delivered_by');
        $('p-recv').textContent    = val('customer_name');

        var tbody = $('p-items');
        tbody.textContent = '';
        readItems().forEach(function (it) {
            if (!it.qty && !it.unit && !it.desc) return;
            var tr = document.createElement('tr');
            [[fmtQty(it.qty), 'ctr'], [it.unit, 'ctr'], [it.desc, '']].forEach(function (c) {
                var td = document.createElement('td');
                td.textContent = c[0];
                if (c[1]) td.className = c[1];
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });
    }

    /* ---------- Validation ---------- */
    function validate() {
        if (!val('dr_no')) return 'Enter the DR number.';
        if (!$('dr_date').value) return 'Choose the date.';
        if (!val('customer_name')) return 'Enter the customer name.';
        var complete = readItems().filter(function (i) { return i.qty && i.unit && i.desc; });
        if (!complete.length) return 'Add at least one item with quantity, unit, and description.';
        var bad = readItems().some(function (i) {
            var any = i.qty || i.unit || i.desc;
            return any && !(parseFloat(i.qty) > 0 && i.unit && i.desc);
        });
        if (bad) return 'Complete or remove the unfinished item rows.';
        return '';
    }

    /* ---------- Save as PDF (sa computer mo) ---------- */
    var saving = false;
    async function savePdf() {
        if (saving) return;
        var problem = validate();
        if (problem) { setStatus(problem, true); return; }
        if (typeof html2pdf === 'undefined') {
            setStatus('The PDF tool could not load (no internet?). Click Print, then choose Save as PDF.', true);
            return;
        }

        var fileName = (val('dr_no').replace(/[^\w.-]+/g, '_') || 'delivery-receipt') + '.pdf';
        var handle = null;

        // "Save as" window (Chrome/Edge): pili ka ng folder at pangalan
        if (window.showSaveFilePicker) {
            try {
                handle = await window.showSaveFilePicker({
                    suggestedName: fileName,
                    types: [{ description: 'PDF document', accept: { 'application/pdf': ['.pdf'] } }]
                });
            } catch (err) {
                if (err && err.name === 'AbortError') { setStatus('Save cancelled.'); return; }
                handle = null; // hindi supported, gagamit ng normal download
            }
        }

        saving = true;
        $('btn-save').disabled = true;
        setStatus('Preparing PDF…');
        try {
            await document.fonts.ready;
            var blob = await html2pdf().set({
                margin: 10,
                filename: fileName,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, scrollX: 0, scrollY: 0 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak: { mode: ['css', 'legacy'] }
            }).from(document.querySelector('.receipt')).outputPdf('blob');

            if (handle) {
                var w = await handle.createWritable();
                await w.write(blob);
                await w.close();
            } else {
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url; a.download = fileName;
                document.body.appendChild(a); a.click(); a.remove();
                setTimeout(function () { URL.revokeObjectURL(url); }, 1500);
            }

            var n = trailingNumber(val('dr_no'));
            if (n !== null && n + 1 > getNext()) setNext(n + 1);
            setStatus('Saved ' + fileName + ' to your computer.');
        } catch (err) {
            console.error(err);
            setStatus('Could not create the PDF. Click Print, then choose Save as PDF.', true);
        } finally {
            saving = false;
            $('btn-save').disabled = false;
        }
    }

    /* ---------- New receipt ---------- */
    function resetForm() {
        ['customer_name', 'address', 'contact', 'terms', 'delivered_by'].forEach(function (id) { $(id).value = ''; });
        itemsBox.textContent = '';
        addRow();
        $('dr_no').value = pad(getNext());
        $('dr_date').value = todayISO();
        setStatus('');
        render();
        $('customer_name').focus();
    }

    /* ---------- Events ---------- */
    $('dr-form').addEventListener('input', render);
    $('add-item').addEventListener('click', function () { addRow().querySelector('input').focus(); });
    itemsBox.addEventListener('click', function (ev) {
        var btn = ev.target.closest('.rm');
        if (!btn) return;
        if (itemsBox.querySelectorAll('.row').length > 1) { btn.closest('.row').remove(); syncRemove(); render(); }
    });
    $('btn-save').addEventListener('click', savePdf);
    $('btn-print').addEventListener('click', function () {
        var problem = validate();
        if (problem) { setStatus(problem, true); return; }
        window.print();
    });
    $('btn-new').addEventListener('click', function () {
        if (confirm('Clear the form and start a new receipt?')) resetForm();
    });

    /* ---------- Start ---------- */
    addRow();
    $('dr_no').value = pad(getNext());
    $('dr_date').value = todayISO();
    render();
})();
</script>
</body>
</html>