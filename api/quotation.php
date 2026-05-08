<?php
require_once __DIR__ . '/config.php';

// Fetch all supplier items for the picker
$result   = $db->getAll('');
$allItems = $result['data'] ?? [];
$itemsJson = json_encode($allItems, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<!DOCTYPE html>
<html lang="fil">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation Builder — Suntastic</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- Export libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<style>
/* ── PAGE LAYOUT ─────────────────────────────────────────────── */
.qb-layout {
    display: grid;
    grid-template-columns: 420px 1fr;
    gap: 1.75rem;
    align-items: start;
}
@media (max-width: 1100px) { .qb-layout { grid-template-columns: 1fr; } }

/* ── FORM PANEL ──────────────────────────────────────────────── */
.form-panel {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    position: sticky;
    top: 88px;
    max-height: calc(100vh - 110px);
    overflow-y: auto;
    padding-right: 4px;
    scrollbar-width: thin;
    scrollbar-color: rgba(245,158,11,0.3) transparent;
}
.form-panel::-webkit-scrollbar { width: 4px; }
.form-panel::-webkit-scrollbar-thumb { background: rgba(245,158,11,0.3); border-radius: 4px; }

.fp-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px;
    padding: 1.25rem;
}
.fp-title {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 0.95rem;
    letter-spacing: 0.14em;
    color: var(--sun);
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.fp-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.fp-grid.full { grid-template-columns: 1fr; }
.fp-group { display: flex; flex-direction: column; gap: 5px; }
.fp-group.span2 { grid-column: 1 / -1; }
.fp-label {
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.4);
}
.fp-input {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.85rem;
    padding: 0.45rem 0.75rem;
    outline: none;
    width: 100%;
    box-sizing: border-box;
    transition: border-color 0.2s;
}
.fp-input:focus { border-color: var(--sun); }
.fp-input::placeholder { color: rgba(255,255,255,0.2); }
textarea.fp-input { resize: vertical; min-height: 70px; line-height: 1.5; }

/* ── ITEMS MANAGER ───────────────────────────────────────────── */
.item-row-ui {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 10px;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    animation: rowIn 0.2s ease;
    position: relative;
}
@keyframes rowIn {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.item-row-num {
    position: absolute;
    top: 8px;
    left: 10px;
    font-size: 0.65rem;
    color: rgba(255,255,255,0.25);
    font-weight: 700;
}
.item-row-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    padding-left: 18px;
}
.item-row-grid .span2 { grid-column: 1 / -1; }
.item-name-wrap {
    display: flex;
    gap: 5px;
    align-items: center;
}
.btn-pick-sm {
    background: rgba(245,158,11,0.1);
    border: 1px solid rgba(245,158,11,0.3);
    border-radius: 6px;
    color: var(--sun);
    padding: 0.3rem 0.45rem;
    font-size: 0.75rem;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.15s;
    white-space: nowrap;
}
.btn-pick-sm:hover { background: rgba(245,158,11,0.2); }
.btn-del-row {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(239,68,68,0.1);
    border: 1px solid rgba(239,68,68,0.2);
    border-radius: 5px;
    color: #f87171;
    padding: 0.2rem 0.45rem;
    cursor: pointer;
    font-size: 0.75rem;
    line-height: 1;
    transition: background 0.15s;
}
.btn-del-row:hover { background: rgba(239,68,68,0.25); }
.item-amt-display {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1rem;
    color: #a78bfa;
    letter-spacing: 0.5px;
    padding: 0.4rem 0;
    grid-column: 1 / -1;
    border-top: 1px solid rgba(255,255,255,0.05);
    margin-top: 2px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.item-amt-display span { font-size: 0.65rem; color: rgba(255,255,255,0.3); font-family: 'DM Sans',sans-serif; font-weight:600; letter-spacing:0.08em; text-transform:uppercase; }

.btn-add-item {
    width: 100%;
    padding: 0.6rem;
    border: 1px dashed rgba(245,158,11,0.35);
    border-radius: 9px;
    background: none;
    color: var(--sun);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-add-item:hover { border-color: var(--sun); background: rgba(245,158,11,0.04); }

/* Toggle rows */
.toggle-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.4rem 0;
    font-size: 0.82rem;
    color: rgba(255,255,255,0.5);
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.toggle-row:last-child { border-bottom: none; }
.sw { position: relative; width: 36px; height: 20px; flex-shrink: 0; }
.sw input { display: none; }
.sw-track {
    position: absolute; inset: 0;
    background: rgba(255,255,255,0.12);
    border-radius: 20px; cursor: pointer;
    transition: background 0.2s;
}
.sw-track::before {
    content: '';
    position: absolute;
    top: 3px; left: 3px;
    width: 14px; height: 14px;
    border-radius: 50%;
    background: #fff;
    transition: transform 0.2s;
}
input:checked + .sw-track { background: var(--sun); }
input:checked + .sw-track::before { transform: translateX(16px); }

/* Export buttons */
.btn-xl {
    width: 100%;
    padding: 0.8rem;
    border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-weight: 700;
    font-size: 0.875rem;
    cursor: pointer;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: filter 0.2s, transform 0.15s;
    margin-bottom: 0.5rem;
}
.btn-xl:hover { filter: brightness(1.1); transform: translateY(-1px); }
.btn-xl:active { transform: translateY(0); }
.btn-xl-pdf { background: linear-gradient(135deg,#c0392b,#e74c3c); color:#fff; }
.btn-xl-excel { background: linear-gradient(135deg,#1d6f42,#21a366); color:#fff; }

/* ── LIVE PREVIEW PANEL ──────────────────────────────────────── */
.preview-panel {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.5);
    overflow: hidden;
    color: #1a1a1a;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11.5px;
    line-height: 1.45;
    min-height: 600px;
}

/* Preview internal styles */
.pv { padding: 28px 32px; }

/* Doc ref line */
.pv-ref-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    font-size: 9.5px;
    color: #555;
}

/* Company header */
.pv-company-header {
    text-align: center;
    margin-bottom: 14px;
    padding-bottom: 12px;
    border-bottom: 1px solid #ddd;
}
.pv-company-logo {
    font-size: 22px;
    margin-bottom: 2px;
}
.pv-company-name {
    font-size: 15px;
    font-weight: 900;
    letter-spacing: 1.5px;
    color: #1a5e1a;
    text-transform: uppercase;
}
.pv-company-tagline {
    font-size: 8px;
    letter-spacing: 3px;
    color: #888;
    text-transform: uppercase;
    margin-bottom: 4px;
}
.pv-company-addr {
    font-size: 9px;
    color: #444;
    line-height: 1.6;
}

/* QUOTATION SLIP title */
.pv-title-band {
    background: #8B9B75;
    color: #fff;
    text-align: center;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 3px;
    padding: 7px 0;
    margin-bottom: 0;
    text-transform: uppercase;
}

/* Info grid */
.pv-info-box {
    border: 1px solid #bbb;
    border-top: none;
    padding: 9px 12px 6px;
    margin-bottom: 10px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2px 20px;
}
.pv-info-row {
    display: flex;
    gap: 5px;
    font-size: 10px;
    padding: 1.5px 0;
}
.pv-info-label { font-weight: 700; white-space: nowrap; min-width: 90px; }
.pv-info-value { color: #333; word-break: break-word; }

/* Items table */
.pv-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 6px;
    font-size: 10px;
}
.pv-table thead tr th {
    background: #8B9B75;
    color: #fff;
    padding: 5px 8px;
    text-align: center;
    font-weight: 700;
    letter-spacing: 0.3px;
    border: 1px solid #7a8a66;
}
.pv-table thead tr th:nth-child(2) { text-align: left; }
.pv-table tbody tr td {
    padding: 5px 8px;
    border: 1px solid #ddd;
    vertical-align: middle;
    text-align: center;
}
.pv-table tbody tr td:nth-child(2) { text-align: left; }
.pv-table tbody tr:nth-child(even) td { background: #f9f9f9; }
.pv-table tfoot tr td {
    border: none;
    padding: 2px 8px;
}

/* Totals section */
.pv-totals {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    padding-right: 4px;
    margin-bottom: 8px;
}
.pv-total-row {
    display: flex;
    gap: 12px;
    align-items: center;
    padding: 2px 0;
    font-size: 10px;
    min-width: 280px;
}
.pv-total-row .tlbl {
    text-align: right;
    flex: 1;
    font-weight: 600;
    color: #333;
}
.pv-total-row .tcur { color: #333; width: 12px; text-align: center; font-weight:600; }
.pv-total-row .tval {
    width: 110px;
    text-align: right;
    font-weight: 600;
    border-bottom: 1px solid #aaa;
}
.pv-total-row.grand .tlbl,
.pv-total-row.grand .tval {
    font-weight: 900;
    font-size: 11px;
    color: #000;
    border-bottom: 2px solid #333;
}

/* Note */
.pv-note {
    font-size: 9px;
    color: #2a6496;
    font-style: italic;
    margin-bottom: 10px;
}

/* Section band */
.pv-section-band {
    background: #8B9B75;
    color: #fff;
    text-align: center;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1px;
    padding: 5px;
    margin-bottom: 6px;
    margin-top: 10px;
    text-transform: uppercase;
}
.pv-section-body {
    font-size: 9.5px;
    color: #333;
    padding: 0 4px 6px;
    white-space: pre-line;
    line-height: 1.6;
}

/* Payment details */
.pv-payment {
    font-size: 9.5px;
    color: #222;
    padding: 6px 4px 2px;
    white-space: pre-line;
    line-height: 1.7;
    font-weight: 600;
}

/* ── ITEM PICKER MODAL ───────────────────────────────────────── */
.picker-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 999;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(4px);
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.picker-overlay.active { display: flex; }
.picker-box {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 18px;
    width: 100%;
    max-width: 520px;
    max-height: 78vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 24px 64px rgba(0,0,0,0.7);
    animation: popIn 0.2s ease;
}
@keyframes popIn {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}
.picker-head {
    padding: 1.2rem 1.5rem 0.9rem;
    border-bottom: 1px solid rgba(255,255,255,0.07);
}
.picker-head h3 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.15rem;
    letter-spacing: 0.1em;
    color: var(--sun);
    margin: 0 0 0.7rem;
}
.picker-search {
    width: 100%;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.85rem;
    padding: 0.5rem 0.85rem;
    outline: none;
    box-sizing: border-box;
    transition: border-color 0.2s;
}
.picker-search:focus { border-color: var(--sun); }
.picker-body { overflow-y: auto; flex: 1; padding: 0.6rem; }
.picker-sup-lbl {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.3);
    padding: 0.55rem 0.5rem 0.2rem;
}
.picker-item {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.6rem 0.7rem;
    border-radius: 9px;
    cursor: pointer;
    transition: background 0.12s;
}
.picker-item:hover { background: rgba(245,158,11,0.1); }
.picker-item-ico {
    width: 30px; height: 30px;
    border-radius: 7px;
    background: rgba(245,158,11,0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}
.picker-item-name { font-weight: 500; font-size: 0.86rem; }
.picker-item-meta { font-size: 0.72rem; color: rgba(255,255,255,0.35); margin-top: 1px; }
.picker-foot {
    padding: 0.8rem 1.2rem;
    border-top: 1px solid rgba(255,255,255,0.07);
}
.picker-manual {
    width: 100%;
    padding: 0.6rem;
    border-radius: 8px;
    border: 1px dashed rgba(255,255,255,0.2);
    background: none;
    color: rgba(255,255,255,0.45);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.82rem;
    cursor: pointer;
    transition: all 0.2s;
}
.picker-manual:hover { border-color: rgba(255,255,255,0.4); color: rgba(255,255,255,0.75); }
.picker-no-results { text-align:center; color: rgba(255,255,255,0.3); padding: 2rem; font-size:0.85rem; }

/* Page heading */
.pg-heading {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.pg-heading h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.75rem;
    letter-spacing: 0.1em;
    color: #fff;
    margin: 0;
}
.pg-heading h2 em { color: var(--sun); font-style: normal; }
.qnum-badge {
    margin-left: auto;
    background: rgba(245,158,11,0.1);
    border: 1px solid rgba(245,158,11,0.25);
    border-radius: 20px;
    padding: 0.3rem 0.9rem;
    font-size: 0.78rem;
    color: var(--sun);
    font-weight: 700;
    letter-spacing: 0.05em;
}

/* Preview wrapper label */
.preview-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.15em;
    color: rgba(255,255,255,0.3);
    text-transform: uppercase;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.preview-label::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(255,255,255,0.07);
}

/* Misc input-prefix for fee */
.fp-prefix-wrap { position:relative; display:flex; align-items:center; }
.fp-prefix { position:absolute; left:10px; color:var(--sun); font-weight:700; pointer-events:none; font-size:0.9rem; }
.fp-input.prefixed { padding-left:24px; }

@media (max-width: 1100px) {
    .form-panel { position: static; max-height: none; }
}
</style>
</head>
<body>

<div class="bg-orb orb-1"></div>
<div class="bg-orb orb-2"></div>

<!-- HEADER -->
<header class="header">
    <div class="header-inner">
        <div class="brand">
            <div class="brand-icon">☀</div>
            <div>
                <h1 class="brand-name">Suntastic</h1>
                <span class="brand-sub">SUPPLIER MANAGEMENT</span>
            </div>
        </div>
        <a href="/index.php" class="btn btn-outline">← Bumalik</a>
    </div>
</header>

<main class="main">

    <div class="pg-heading">
        <h2>Quotation <em>Builder</em></h2>
        <div class="qnum-badge" id="qNumBadge">QT-2605-0001</div>
    </div>

    <div class="qb-layout">

        <!-- ══ LEFT: FORM PANEL ══════════════════════════════════ -->
        <div class="form-panel">

            <!-- Company Info -->
            <div class="fp-card">
                <div class="fp-title">🏢 Company Info</div>
                <div class="fp-grid">
                    <div class="fp-group span2">
                        <label class="fp-label">Company Name</label>
                        <input class="fp-input" id="coName" value="SUNTASTIC SOLAR CORP." oninput="renderPreview()">
                    </div>
                    <div class="fp-group span2">
                        <label class="fp-label">Address</label>
                        <input class="fp-input" id="coAddr" value="Quezon City, Metro Manila" oninput="renderPreview()">
                    </div>
                    <div class="fp-group">
                        <label class="fp-label">Email</label>
                        <input class="fp-input" id="coEmail" value="info@suntastic.ph" oninput="renderPreview()">
                    </div>
                    <div class="fp-group">
                        <label class="fp-label">Telephone</label>
                        <input class="fp-input" id="coTel" value="+63 917 000 0000" oninput="renderPreview()">
                    </div>
                </div>
            </div>

            <!-- Client Info -->
            <div class="fp-card">
                <div class="fp-title">👤 Client Info</div>
                <div class="fp-grid">
                    <div class="fp-group">
                        <label class="fp-label">Client Name</label>
                        <input class="fp-input" id="clientName" placeholder="Juan dela Cruz" oninput="renderPreview()">
                    </div>
                    <div class="fp-group">
                        <label class="fp-label">Company / Client Co.</label>
                        <input class="fp-input" id="clientCo" placeholder="ABC Corporation" oninput="renderPreview()">
                    </div>
                    <div class="fp-group span2">
                        <label class="fp-label">Address</label>
                        <input class="fp-input" id="clientAddr" placeholder="Street, Barangay, City" oninput="renderPreview()">
                    </div>
                    <div class="fp-group">
                        <label class="fp-label">Contact No.</label>
                        <input class="fp-input" id="clientContact" placeholder="09XX-XXX-XXXX" oninput="renderPreview()">
                    </div>
                    <div class="fp-group">
                        <label class="fp-label">TIN No.</label>
                        <input class="fp-input" id="clientTin" placeholder="000-000-000" oninput="renderPreview()">
                    </div>
                    <div class="fp-group">
                        <label class="fp-label">Date</label>
                        <input class="fp-input" type="date" id="qDate" oninput="renderPreview()">
                    </div>
                    <div class="fp-group">
                        <label class="fp-label">Valid Until</label>
                        <input class="fp-input" type="date" id="qValid" oninput="renderPreview()">
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="fp-card">
                <div class="fp-title">🔆 Items</div>
                <div id="itemsList"></div>
                <button class="btn-add-item" onclick="addItem()">＋ Magdagdag ng Item</button>
            </div>

            <!-- Charges & Toggles -->
            <div class="fp-card">
                <div class="fp-title">💰 Charges</div>

                <div class="toggle-row">
                    <span>Kasama ang Delivery Fee?</span>
                    <label class="sw"><input type="checkbox" id="delivToggle" onchange="renderPreview()"><span class="sw-track"></span></label>
                </div>
                <div id="delivRow" style="display:none; padding: 0.5rem 0 0.2rem;">
                    <div class="fp-prefix-wrap">
                        <span class="fp-prefix">₱</span>
                        <input class="fp-input prefixed" type="number" id="delivFee" placeholder="0.00" min="0" step="0.01" oninput="renderPreview()">
                    </div>
                </div>

                <div class="toggle-row" style="margin-top:0.5rem">
                    <span>Kasama ang VAT (12%)?</span>
                    <label class="sw"><input type="checkbox" id="vatToggle" onchange="renderPreview()"><span class="sw-track"></span></label>
                </div>

                <div class="toggle-row">
                    <span>May Discount?</span>
                    <label class="sw"><input type="checkbox" id="discToggle" onchange="toggleDisc()"><span class="sw-track"></span></label>
                </div>
                <div id="discRow" style="display:none; padding: 0.5rem 0 0.2rem;">
                    <div style="display:flex; gap:0.4rem; align-items:center;">
                        <div class="fp-prefix-wrap" style="flex:1">
                            <span class="fp-prefix">₱</span>
                            <input class="fp-input prefixed" type="number" id="discAmt" placeholder="0.00" min="0" step="0.01" oninput="renderPreview()">
                        </div>
                        <select class="fp-input" id="discType" style="width:65px" onchange="renderPreview()">
                            <option value="fixed">₱</option>
                            <option value="pct">%</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="fp-card">
                <div class="fp-title">📝 Note</div>
                <div class="fp-group">
                    <textarea class="fp-input" id="qNote" rows="2" oninput="renderPreview()">Note: Prices quoted above are VAT exclusive. Should you require an official VAT Sales Invoice, 12% VAT will be added on top of the total amount.</textarea>
                </div>
            </div>

            <!-- Warranty Terms -->
            <div class="fp-card">
                <div class="fp-title">🛡 Warranty Terms &amp; Conditions</div>
                <div class="fp-group">
                    <textarea class="fp-input" id="qWarranty" rows="5" oninput="renderPreview()">1. Warranty Period:
Twelve (12) year warranty on Solar Panels. Five (5) year warranty on Inverters. Two (2) year warranty on Batteries.
2. Return Requirements:
Defective unit must be returned with the box and complete accessories.
3. Shipping Costs:
The purchaser shoulders the shipping fee of both the defective unit and the replacement.
4. Transferability:
This warranty is non-transferable.</textarea>
                </div>
            </div>

            <!-- Warranty Exclusions -->
            <div class="fp-card">
                <div class="fp-title">⚠ Warranty Exclusions</div>
                <div class="fp-group">
                    <textarea class="fp-input" id="qExclusions" rows="4" oninput="renderPreview()">The warranty will be null and void under the following circumstances:
1. Force Majeure / Acts of God: Damages due to typhoons, floods, fire, lightning, earthquakes, etc.
2. Improper Handling: Damages caused by accident, misuse, abuse, faulty or improper installation, poor or lack of maintenance.
3. Tampered Serial Number: If the product's serial number is removed, obliterated, tampered with, or defaced.</textarea>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="fp-card">
                <div class="fp-title">🏦 Payment Details</div>
                <div class="fp-group">
                    <textarea class="fp-input" id="qPayment" rows="3" oninput="renderPreview()">Payment Details: Banks Transfer or Deposit
BDO Unibank (Banco De Oro)
Account Number: 0000-0000-0000
Account Name: SUNTASTIC SOLAR CORP.</textarea>
                </div>
            </div>

            <!-- Export -->
            <div class="fp-card">
                <div class="fp-title">📤 I-export</div>
                <button class="btn-xl btn-xl-pdf" onclick="exportPDF()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    I-export bilang PDF
                </button>
                <button class="btn-xl btn-xl-excel" onclick="exportExcel()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
                    I-export bilang Excel
                </button>
            </div>

        </div><!-- /.form-panel -->

        <!-- ══ RIGHT: LIVE PREVIEW ═══════════════════════════════ -->
        <div>
            <div class="preview-label">Live Preview <span style="color:var(--sun)">●</span></div>
            <div class="preview-panel">
                <div class="pv" id="previewContent">
                    <!-- rendered by JS -->
                </div>
            </div>
        </div>

    </div><!-- /.qb-layout -->

</main>

<!-- ITEM PICKER MODAL -->
<div id="pickerOverlay" class="picker-overlay">
    <div class="picker-box">
        <div class="picker-head">
            <h3>🔍 Pumili ng Item mula sa Supplier</h3>
            <input type="text" id="pickerSearch" class="picker-search"
                placeholder="Hanapin ang item o supplier..." oninput="filterPicker(this.value)">
        </div>
        <div class="picker-body" id="pickerBody"></div>
        <div class="picker-foot">
            <button class="picker-manual" onclick="pickManual()">✏ Mag-type ng sariling item (manual)</button>
        </div>
    </div>
</div>

<script>
// ── DATA ─────────────────────────────────────────────────────
const ITEMS_DB = <?= $itemsJson ?>;

// ── INIT ─────────────────────────────────────────────────────
let rowId = 0;
let pickerTarget = null;

document.addEventListener('DOMContentLoaded', function() {
    // Set dates
    const today = new Date();
    document.getElementById('qDate').value = today.toISOString().slice(0,10);
    const valid = new Date(today); valid.setDate(valid.getDate()+30);
    document.getElementById('qValid').value = valid.toISOString().slice(0,10);

    // Generate QT number
    const yr = String(today.getFullYear()).slice(-2);
    const mo = String(today.getMonth()+1).padStart(2,'0');
    const rnd = String(Math.floor(Math.random()*9000)+1000);
    document.getElementById('qNumBadge').textContent = `QT-${yr}${mo}-${rnd}`;

    // Add 2 default rows
    addItem(); addItem();
    renderPreview();
});

// ── ITEM ROWS ─────────────────────────────────────────────────
function addItem(name='', desc='', qty=1, price='', unit='pc/s') {
    rowId++;
    const id = rowId;
    const el = document.createElement('div');
    el.className = 'item-row-ui';
    el.dataset.id = id;
    el.innerHTML = `
        <span class="item-row-num">#<span class="rnum">${id}</span></span>
        <button class="btn-del-row" onclick="delItem(${id})">✕</button>
        <div class="item-row-grid">
            <div class="fp-group span2">
                <label class="fp-label">Item / Produkto</label>
                <div class="item-name-wrap">
                    <input class="fp-input" data-field="name" placeholder="Pangalan ng item..."
                        value="${escHtml(name)}" oninput="renderPreview()">
                    <button class="btn-pick-sm" onclick="openPicker(${id})">🔍 Supplier</button>
                </div>
            </div>
            <div class="fp-group span2">
                <label class="fp-label">Description / Specs</label>
                <input class="fp-input" data-field="desc" placeholder="Model, brand, specs..."
                    value="${escHtml(desc)}" oninput="renderPreview()">
            </div>
            <div class="fp-group">
                <label class="fp-label">Qty</label>
                <input class="fp-input" type="number" data-field="qty"
                    value="${qty}" min="1" oninput="calcRow(${id}); renderPreview()">
            </div>
            <div class="fp-group">
                <label class="fp-label">Unit</label>
                <select class="fp-input" data-field="unit" onchange="renderPreview()">
                    ${['pc/s','sets','kW','kWh','unit','lot','roll','meter','pair'].map(u=>`<option ${u===unit?'selected':''}>${u}</option>`).join('')}
                </select>
            </div>
            <div class="fp-group span2">
                <label class="fp-label">Unit Price (₱)</label>
                <div class="fp-prefix-wrap">
                    <span class="fp-prefix">₱</span>
                    <input class="fp-input prefixed" type="number" data-field="price"
                        value="${price}" min="0" step="0.01" placeholder="0.00"
                        oninput="calcRow(${id}); renderPreview()">
                </div>
            </div>
            <div class="item-amt-display" id="amt-${id}">
                <span>Total</span> ₱0.00
            </div>
        </div>`;
    document.getElementById('itemsList').appendChild(el);
    calcRow(id);
}

function delItem(id) {
    const el = document.querySelector(`.item-row-ui[data-id="${id}"]`);
    if (el) el.remove();
    renumberRows();
    renderPreview();
}

function renumberRows() {
    document.querySelectorAll('.item-row-ui').forEach((el,i) => {
        el.querySelector('.rnum').textContent = i+1;
    });
}

function calcRow(id) {
    const el = document.querySelector(`.item-row-ui[data-id="${id}"]`);
    if (!el) return;
    const qty   = parseFloat(el.querySelector('[data-field="qty"]').value) || 0;
    const price = parseFloat(el.querySelector('[data-field="price"]').value) || 0;
    const amt   = qty * price;
    const amtEl = document.getElementById(`amt-${id}`);
    if (amtEl) amtEl.innerHTML = `<span>Total</span> ₱${fmt(amt)}`;
}

function getRows() {
    const rows = [];
    document.querySelectorAll('.item-row-ui').forEach((el,i) => {
        rows.push({
            num:   i+1,
            name:  el.querySelector('[data-field="name"]').value || '',
            desc:  el.querySelector('[data-field="desc"]').value || '',
            qty:   parseFloat(el.querySelector('[data-field="qty"]').value) || 0,
            unit:  el.querySelector('[data-field="unit"]').value || 'pc/s',
            price: parseFloat(el.querySelector('[data-field="price"]').value) || 0,
            amt:   (parseFloat(el.querySelector('[data-field="qty"]').value)||0) *
                   (parseFloat(el.querySelector('[data-field="price"]').value)||0),
        });
    });
    return rows;
}

// ── TOTALS CALCULATION ────────────────────────────────────────
function calcTotals(rows) {
    const subtotal = rows.reduce((s,r) => s + r.amt, 0);
    const vatOn    = document.getElementById('vatToggle').checked;
    const delivOn  = document.getElementById('delivToggle').checked;
    const discOn   = document.getElementById('discToggle').checked;

    const delivFee = delivOn ? (parseFloat(document.getElementById('delivFee').value)||0) : 0;

    let discount = 0;
    if (discOn) {
        const dv = parseFloat(document.getElementById('discAmt').value)||0;
        discount = document.getElementById('discType').value === 'pct'
            ? subtotal * (dv/100) : dv;
    }

    const afterDisc = subtotal - discount;
    const vat       = vatOn ? afterDisc * 0.12 : 0;
    const total     = afterDisc + vat + delivFee;

    return { subtotal, discount, delivFee, vat, total, vatOn, delivOn, discOn };
}

// ── LIVE PREVIEW RENDER ───────────────────────────────────────
function renderPreview() {
    const coName   = v('coName');
    const coAddr   = v('coAddr');
    const coEmail  = v('coEmail');
    const coTel    = v('coTel');
    const client   = v('clientName');
    const clientCo = v('clientCo');
    const clientAddr = v('clientAddr');
    const contact  = v('clientContact');
    const tin      = v('clientTin');
    const qDate    = fmtDate(v('qDate'));
    const qValid   = fmtDate(v('qValid'));
    const qNum     = document.getElementById('qNumBadge').textContent;
    const note     = v('qNote');
    const warranty = v('qWarranty');
    const excl     = v('qExclusions');
    const payment  = v('qPayment');

    const rows  = getRows();
    const tots  = calcTotals(rows);

    // Items rows HTML
    let itemsHtml = rows.map((r,i) => `
        <tr style="${i%2===0?'':'background:#f9f9f9'}">
            <td>${r.num}</td>
            <td style="text-align:left">${he(r.name)}${r.desc?`<br><span style="font-size:9px;color:#888">${he(r.desc)}</span>`:''}
            </td>
            <td>${r.qty}</td>
            <td>${he(r.unit)}</td>
            <td style="text-align:right">₱ ${fmt(r.price)}</td>
            <td style="text-align:right">₱ ${fmt(r.amt)}</td>
        </tr>`).join('');

    if (!itemsHtml) itemsHtml = `<tr><td colspan="6" style="color:#bbb;text-align:center;padding:12px;font-size:10px;">Walang items pa</td></tr>`;

    // Totals HTML
    let totHtml = `
        <div class="pv-total-row">
            <span class="tlbl">Subtotal (VAT Exclusive):</span>
            <span class="tcur">₱</span>
            <span class="tval">${fmt(tots.subtotal)}</span>
        </div>`;

    if (tots.discOn && tots.discount > 0) {
        totHtml += `<div class="pv-total-row">
            <span class="tlbl">Discount:</span>
            <span class="tcur">₱</span>
            <span class="tval">(${fmt(tots.discount)})</span>
        </div>`;
    }

    totHtml += `<div class="pv-total-row">
        <span class="tlbl">Delivery Fee:</span>
        <span class="tcur">₱</span>
        <span class="tval">${tots.delivOn && tots.delivFee > 0 ? fmt(tots.delivFee) : '-'}</span>
    </div>
    <div class="pv-total-row">
        <span class="tlbl">Vat 12%:</span>
        <span class="tcur">₱</span>
        <span class="tval">${tots.vatOn ? fmt(tots.vat) : '-'}</span>
    </div>
    <div class="pv-total-row grand">
        <span class="tlbl">TOTAL AMOUNT:</span>
        <span class="tcur">₱</span>
        <span class="tval">${fmt(tots.total)}</span>
    </div>`;

    document.getElementById('previewContent').innerHTML = `
        <!-- Ref line -->
        <div class="pv-ref-line">
            <span>Suntastic Solar — Quotation</span>
            <span>${he(qNum)}</span>
        </div>

        <!-- Company Header -->
        <div class="pv-company-header">
            <div class="pv-company-logo">
            <img src="/assets/images/suntastic_logo_png.png" alt="Logo" style="width:32px; height:32px; border-radius:6px; background:#fff; padding:4px">
            </div>
            <div class="pv-company-name">${he(coName)}</div>
            <div class="pv-company-tagline">Solar Energy Solutions</div>
            <div class="pv-company-addr">
                ${he(coAddr)}<br>
                Email: ${he(coEmail)}&nbsp;&nbsp;&nbsp;Tel: ${he(coTel)}
            </div>
        </div>

        <!-- Title -->
        <div class="pv-title-band">QUOTATION SLIP</div>

        <!-- Info Box -->
        <div class="pv-info-box">
            <div>
                <div class="pv-info-row"><span class="pv-info-label">Date:</span><span class="pv-info-value">${he(qDate)}</span></div>
                <div class="pv-info-row"><span class="pv-info-label">Company Name:</span><span class="pv-info-value">${he(clientCo)}</span></div>
                <div class="pv-info-row"><span class="pv-info-label">Client Name:</span><span class="pv-info-value">${he(client)}</span></div>
                <div class="pv-info-row"><span class="pv-info-label">Tin No.:</span><span class="pv-info-value">${he(tin)}</span></div>
            </div>
            <div>
                <div class="pv-info-row"><span class="pv-info-label">Valid Until:</span><span class="pv-info-value">${he(qValid)}</span></div>
                <div class="pv-info-row"><span class="pv-info-label">Address:</span><span class="pv-info-value">${he(clientAddr)}</span></div>
                <div class="pv-info-row"><span class="pv-info-label">&nbsp;</span></div>
                <div class="pv-info-row"><span class="pv-info-label">Contact No.:</span><span class="pv-info-value">${he(contact)}</span></div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="pv-table">
            <thead>
                <tr>
                    <th style="width:24px">#</th>
                    <th style="text-align:left">Item Description</th>
                    <th style="width:36px">Qty</th>
                    <th style="width:40px">Unit</th>
                    <th style="width:80px">Unit Price</th>
                    <th style="width:80px">Total</th>
                </tr>
            </thead>
            <tbody>${itemsHtml}</tbody>
        </table>

        <!-- Totals -->
        <div class="pv-totals" style="margin-top:8px">
            ${totHtml}
        </div>

        <!-- Note -->
        ${note ? `<div class="pv-note">${he(note)}</div>` : ''}

        <!-- Warranty T&C -->
        ${warranty ? `
        <div class="pv-section-band">Warranty Terms and Conditions</div>
        <div class="pv-section-body">${he(warranty)}</div>
        ` : ''}

        <!-- Warranty Exclusions -->
        ${excl ? `
        <div class="pv-section-band">Warranty Exclusions</div>
        <div class="pv-section-body">${he(excl)}</div>
        ` : ''}

        <!-- Payment -->
        ${payment ? `<div class="pv-payment">${he(payment)}</div>` : ''}
    `;
}

// ── ITEM PICKER ───────────────────────────────────────────────
function openPicker(id) {
    pickerTarget = id;
    document.getElementById('pickerSearch').value = '';
    renderPicker(ITEMS_DB);
    document.getElementById('pickerOverlay').classList.add('active');
    setTimeout(() => document.getElementById('pickerSearch').focus(), 60);
}

function closePicker() {
    document.getElementById('pickerOverlay').classList.remove('active');
    pickerTarget = null;
}

function pickItem(name, supplier) {
    if (!pickerTarget) return;
    const el = document.querySelector(`.item-row-ui[data-id="${pickerTarget}"]`);
    if (el) {
        el.querySelector('[data-field="name"]').value = name;
        el.querySelector('[data-field="desc"]').value = supplier;
        el.querySelector('[data-field="price"]').focus();
    }
    closePicker();
    renderPreview();
}

function pickManual() {
    if (!pickerTarget) return;
    const el = document.querySelector(`.item-row-ui[data-id="${pickerTarget}"]`);
    if (el) el.querySelector('[data-field="name"]').focus();
    closePicker();
}

function filterPicker(q) {
    const f = q ? ITEMS_DB.filter(i =>
        i.item_name.toLowerCase().includes(q.toLowerCase()) ||
        i.supplier_name.toLowerCase().includes(q.toLowerCase())
    ) : ITEMS_DB;
    renderPicker(f);
}

function renderPicker(items) {
    if (!items || !items.length) {
        document.getElementById('pickerBody').innerHTML = `<div class="picker-no-results">Walang nahanap.</div>`;
        return;
    }
    const groups = {};
    items.forEach(it => {
        if (!groups[it.supplier_name]) groups[it.supplier_name] = [];
        groups[it.supplier_name].push(it);
    });
    let html = '';
    Object.keys(groups).sort().forEach(s => {
        html += `<div class="picker-sup-lbl">🏭 ${he(s)}</div>`;
        groups[s].forEach(it => {
            html += `<div class="picker-item" onclick="pickItem('${escJs(it.item_name)}','${escJs(it.supplier_name)}')">
                <div class="picker-item-ico">${itemIcon(it.item_name)}</div>
                <div>
                    <div class="picker-item-name">${he(it.item_name)}</div>
                    <div class="picker-item-meta">Stock: ${it.quantity} &nbsp;·&nbsp; Cost: ₱${Number(it.price||0).toLocaleString('en-PH',{minimumFractionDigits:2})}</div>
                </div>
            </div>`;
        });
    });
    document.getElementById('pickerBody').innerHTML = html;
}

document.getElementById('pickerOverlay').addEventListener('click', e => { if (e.target === document.getElementById('pickerOverlay')) closePicker(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closePicker(); });

function toggleDisc() {
    document.getElementById('discRow').style.display = document.getElementById('discToggle').checked ? '' : 'none';
    renderPreview();
}
document.getElementById('delivToggle').addEventListener('change', function() {
    document.getElementById('delivRow').style.display = this.checked ? '' : 'none';
    renderPreview();
});

// ── PDF EXPORT ────────────────────────────────────────────────
function exportPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });
    const pw = doc.internal.pageSize.getWidth();
    const ph = doc.internal.pageSize.getHeight();
    const GREEN = [139, 155, 117];
    const DARKGRAY = [30, 30, 30];

    const qNum     = document.getElementById('qNumBadge').textContent;
    const coName   = v('coName');
    const coAddr   = v('coAddr');
    const coEmail  = v('coEmail');
    const coTel    = v('coTel');
    const client   = v('clientName');
    const clientCo = v('clientCo');
    const clientAddr = v('clientAddr');
    const contact  = v('clientContact');
    const tin      = v('clientTin');
    const qDate    = fmtDate(v('qDate'));
    const qValid   = fmtDate(v('qValid'));
    const note     = v('qNote');
    const warranty = v('qWarranty');
    const excl     = v('qExclusions');
    const payment  = v('qPayment');

    const rows = getRows();
    const tots = calcTotals(rows);

    let y = 10;

    // Ref line
    doc.setFontSize(7.5); doc.setTextColor(120,120,120); doc.setFont('helvetica','normal');
    doc.text('Suntastic Solar — Quotation', 14, y);
    doc.text(qNum, pw-14, y, { align:'right' });
    y += 8;

    // Company header (centered)
    doc.setFontSize(14); doc.setFont('helvetica','bold');
    doc.setTextColor(...GREEN);
    doc.text(coName, pw/2, y, { align:'center' }); y += 5;
    doc.setFontSize(7); doc.setTextColor(140,140,140); doc.setFont('helvetica','normal');
    doc.text('SOLAR ENERGY SOLUTIONS', pw/2, y, { align:'center' }); y += 4;
    doc.setFontSize(8.5); doc.setTextColor(60,60,60);
    doc.text(coAddr, pw/2, y, { align:'center' }); y += 4;
    doc.text(`Email: ${coEmail}   Tel: ${coTel}`, pw/2, y, { align:'center' }); y += 5;

    // Divider
    doc.setDrawColor(200,200,200); doc.line(14, y, pw-14, y); y += 5;

    // QUOTATION SLIP title band
    doc.setFillColor(...GREEN);
    doc.rect(14, y, pw-28, 8, 'F');
    doc.setFontSize(11); doc.setFont('helvetica','bold'); doc.setTextColor(255,255,255);
    doc.text('QUOTATION SLIP', pw/2, y+5.5, { align:'center' }); y += 8;

    // Info box with border
    const infoH = 22;
    doc.setDrawColor(170,170,170);
    doc.rect(14, y, pw-28, infoH, 'S');
    doc.setFontSize(8.5); doc.setFont('helvetica','normal'); doc.setTextColor(50,50,50);

    const lx = 16, lx2 = pw/2 + 2;
    const infoRows = [
        ['Date:',         qDate,      'Valid Until:', qValid],
        ['Company Name:', clientCo,   'Address:',     clientAddr],
        ['Client Name:',  client,     '',             ''],
        ['Tin No.:',      tin,        'Contact No.:',  contact],
    ];
    let iy = y + 5;
    infoRows.forEach(row => {
        doc.setFont('helvetica','bold');  doc.text(row[0], lx, iy);
        doc.setFont('helvetica','normal'); doc.text(row[1], lx+26, iy, { maxWidth: pw/2-32 });
        doc.setFont('helvetica','bold');  doc.text(row[2], lx2, iy);
        doc.setFont('helvetica','normal'); doc.text(row[3], lx2+24, iy, { maxWidth: pw/2-28 });
        iy += 5;
    });
    y += infoH + 4;

    // Items table
    const tableBody = rows.map((r,i) => [
        r.num,
        r.name + (r.desc ? `\n${r.desc}` : ''),
        r.qty,
        r.unit,
        '₱ ' + fmt(r.price),
        '₱ ' + fmt(r.amt)
    ]);

    doc.autoTable({
        startY: y,
        head: [['#', 'Item Description', 'Qty', 'Unit', 'Unit Price', 'Total']],
        body: tableBody,
        theme: 'grid',
        headStyles: { fillColor: GREEN, textColor:[255,255,255], fontStyle:'bold', fontSize:8.5, halign:'center' },
        bodyStyles: { fontSize:8.5, textColor:[30,30,30] },
        alternateRowStyles: { fillColor:[249,249,249] },
        columnStyles: {
            0: { cellWidth:8,  halign:'center' },
            1: { cellWidth:64 },
            2: { cellWidth:12, halign:'center' },
            3: { cellWidth:16, halign:'center' },
            4: { cellWidth:28, halign:'right' },
            5: { cellWidth:28, halign:'right' },
        },
        margin: { left:14, right:14 },
        styles: { overflow:'linebreak' },
    });
    y = doc.lastAutoTable.finalY + 6;

    // Totals
    const totLines = [
        ['Subtotal (VAT Exclusive):', tots.subtotal],
    ];
    if (tots.discOn && tots.discount > 0) totLines.push([`Discount:`, -tots.discount]);
    totLines.push(['Delivery Fee:', tots.delivOn ? tots.delivFee : null]);
    totLines.push(['Vat 12%:', tots.vatOn ? tots.vat : null]);
    totLines.push(['TOTAL AMOUNT:', tots.total]);

    const totX   = pw - 14 - 65;
    const lblW   = 44;
    const symX   = totX + lblW + 1;
    const valX   = pw - 14;

    totLines.forEach((line, i) => {
        const isTotal = i === totLines.length - 1;
        if (isTotal) {
            doc.setFillColor(...GREEN);
            doc.rect(totX, y-3.5, 65, 7, 'F');
        }
        doc.setFont('helvetica', isTotal ? 'bold' : 'normal');
        doc.setFontSize(isTotal ? 9.5 : 8.5);
        doc.setTextColor(isTotal ? 255 : 60, isTotal ? 255 : 60, isTotal ? 255 : 60);
        doc.text(line[0], totX + lblW, y, { align:'right' });
        doc.text('₱', symX, y);
        const val = line[1] === null ? '-' : (line[1] < 0 ? `(${fmt(-line[1])})` : fmt(line[1]));
        doc.text(val, valX, y, { align:'right' });

        if (!isTotal) {
            doc.setDrawColor(180,180,180);
            doc.line(symX-1, y+1.5, valX, y+1.5);
        }
        y += 6;
    });
    y += 4;

    // Note (blue italic)
    if (note) {
        doc.setFont('helvetica','italic'); doc.setFontSize(8); doc.setTextColor(42,100,150);
        const noteLines = doc.splitTextToSize(note, pw-28);
        doc.text(noteLines, 14, y); y += noteLines.length * 4 + 4;
    }

    // Warranty section helper
    function drawSection(title, body) {
        if (!body) return;
        if (y > ph - 40) { doc.addPage(); y = 14; }
        doc.setFillColor(...GREEN);
        doc.rect(14, y, pw-28, 7, 'F');
        doc.setFont('helvetica','bold'); doc.setFontSize(9); doc.setTextColor(255,255,255);
        doc.text(title, pw/2, y+4.8, { align:'center' });
        y += 9;
        doc.setFont('helvetica','normal'); doc.setFontSize(8.5); doc.setTextColor(40,40,40);
        const lines = doc.splitTextToSize(body, pw-28);
        if (y + lines.length*4.5 > ph-20) { doc.addPage(); y = 14; }
        doc.text(lines, 14, y);
        y += lines.length * 4.5 + 5;
    }

    drawSection('Warranty Terms and Conditions', warranty);
    drawSection('Warranty Exclusions', excl);

    // Payment
    if (payment) {
        if (y > ph - 30) { doc.addPage(); y = 14; }
        doc.setFont('helvetica','bold'); doc.setFontSize(8.5); doc.setTextColor(20,20,20);
        const pLines = doc.splitTextToSize(payment, pw-28);
        doc.text(pLines, 14, y); y += pLines.length * 4.5 + 5;
    }

    // Footer line
    doc.setDrawColor(200,200,200); doc.line(14, ph-12, pw-14, ph-12);
    doc.setFont('helvetica','normal'); doc.setFontSize(7); doc.setTextColor(160,160,160);
    doc.text('Suntastic Solar — Supplier Management System', 14, ph-7);
    doc.text(`Generated: ${new Date().toLocaleDateString('en-PH')}`, pw-14, ph-7, { align:'right' });

    doc.save(`${qNum}_Quotation.pdf`);
}

// ── EXCEL EXPORT ──────────────────────────────────────────────
function exportExcel() {
    const qNum   = document.getElementById('qNumBadge').textContent;
    const rows   = getRows();
    const tots   = calcTotals(rows);

    const wb = XLSX.utils.book_new();
    const data = [
        [v('coName')],
        [v('coAddr')],
        [`Email: ${v('coEmail')}   Tel: ${v('coTel')}`],
        [],
        ['QUOTATION SLIP'],
        [],
        ['Date:', fmtDate(v('qDate')), '', 'Valid Until:', fmtDate(v('qValid'))],
        ['Company Name:', v('clientCo'), '', 'Address:', v('clientAddr')],
        ['Client Name:', v('clientName'), '', 'Contact No.:', v('clientContact')],
        ['TIN No.:', v('clientTin')],
        [],
        ['#','Item Description','Description / Specs','Qty','Unit','Unit Price (₱)','Total (₱)'],
        ...rows.map(r => [r.num, r.name, r.desc, r.qty, r.unit, r.price, r.amt]),
        [],
        ['','','','','Subtotal (VAT Exclusive):','₱', tots.subtotal],
        ...(tots.discOn && tots.discount > 0 ? [['','','','','Discount:','₱', -tots.discount]] : []),
        ['','','','','Delivery Fee:','₱', tots.delivOn ? tots.delivFee : '-'],
        ['','','','','VAT (12%):','₱', tots.vatOn ? tots.vat : '-'],
        ['','','','','TOTAL AMOUNT:','₱', tots.total],
        [],
        ['Note:', v('qNote')],
        [],
        ['Warranty Terms & Conditions:'],
        [v('qWarranty')],
        [],
        ['Warranty Exclusions:'],
        [v('qExclusions')],
        [],
        [v('qPayment')],
    ];

    const ws = XLSX.utils.aoa_to_sheet(data);
    ws['!cols'] = [{wch:6},{wch:32},{wch:28},{wch:6},{wch:10},{wch:16},{wch:16}];
    XLSX.utils.book_append_sheet(wb, ws, 'Quotation');
    XLSX.writeFile(wb, `${qNum}_Quotation.xlsx`);
}

// ── HELPERS ───────────────────────────────────────────────────
function v(id) { return (document.getElementById(id)||{}).value || ''; }
function he(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function escHtml(s) { return he(s).replace(/"/g,'&quot;'); }
function escJs(s) { return String(s||'').replace(/\\/g,'\\\\').replace(/'/g,"\\'"); }
function fmt(n) { return Number(n||0).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function fmtDate(d) {
    if (!d) return '';
    const parts = d.split('-');
    if (parts.length !== 3) return d;
    const mo = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return `${mo[parseInt(parts[1])-1]} ${parseInt(parts[2])}, ${parts[0]}`;
}
function itemIcon(n) {
    n = (n||'').toLowerCase();
    if (n.includes('panel')) return '🔆';
    if (n.includes('inverter')) return '⚡';
    if (n.includes('battery') || n.includes('batter')) return '🔋';
    if (n.includes('cable') || n.includes('wire')) return '🔌';
    if (n.includes('mount') || n.includes('rack')) return '🔩';
    return '📦';
}
</script>

</body>
</html>