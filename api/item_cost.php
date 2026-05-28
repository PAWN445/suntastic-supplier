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
    <title>Item Cost — Suntastic</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Semi+Condensed:wght@600;700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<style>
/* ── PAGE LAYOUT ──────────────────────────────────────── */
.qb-layout {
    display: grid;
    grid-template-columns: 420px 1fr;
    gap: 1.75rem;
    align-items: start;
}
@media (max-width: 1100px) { .qb-layout { grid-template-columns: 1fr; } }

/* ── FORM PANEL ───────────────────────────────────────── */
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
@media (max-width: 1100px) { .form-panel { position: static; max-height: none; } }

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
textarea.fp-input { resize: vertical; min-height: 60px; line-height: 1.5; }

.fp-prefix-wrap { position: relative; display: flex; align-items: center; }
.fp-prefix { position: absolute; left: 10px; color: var(--sun); font-weight: 700; pointer-events: none; font-size: 0.9rem; }
.fp-input.prefixed { padding-left: 24px; }

/* ── ITEM ROWS ────────────────────────────────────────── */
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
    top: 8px; left: 10px;
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
    top: 8px; right: 8px;
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
.item-amt-display span {
    font-size: 0.65rem;
    color: rgba(255,255,255,0.3);
    font-family: 'DM Sans', sans-serif;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

/* ── IMAGE UPLOAD ─────────────────────────────────────── */
.img-upload-zone {
    border: 1.5px dashed rgba(245,158,11,0.35);
    border-radius: 8px;
    padding: 0.6rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: rgba(245,158,11,0.03);
    position: relative;
    overflow: hidden;
    min-height: 72px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 4px;
}
.img-upload-zone:hover { border-color: var(--sun); background: rgba(245,158,11,0.06); }
.img-upload-zone input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
}
.img-upload-placeholder {
    font-size: 0.72rem;
    color: rgba(255,255,255,0.3);
    line-height: 1.4;
    pointer-events: none;
}
.img-upload-placeholder .ico { font-size: 1.2rem; display: block; margin-bottom: 2px; }
.img-preview-thumb {
    width: 100%;
    height: 68px;
    object-fit: contain;
    border-radius: 5px;
    background: rgba(255,255,255,0.04);
    display: none;
    pointer-events: none;
}
.img-remove-btn {
    position: absolute;
    top: 4px; right: 4px;
    background: rgba(239,68,68,0.8);
    border: none;
    border-radius: 4px;
    color: #fff;
    font-size: 0.65rem;
    padding: 2px 5px;
    cursor: pointer;
    display: none;
    z-index: 2;
    pointer-events: all;
}

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

/* ── TOGGLE SWITCHES ──────────────────────────────────── */
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
    border-radius: 20px;
    cursor: pointer;
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

/* ── EXPORT / SAVE BUTTONS ────────────────────────────── */
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
.btn-xl:hover  { filter: brightness(1.1); transform: translateY(-1px); }
.btn-xl:active { transform: translateY(0); }
.btn-xl-pdf    { background: linear-gradient(135deg,#c0392b,#e74c3c); color: #fff; }
.btn-xl-excel  { background: linear-gradient(135deg,#1d6f42,#21a366); color: #fff; }
.btn-xl-save   { background: linear-gradient(135deg,#1e40af,#3b82f6); color: #fff; }

.btn-xl-load {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.12);
    color: rgba(255,255,255,0.7);
    font-family: 'DM Sans', sans-serif;
    font-weight: 700;
    font-size: 0.875rem;
    width: 100%;
    padding: 0.75rem;
    border-radius: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s;
    margin-bottom: 0.5rem;
}
.btn-xl-load:hover { background: rgba(255,255,255,0.1); color: #fff; border-color: rgba(255,255,255,0.25); }

/* ── SAVED RECORD ID BADGE ────────────────────────────── */
.saved-id-bar {
    display: none;
    align-items: center;
    gap: 0.5rem;
    background: rgba(59,130,246,0.08);
    border: 1px solid rgba(59,130,246,0.2);
    border-radius: 8px;
    padding: 0.45rem 0.75rem;
    font-size: 0.75rem;
    color: #93c5fd;
    margin-bottom: 0.5rem;
}
.saved-id-bar.visible { display: flex; }
.saved-id-bar button {
    margin-left: auto;
    background: rgba(239,68,68,0.15);
    border: 1px solid rgba(239,68,68,0.25);
    border-radius: 5px;
    color: #f87171;
    font-size: 0.7rem;
    padding: 2px 8px;
    cursor: pointer;
}
.saved-id-bar button:hover { background: rgba(239,68,68,0.3); }

/* ── LIVE PREVIEW PANEL ───────────────────────────────── */
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
.pv { padding: 28px 32px; }

.pv-ref-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    font-size: 9.5px;
    color: #555;
}
.pv-company-header {
    text-align: center;
    margin-bottom: 14px;
    padding-bottom: 12px;
    border-bottom: 1px solid #ddd;
}
.pv-company-logo    { font-size: 22px; margin-bottom: 2px; }
.pv-company-name    { font-size: 15px; font-weight: 900; letter-spacing: 1.5px; color: #1a5e1a; text-transform: uppercase; }
.pv-company-tagline { font-size: 8px; letter-spacing: 3px; color: #888; text-transform: uppercase; margin-bottom: 4px; }
.pv-company-addr    { font-size: 9px; color: #444; line-height: 1.6; }

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
.pv-info-box {
    border: 1px solid #bbb;
    border-top: none;
    padding: 9px 12px 6px;
    margin-bottom: 10px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2px 20px;
}
.pv-info-row     { display: flex; gap: 5px; font-size: 10px; padding: 1.5px 0; }
.pv-info-label   { font-weight: 700; white-space: nowrap; min-width: 90px; }
.pv-info-value   { color: #333; word-break: break-word; }

.pv-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; font-size: 10px; }
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
    padding: 6px 8px;
    border: 1px solid #ddd;
    vertical-align: middle;
    text-align: center;
}
.pv-table tbody tr td:nth-child(2) { text-align: left; }
.pv-table tbody tr:nth-child(even) td { background: #f9f9f9; }

.pv-item-img {
    width: 70px; height: 55px;
    object-fit: contain;
    border-radius: 4px;
    border: 1px solid #eee;
    background: #fafafa;
    display: block;
    margin: 0 auto;
}
.pv-item-no-img {
    width: 70px; height: 55px;
    background: #f5f5f5;
    border-radius: 4px;
    border: 1px dashed #ddd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin: 0 auto;
}

.pv-totals { display: flex; flex-direction: column; align-items: flex-end; padding-right: 4px; margin-bottom: 8px; }
.pv-total-row {
    display: flex;
    gap: 12px;
    align-items: center;
    padding: 2px 0;
    font-size: 10px;
    min-width: 280px;
}
.pv-total-row .tlbl { text-align: right; flex: 1; font-weight: 600; color: #333; }
.pv-total-row .tcur { color: #333; width: 12px; text-align: center; font-weight: 600; }
.pv-total-row .tval { width: 110px; text-align: right; font-weight: 600; border-bottom: 1px solid #aaa; }
.pv-total-row.grand .tlbl,
.pv-total-row.grand .tval { font-weight: 900; font-size: 11px; color: #000; border-bottom: 2px solid #333; }

.pv-note { font-size: 9px; color: #2a6496; font-style: italic; margin-bottom: 10px; }

/* ── ITEM PICKER MODAL ────────────────────────────────── */
.picker-overlay {
    display: none;
    position: fixed; inset: 0; z-index: 999;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(4px);
    align-items: center; justify-content: center;
    padding: 1rem;
}
.picker-overlay.active { display: flex; }
.picker-box {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 18px;
    width: 100%; max-width: 520px; max-height: 78vh;
    display: flex; flex-direction: column; overflow: hidden;
    box-shadow: 0 24px 64px rgba(0,0,0,0.7);
    animation: popIn 0.2s ease;
}
@keyframes popIn {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}
.picker-head { padding: 1.2rem 1.5rem 0.9rem; border-bottom: 1px solid rgba(255,255,255,0.07); }
.picker-head h3 { font-family: 'Bebas Neue', sans-serif; font-size: 1.15rem; letter-spacing: 0.1em; color: var(--sun); margin: 0 0 0.7rem; }
.picker-search {
    width: 100%; background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px; color: #fff;
    font-family: 'DM Sans', sans-serif; font-size: 0.85rem;
    padding: 0.5rem 0.85rem; outline: none; box-sizing: border-box;
    transition: border-color 0.2s;
}
.picker-search:focus { border-color: var(--sun); }
.picker-body { overflow-y: auto; flex: 1; padding: 0.6rem; }
.picker-sup-lbl { font-size: 0.68rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(255,255,255,0.3); padding: 0.55rem 0.5rem 0.2rem; }
.picker-item {
    display: flex; align-items: center; gap: 0.7rem;
    padding: 0.6rem 0.7rem; border-radius: 9px;
    cursor: pointer; transition: background 0.12s;
}
.picker-item:hover { background: rgba(245,158,11,0.1); }
.picker-item-ico { width: 30px; height: 30px; border-radius: 7px; background: rgba(245,158,11,0.12); display: flex; align-items: center; justify-content: center; font-size: 0.9rem; flex-shrink: 0; }
.picker-item-name { font-weight: 500; font-size: 0.86rem; }
.picker-item-meta { font-size: 0.72rem; color: rgba(255,255,255,0.35); margin-top: 1px; }
.picker-foot { padding: 0.8rem 1.2rem; border-top: 1px solid rgba(255,255,255,0.07); }
.picker-manual { width: 100%; padding: 0.6rem; border-radius: 8px; border: 1px dashed rgba(255,255,255,0.2); background: none; color: rgba(255,255,255,0.45); font-family: 'DM Sans', sans-serif; font-size: 0.82rem; cursor: pointer; transition: all 0.2s; }
.picker-manual:hover { border-color: rgba(255,255,255,0.4); color: rgba(255,255,255,0.75); }
.picker-no-results { text-align: center; color: rgba(255,255,255,0.3); padding: 2rem; font-size: 0.85rem; }

/* ── LOAD RECORDS MODAL ───────────────────────────────── */
.load-overlay {
    display: none;
    position: fixed; inset: 0; z-index: 999;
    background: rgba(0,0,0,0.75);
    backdrop-filter: blur(4px);
    align-items: center; justify-content: center;
    padding: 1rem;
}
.load-overlay.active { display: flex; }
.load-box {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 18px;
    width: 100%; max-width: 560px; max-height: 80vh;
    display: flex; flex-direction: column; overflow: hidden;
    box-shadow: 0 24px 64px rgba(0,0,0,0.7);
    animation: popIn 0.2s ease;
}
.load-head {
    padding: 1.2rem 1.5rem 0.9rem;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    display: flex; align-items: center; gap: 0.75rem;
}
.load-head h3 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.15rem; letter-spacing: 0.1em;
    color: var(--sun); margin: 0; flex: 1;
}
.load-head-close {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 7px;
    color: rgba(255,255,255,0.5);
    padding: 0.25rem 0.6rem;
    cursor: pointer; font-size: 0.85rem;
}
.load-head-close:hover { background: rgba(255,255,255,0.12); color: #fff; }
.load-body { overflow-y: auto; flex: 1; padding: 0.75rem; }
.load-record {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.75rem 0.9rem;
    border-radius: 10px;
    cursor: pointer;
    transition: background 0.12s;
    border: 1px solid transparent;
    margin-bottom: 0.35rem;
}
.load-record:hover { background: rgba(245,158,11,0.08); border-color: rgba(245,158,11,0.15); }
.load-record-icon {
    width: 34px; height: 34px;
    border-radius: 8px;
    background: rgba(59,130,246,0.12);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.load-record-info { flex: 1; min-width: 0; }
.load-record-ref  { font-weight: 700; font-size: 0.86rem; color: #fff; }
.load-record-meta { font-size: 0.72rem; color: rgba(255,255,255,0.35); margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.load-record-del {
    background: rgba(239,68,68,0.1);
    border: 1px solid rgba(239,68,68,0.2);
    border-radius: 6px;
    color: #f87171;
    padding: 0.2rem 0.5rem;
    cursor: pointer; font-size: 0.72rem;
    flex-shrink: 0;
    transition: background 0.15s;
}
.load-record-del:hover { background: rgba(239,68,68,0.25); }
.load-empty { text-align: center; color: rgba(255,255,255,0.3); padding: 2.5rem 1rem; font-size: 0.85rem; }
.load-spinner { text-align: center; color: rgba(255,255,255,0.4); padding: 2rem; font-size: 0.85rem; }

/* ── TOAST ────────────────────────────────────────────── */
.save-toast {
    position: fixed; bottom: 1.5rem; right: 1.5rem;
    background: #1e293b; border: 1px solid rgba(255,255,255,0.12);
    border-radius: 10px; padding: 0.75rem 1.2rem;
    font-size: 0.84rem; color: #fff;
    display: flex; align-items: center; gap: 0.6rem;
    box-shadow: 0 8px 24px rgba(0,0,0,0.5);
    z-index: 1000;
    transform: translateY(6px); opacity: 0;
    transition: all 0.25s ease; pointer-events: none;
}
.save-toast.show    { opacity: 1; transform: translateY(0); }
.save-toast.success { border-color: rgba(34,197,94,0.4); }
.save-toast.error   { border-color: rgba(239,68,68,0.4); }
.save-toast.info    { border-color: rgba(59,130,246,0.4); }

/* ── PAGE HEADING ─────────────────────────────────────── */
.pg-heading {
    display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;
}
.pg-heading h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.75rem; letter-spacing: 0.1em; color: #fff; margin: 0;
}
.pg-heading h2 em { color: var(--sun); font-style: normal; }
.qnum-badge {
    margin-left: auto;
    background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.25);
    border-radius: 20px; padding: 0.3rem 0.9rem;
    font-size: 0.78rem; color: var(--sun); font-weight: 700; letter-spacing: 0.05em;
}

.preview-label {
    font-size: 0.7rem; font-weight: 700; letter-spacing: 0.15em;
    color: rgba(255,255,255,0.3); text-transform: uppercase;
    margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;
}
.preview-label::after { content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.07); }
</style>
</head>
<body>

<div class="bg-orb orb-1"></div>
<div class="bg-orb orb-2"></div>
<div class="bg-orb orb-3"></div>

<!-- HEADER -->
<header class="header">
    <div class="header-inner">
        <div class="brand">
            <div class="brand-icon">
                <img src="/assets/images/suntastic_logo_png.png" alt="Logo" style="width:80px;height:80px;border-radius:6px;opacity:0.5;padding:4px">
            </div>
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
        <h2>Item <em>Cost</em></h2>
        <div class="qnum-badge" id="qNumBadge">IC-2605-0001</div>
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

            <!-- Project Info -->
            <div class="fp-card">
                <div class="fp-title">📋 Project Info</div>
                <div class="fp-grid">
                    <div class="fp-group">
                        <label class="fp-label">Prepared By</label>
                        <input class="fp-input" id="preparedBy" placeholder="Juan dela Cruz" oninput="renderPreview()">
                    </div>
                    <div class="fp-group">
                        <label class="fp-label">Project Name</label>
                        <input class="fp-input" id="projectName" placeholder="Solar Installation" oninput="renderPreview()">
                    </div>
                    <div class="fp-group span2">
                        <label class="fp-label">Project Location</label>
                        <input class="fp-input" id="projectLoc" placeholder="Street, Barangay, City" oninput="renderPreview()">
                    </div>
                    <div class="fp-group">
                        <label class="fp-label">Date</label>
                        <input class="fp-input" type="date" id="icDate" oninput="renderPreview()">
                    </div>
                    <div class="fp-group">
                        <label class="fp-label">Ref. No.</label>
                        <input class="fp-input" id="refNo" placeholder="Auto-generated" oninput="renderPreview()">
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="fp-card">
                <div class="fp-title">🔆 Items (na may Larawan)</div>
                <div id="itemsList"></div>
                <button class="btn-add-item" onclick="addItem()">＋ Magdagdag ng Item</button>
            </div>

            <!-- Charges -->
            <div class="fp-card">
                <div class="fp-title">💰 Charges</div>
                <div class="toggle-row">
                    <span>Kasama ang Delivery Fee?</span>
                    <label class="sw"><input type="checkbox" id="delivToggle" onchange="toggleDeliv()"><span class="sw-track"></span></label>
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

            <!-- Remarks -->
            <div class="fp-card">
                <div class="fp-title">📝 Remarks / Note</div>
                <div class="fp-group">
                    <textarea class="fp-input" id="icNote" rows="3" oninput="renderPreview()">Prices are subject to change without prior notice. All items are subject to availability.</textarea>
                </div>
            </div>

            <!-- Save / Export -->
            <div class="fp-card">
                <div class="fp-title">💾 I-save at I-export</div>

                <!-- Current saved record indicator -->
                <div class="saved-id-bar" id="savedIdBar">
                    <span>💾</span>
                    <span id="savedIdText">Naka-save</span>
                    <button onclick="detachRecord()" title="I-detach para gumawa ng bago">✕ Detach</button>
                </div>

                <!-- Save to Supabase -->
                <button class="btn-xl btn-xl-save" onclick="saveRecord()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    I-save sa Supabase
                </button>

                <!-- Load saved records -->
                <button class="btn-xl-load" onclick="openLoadModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 15v4c0 1.1.9 2 2 2h14a2 2 0 0 0 2-2v-4M17 9l-5 5-5-5M12 12.8V2.5"/></svg>
                    I-load ang Nakaraang Records
                </button>

                <!-- Export PDF -->
                <button class="btn-xl btn-xl-pdf" onclick="exportPDF()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    I-export bilang PDF
                </button>

                <!-- Export Excel -->
                <button class="btn-xl btn-xl-excel" onclick="exportExcel()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
                    I-export bilang Excel
                </button>

                <button class="btn-xl-load" onclick="clearAll()" style="border-style:dashed; margin-top:0.25rem;">
                    ✦ I-clear / Bagong Sheet
                </button>
            </div>

        </div><!-- /.form-panel -->

        <!-- ══ RIGHT: LIVE PREVIEW ═══════════════════════════════ -->
        <div>
            <div class="preview-label">Live Preview <span style="color:var(--sun)">●</span></div>
            <div class="preview-panel">
                <div class="pv" id="previewContent"></div>
            </div>
        </div>

    </div><!-- /.qb-layout -->

</main>

<!-- ══ ITEM PICKER MODAL ═══════════════════════════════════════ -->
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

<!-- ══ LOAD RECORDS MODAL ══════════════════════════════════════ -->
<div id="loadOverlay" class="load-overlay">
    <div class="load-box">
        <div class="load-head">
            <h3>📂 I-load ang Nakaraang Record</h3>
            <button class="load-head-close" onclick="closeLoadModal()">✕ Isara</button>
        </div>
        <div class="load-body" id="loadBody">
            <div class="load-spinner">Naglo-load...</div>
        </div>
    </div>
</div>

<!-- ══ TOAST ════════════════════════════════════════════════════ -->
<div class="save-toast" id="saveToast"></div>

<script>
// ── DATA ──────────────────────────────────────────────────────
const ITEMS_DB = <?= $itemsJson ?>;

// ── STATE ─────────────────────────────────────────────────────
let rowId        = 0;
let pickerTarget = null;
let currentRecordId = null;   // UUID of the currently loaded/saved Supabase record
const rowImages  = {};        // rowId → base64 string or null

// ── INIT ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const today = new Date();
    document.getElementById('icDate').value = today.toISOString().slice(0, 10);
    generateRefNum();
    addItem(); addItem(); addItem();
    renderPreview();
});

function generateRefNum() {
    const today = new Date();
    const yr  = String(today.getFullYear()).slice(-2);
    const mo  = String(today.getMonth() + 1).padStart(2, '0');
    const rnd = String(Math.floor(Math.random() * 9000) + 1000);
    const num = `IC-${yr}${mo}-${rnd}`;
    document.getElementById('qNumBadge').textContent = num;
    document.getElementById('refNo').value = num;
}

// ── ITEM ROWS ─────────────────────────────────────────────────
function addItem(name = '', desc = '', qty = 1, price = '', unit = 'pc/s') {
    rowId++;
    const id = rowId;
    rowImages[id] = null;

    const el = document.createElement('div');
    el.className  = 'item-row-ui';
    el.dataset.id = id;
    el.innerHTML  = `
        <span class="item-row-num">#<span class="rnum">${id}</span></span>
        <button class="btn-del-row" onclick="delItem(${id})">✕</button>
        <div class="item-row-grid">
            <div class="fp-group span2">
                <label class="fp-label">Item / Produkto</label>
                <div style="display:flex;gap:5px;align-items:center;">
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
                    ${['pc/s','sets','kW','kWh','unit','lot','roll','meter','pair'].map(u => `<option ${u === unit ? 'selected' : ''}>${u}</option>`).join('')}
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
            <div class="fp-group span2">
                <label class="fp-label">📷 Larawan ng Item</label>
                <div class="img-upload-zone" id="imgZone-${id}">
                    <input type="file" accept="image/*" onchange="handleImageUpload(${id}, this)">
                    <div class="img-upload-placeholder" id="imgPlaceholder-${id}">
                        <span class="ico">🖼</span>
                        I-click para mag-upload ng larawan
                    </div>
                    <img class="img-preview-thumb" id="imgThumb-${id}" src="" alt="preview">
                    <button class="img-remove-btn" id="imgRemove-${id}" onclick="removeImage(event, ${id})">✕ Alisin</button>
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
    delete rowImages[id];
    renumberRows();
    renderPreview();
}

function renumberRows() {
    document.querySelectorAll('.item-row-ui').forEach((el, i) => {
        el.querySelector('.rnum').textContent = i + 1;
    });
}

function calcRow(id) {
    const el = document.querySelector(`.item-row-ui[data-id="${id}"]`);
    if (!el) return;
    const qty   = parseFloat(el.querySelector('[data-field="qty"]').value)   || 0;
    const price = parseFloat(el.querySelector('[data-field="price"]').value) || 0;
    const amtEl = document.getElementById(`amt-${id}`);
    if (amtEl) amtEl.innerHTML = `<span>Total</span> ₱${fmt(qty * price)}`;
}

function getRows() {
    const rows = [];
    document.querySelectorAll('.item-row-ui').forEach((el, i) => {
        const id = parseInt(el.dataset.id);
        rows.push({
            id,
            num:   i + 1,
            name:  el.querySelector('[data-field="name"]').value  || '',
            desc:  el.querySelector('[data-field="desc"]').value  || '',
            qty:   parseFloat(el.querySelector('[data-field="qty"]').value)   || 0,
            unit:  el.querySelector('[data-field="unit"]').value  || 'pc/s',
            price: parseFloat(el.querySelector('[data-field="price"]').value) || 0,
            amt:   (parseFloat(el.querySelector('[data-field="qty"]').value)   || 0) *
                   (parseFloat(el.querySelector('[data-field="price"]').value) || 0),
            image: rowImages[id] || null,
        });
    });
    return rows;
}

// ── IMAGE HANDLING ────────────────────────────────────────────
function handleImageUpload(id, input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        rowImages[id] = e.target.result;
        const thumb     = document.getElementById(`imgThumb-${id}`);
        const ph        = document.getElementById(`imgPlaceholder-${id}`);
        const removeBtn = document.getElementById(`imgRemove-${id}`);
        thumb.src              = e.target.result;
        thumb.style.display    = 'block';
        ph.style.display       = 'none';
        removeBtn.style.display = 'block';
        renderPreview();
    };
    reader.readAsDataURL(file);
}

function removeImage(event, id) {
    event.preventDefault();
    event.stopPropagation();
    rowImages[id] = null;
    const thumb     = document.getElementById(`imgThumb-${id}`);
    const ph        = document.getElementById(`imgPlaceholder-${id}`);
    const removeBtn = document.getElementById(`imgRemove-${id}`);
    const zone      = document.getElementById(`imgZone-${id}`);
    thumb.style.display      = 'none';
    ph.style.display         = 'flex';
    removeBtn.style.display  = 'none';
    const fileInput = zone.querySelector('input[type="file"]');
    if (fileInput) fileInput.value = '';
    renderPreview();
}

// ── TOTALS ────────────────────────────────────────────────────
function calcTotals(rows) {
    const subtotal = rows.reduce((s, r) => s + r.amt, 0);
    const vatOn    = document.getElementById('vatToggle').checked;
    const delivOn  = document.getElementById('delivToggle').checked;
    const discOn   = document.getElementById('discToggle').checked;
    const delivFee = delivOn ? (parseFloat(document.getElementById('delivFee').value) || 0) : 0;
    let discount = 0;
    if (discOn) {
        const dv = parseFloat(document.getElementById('discAmt').value) || 0;
        discount = document.getElementById('discType').value === 'pct' ? subtotal * (dv / 100) : dv;
    }
    const afterDisc = subtotal - discount;
    const vat       = vatOn ? afterDisc * 0.12 : 0;
    const total     = afterDisc + vat + delivFee;
    return { subtotal, discount, delivFee, vat, total, vatOn, delivOn, discOn };
}

// ── SUPABASE SAVE ─────────────────────────────────────────────
async function saveRecord() {
    const btn  = document.querySelector('.btn-xl-save');
    const orig = btn.innerHTML;
    btn.innerHTML = '⏳ Sine-save...';
    btn.disabled  = true;

    const rows = getRows();
    const tots = calcTotals(rows);

    const payload = {
        ...(currentRecordId ? { id: currentRecordId } : {}),
        ref_number:        v('refNo') || document.getElementById('qNumBadge').textContent,
        co_name:           v('coName'),
        co_addr:           v('coAddr'),
        co_email:          v('coEmail'),
        co_tel:            v('coTel'),
        prepared_by:       v('preparedBy'),
        project_name:      v('projectName'),
        project_location:  v('projectLoc'),
        ic_date:           v('icDate') || null,
        items:             rows,          // images stripped server-side
        delivery_fee:      tots.delivFee,
        discount_amount:   tots.discount,
        discount_type:     document.getElementById('discType').value,
        vat_enabled:       document.getElementById('vatToggle').checked,
        delivery_enabled:  document.getElementById('delivToggle').checked,
        discount_enabled:  document.getElementById('discToggle').checked,
        note:              v('icNote'),
    };

    try {
        const res  = await fetch('/item_cost_api.php?action=save', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const json = await res.json();

        if (json.ok) {
            currentRecordId = json.data?.id || currentRecordId;
            updateSavedBar();
            showToast('✅ Nai-save sa Supabase!', 'success');
        } else {
            showToast('❌ Error sa pag-save: ' + (json.message || 'Unknown'), 'error');
        }
    } catch (err) {
        showToast('❌ Network error. Subukan ulit.', 'error');
        console.error(err);
    } finally {
        btn.innerHTML = orig;
        btn.disabled  = false;
    }
}

function updateSavedBar() {
    const bar  = document.getElementById('savedIdBar');
    const text = document.getElementById('savedIdText');
    if (currentRecordId) {
        bar.classList.add('visible');
        text.textContent = `ID: ${currentRecordId.slice(0, 8)}...`;
    } else {
        bar.classList.remove('visible');
    }
}

function detachRecord() {
    currentRecordId = null;
    updateSavedBar();
    generateRefNum();
    showToast('ℹ Detached — susunod na save ay gagawa ng bagong record.', 'info');
}

// ── SUPABASE LOAD ─────────────────────────────────────────────
async function openLoadModal() {
    document.getElementById('loadOverlay').classList.add('active');
    document.getElementById('loadBody').innerHTML = '<div class="load-spinner">⏳ Naglo-load ng mga record...</div>';

    try {
        const res  = await fetch('/item_cost_api.php?action=list');
        const json = await res.json();

        if (!json.ok || !json.data || !json.data.length) {
            document.getElementById('loadBody').innerHTML = '<div class="load-empty">📭 Wala pang naka-save na records.</div>';
            return;
        }

        let html = '';
        json.data.forEach(rec => {
            const date  = rec.ic_date ? fmtDate(rec.ic_date) : '—';
            const proj  = rec.project_name || '—';
            const by    = rec.prepared_by  || '—';
            html += `
            <div class="load-record" onclick="loadRecord('${escJs(rec.id)}')">
                <div class="load-record-icon">📋</div>
                <div class="load-record-info">
                    <div class="load-record-ref">${he(rec.ref_number || rec.id)}</div>
                    <div class="load-record-meta">${he(proj)} · ${he(by)} · ${date}</div>
                </div>
                <button class="load-record-del" onclick="deleteRecord(event, '${escJs(rec.id)}')">🗑</button>
            </div>`;
        });
        document.getElementById('loadBody').innerHTML = html;

    } catch (err) {
        document.getElementById('loadBody').innerHTML = '<div class="load-empty">❌ Hindi ma-load ang records.</div>';
        console.error(err);
    }
}

function closeLoadModal() {
    document.getElementById('loadOverlay').classList.remove('active');
}

async function loadRecord(id) {
    showToast('⏳ Naglo-load...', 'info');
    closeLoadModal();

    try {
        const res  = await fetch(`/item_cost_api.php?action=load&id=${encodeURIComponent(id)}`);
        const json = await res.json();

        if (!json.ok || !json.data) {
            showToast('❌ Hindi mahanap ang record.', 'error');
            return;
        }

        const d = json.data;
        currentRecordId = d.id;

        // Populate header fields
        setVal('coName',      d.co_name);
        setVal('coAddr',      d.co_addr);
        setVal('coEmail',     d.co_email);
        setVal('coTel',       d.co_tel);
        setVal('preparedBy',  d.prepared_by);
        setVal('projectName', d.project_name);
        setVal('projectLoc',  d.project_location);
        setVal('icDate',      d.ic_date || '');
        setVal('refNo',       d.ref_number);
        setVal('icNote',      d.note);
        document.getElementById('qNumBadge').textContent = d.ref_number || '';

        // Charges
        document.getElementById('vatToggle').checked  = !!d.vat_enabled;
        document.getElementById('delivToggle').checked = !!d.delivery_enabled;
        document.getElementById('discToggle').checked  = !!d.discount_enabled;
        toggleDeliv(); toggleDisc();

        if (d.delivery_fee)    setVal('delivFee', d.delivery_fee);
        if (d.discount_amount) setVal('discAmt',  d.discount_amount);
        if (d.discount_type)   document.getElementById('discType').value = d.discount_type;

        // Rebuild items
        document.getElementById('itemsList').innerHTML = '';
        rowId = 0;
        Object.keys(rowImages).forEach(k => delete rowImages[k]);

        const items = Array.isArray(d.items) ? d.items : [];
        if (items.length) {
            items.forEach(it => addItem(it.name, it.desc, it.qty, it.price, it.unit));
        } else {
            addItem(); addItem(); addItem();
        }

        updateSavedBar();
        renderPreview();
        showToast('✅ Na-load ang record!', 'success');

    } catch (err) {
        showToast('❌ Network error sa pag-load.', 'error');
        console.error(err);
    }
}

async function deleteRecord(event, id) {
    event.stopPropagation();
    if (!confirm('Sigurado kang burahin ang record na ito?')) return;

    try {
        const res  = await fetch('/item_cost_api.php?action=delete', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ id }),
        });
        const json = await res.json();

        if (json.ok) {
            if (currentRecordId === id) { currentRecordId = null; updateSavedBar(); }
            showToast('🗑 Na-delete ang record.', 'success');
            openLoadModal(); // refresh list
        } else {
            showToast('❌ Hindi na-delete.', 'error');
        }
    } catch (err) {
        showToast('❌ Network error.', 'error');
        console.error(err);
    }
}

// ── LIVE PREVIEW ──────────────────────────────────────────────
function renderPreview() {
    const coName      = v('coName');
    const coAddr      = v('coAddr');
    const coEmail     = v('coEmail');
    const coTel       = v('coTel');
    const preparedBy  = v('preparedBy');
    const projectName = v('projectName');
    const projectLoc  = v('projectLoc');
    const icDate      = fmtDate(v('icDate'));
    const refNo       = v('refNo') || document.getElementById('qNumBadge').textContent;
    const note        = v('icNote');

    const rows = getRows();
    const tots = calcTotals(rows);

    let itemsHtml = rows.map((r, i) => {
        const imgHtml = r.image
            ? `<img src="${r.image}" class="pv-item-img" alt="${he(r.name)}">`
            : `<div class="pv-item-no-img">📦</div>`;
        return `
        <tr style="${i % 2 === 0 ? '' : 'background:#f9f9f9'}">
            <td>${r.num}</td>
            <td>${imgHtml}</td>
            <td style="text-align:left">
                <strong>${he(r.name)}</strong>
                ${r.desc ? `<br><span style="font-size:9px;color:#888">${he(r.desc)}</span>` : ''}
            </td>
            <td>${r.qty}</td>
            <td>${he(r.unit)}</td>
            <td style="text-align:right">₱ ${fmt(r.price)}</td>
            <td style="text-align:right">₱ ${fmt(r.amt)}</td>
        </tr>`;
    }).join('');

    if (!itemsHtml) {
        itemsHtml = `<tr><td colspan="7" style="color:#bbb;text-align:center;padding:12px;font-size:10px;">Walang items pa</td></tr>`;
    }

    let totHtml = `
        <div class="pv-total-row">
            <span class="tlbl">Subtotal:</span>
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
    totHtml += `
        <div class="pv-total-row">
            <span class="tlbl">Delivery Fee:</span>
            <span class="tcur">₱</span>
            <span class="tval">${tots.delivOn && tots.delivFee > 0 ? fmt(tots.delivFee) : '-'}</span>
        </div>
        <div class="pv-total-row">
            <span class="tlbl">VAT 12%:</span>
            <span class="tcur">₱</span>
            <span class="tval">${tots.vatOn ? fmt(tots.vat) : '-'}</span>
        </div>
        <div class="pv-total-row grand">
            <span class="tlbl">TOTAL AMOUNT:</span>
            <span class="tcur">₱</span>
            <span class="tval">${fmt(tots.total)}</span>
        </div>`;

    document.getElementById('previewContent').innerHTML = `
        <div class="pv-ref-line">
            <span>Suntastic Solar — Item Cost Sheet</span>
            <span>${he(refNo)}</span>
        </div>
        <div class="pv-company-header">
            <div class="pv-company-logo">
                <img src="/assets/images/suntastic_logo_png.png" alt="Logo"
                    style="width:50px;height:50px;border-radius:6px;background:#fff;padding:4px">
            </div>
            <div class="pv-company-name">${he(coName)}</div>
            <div class="pv-company-tagline">BRIGHTEN UP YOUR LIFE</div>
            <div class="pv-company-addr">${he(coAddr)}<br>Email: ${he(coEmail)}&nbsp;&nbsp;&nbsp;Tel: ${he(coTel)}</div>
        </div>
        <div class="pv-title-band">ITEM COST SHEET</div>
        <div class="pv-info-box">
            <div>
                <div class="pv-info-row"><span class="pv-info-label">Date:</span><span class="pv-info-value">${he(icDate)}</span></div>
                <div class="pv-info-row"><span class="pv-info-label">Ref. No.:</span><span class="pv-info-value">${he(refNo)}</span></div>
                <div class="pv-info-row"><span class="pv-info-label">Prepared By:</span><span class="pv-info-value">${he(preparedBy)}</span></div>
            </div>
            <div>
                <div class="pv-info-row"><span class="pv-info-label">Project Name:</span><span class="pv-info-value">${he(projectName)}</span></div>
                <div class="pv-info-row"><span class="pv-info-label">Location:</span><span class="pv-info-value">${he(projectLoc)}</span></div>
            </div>
        </div>
        <table class="pv-table">
            <thead>
                <tr>
                    <th style="width:22px">#</th>
                    <th style="width:80px">Larawan</th>
                    <th style="text-align:left">Pangalan / Specs</th>
                    <th style="width:32px">Qty</th>
                    <th style="width:38px">Unit</th>
                    <th style="width:72px">Unit Price</th>
                    <th style="width:80px">Total</th>
                </tr>
            </thead>
            <tbody>${itemsHtml}</tbody>
        </table>
        <div class="pv-totals" style="margin-top:8px">${totHtml}</div>
        ${note ? `<div class="pv-note" style="margin-top:8px">${he(note)}</div>` : ''}
        <div style="margin-top:20px;display:grid;grid-template-columns:1fr 1fr;gap:20px;font-size:9.5px;">
            <div style="border-top:1px solid #aaa;padding-top:6px;text-align:center;">
                <div style="color:#555;margin-bottom:24px;">Prepared By</div>
                <div style="font-weight:700">${he(preparedBy) || '____________________________'}</div>
            </div>
            <div style="border-top:1px solid #aaa;padding-top:6px;text-align:center;">
                <div style="color:#555;margin-bottom:24px;">Checked / Approved By</div>
                <div style="font-weight:700">____________________________</div>
            </div>
        </div>
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
                    <div class="picker-item-meta">Stock: ${it.quantity} &nbsp;·&nbsp; Cost: ₱${Number(it.price || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</div>
                </div>
            </div>`;
        });
    });
    document.getElementById('pickerBody').innerHTML = html;
}

document.getElementById('pickerOverlay').addEventListener('click', e => {
    if (e.target === document.getElementById('pickerOverlay')) closePicker();
});
document.getElementById('loadOverlay').addEventListener('click', e => {
    if (e.target === document.getElementById('loadOverlay')) closeLoadModal();
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closePicker(); closeLoadModal(); }
});

// ── CHARGES TOGGLES ───────────────────────────────────────────
function toggleDeliv() {
    document.getElementById('delivRow').style.display = document.getElementById('delivToggle').checked ? '' : 'none';
    renderPreview();
}
function toggleDisc() {
    document.getElementById('discRow').style.display = document.getElementById('discToggle').checked ? '' : 'none';
    renderPreview();
}

// ── CLEAR ALL ─────────────────────────────────────────────────
function clearAll() {
    if (!confirm('I-clear ang lahat ng data at magsimula muli?')) return;
    document.getElementById('itemsList').innerHTML = '';
    rowId = 0;
    currentRecordId = null;
    Object.keys(rowImages).forEach(k => delete rowImages[k]);
    ['preparedBy','projectName','projectLoc'].forEach(id => document.getElementById(id).value = '');
    generateRefNum();
    const today = new Date();
    document.getElementById('icDate').value = today.toISOString().slice(0, 10);
    addItem(); addItem(); addItem();
    updateSavedBar();
    renderPreview();
    showToast('✦ Nai-clear ang lahat', 'success');
}

// ── PDF EXPORT ────────────────────────────────────────────────
async function exportPDF() {
    const { jsPDF } = window.jspdf;
    const refNo = v('refNo') || document.getElementById('qNumBadge').textContent;
    const previewEl = document.getElementById('previewContent');

    const btn  = document.querySelector('.btn-xl-pdf');
    const orig = btn.innerHTML;
    btn.innerHTML = '⏳ Generating PDF...';
    btn.disabled  = true;

    const previewPanel      = document.querySelector('.preview-panel');
    const savedOverflow     = previewPanel.style.overflow;
    const savedBorderRadius = previewPanel.style.borderRadius;
    previewPanel.style.overflow     = 'visible';
    previewPanel.style.borderRadius = '0';

    try {
        const canvas = await html2canvas(previewEl, {
            scale: 2, useCORS: true, allowTaint: true,
            backgroundColor: '#ffffff', logging: false,
            width: previewEl.scrollWidth, height: previewEl.scrollHeight,
            windowWidth: previewEl.scrollWidth, windowHeight: previewEl.scrollHeight,
            scrollX: 0, scrollY: -window.scrollY,
        });

        const imgData = canvas.toDataURL('image/jpeg', 0.95);
        const doc     = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        const pageW   = doc.internal.pageSize.getWidth();
        const pageH   = doc.internal.pageSize.getHeight();
        const margin  = 8;
        const usableW = pageW - margin * 2;
        const usableH = pageH - margin * 2;
        const imgW    = canvas.width;
        const imgH    = canvas.height;
        const pxPerMm = imgW / usableW;
        const printedH = imgH / pxPerMm;

        if (printedH <= usableH) {
            doc.addImage(imgData, 'JPEG', margin, margin, usableW, printedH);
        } else {
            const sliceHeightPx = Math.floor(usableH * pxPerMm);
            let offsetPx = 0, pageNum = 0;
            while (offsetPx < imgH) {
                if (pageNum > 0) doc.addPage();
                const thisSlicePx = Math.min(sliceHeightPx, imgH - offsetPx);
                const sc = document.createElement('canvas');
                sc.width = imgW; sc.height = thisSlicePx;
                sc.getContext('2d').drawImage(canvas, 0, -offsetPx);
                doc.addImage(sc.toDataURL('image/jpeg', 0.95), 'JPEG', margin, margin, usableW, thisSlicePx / pxPerMm);
                offsetPx += thisSlicePx; pageNum++;
            }
        }
        doc.save(`${refNo}_ItemCost.pdf`);
        showToast('✅ Na-export ang PDF!', 'success');
    } catch (err) {
        console.error('PDF export error:', err);
        showToast('❌ May error sa PDF export.', 'error');
    } finally {
        previewPanel.style.overflow     = savedOverflow;
        previewPanel.style.borderRadius = savedBorderRadius;
        btn.innerHTML = orig;
        btn.disabled  = false;
    }
}

// ── EXCEL EXPORT ──────────────────────────────────────────────
function exportExcel() {
    const refNo       = v('refNo') || document.getElementById('qNumBadge').textContent;
    const rows        = getRows();
    const tots        = calcTotals(rows);

    const wb = XLSX.utils.book_new();
    const data = [
        [v('coName')],
        [v('coAddr')],
        [`Email: ${v('coEmail')}   Tel: ${v('coTel')}`],
        [],
        ['ITEM COST SHEET'],
        [`Ref. No: ${refNo}`],
        [],
        ['Date:', fmtDate(v('icDate')), '', 'Project Name:', v('projectName')],
        ['Prepared By:', v('preparedBy'), '', 'Location:', v('projectLoc')],
        [],
        ['#', 'Item Name', 'Description / Specs', 'Qty', 'Unit', 'Unit Price (₱)', 'Total (₱)'],
        ...rows.map(r => [r.num, r.name, r.desc, r.qty, r.unit, r.price, r.amt]),
        [],
        ['', '', '', '', 'Subtotal:', '₱', tots.subtotal],
        ...(tots.discOn && tots.discount > 0 ? [['', '', '', '', 'Discount:', '₱', -tots.discount]] : []),
        ['', '', '', '', 'Delivery Fee:', '₱', tots.delivOn ? tots.delivFee : '-'],
        ['', '', '', '', 'VAT (12%):', '₱', tots.vatOn ? tots.vat : '-'],
        ['', '', '', '', 'TOTAL AMOUNT:', '₱', tots.total],
        [],
        ...(v('icNote') ? [['Remarks:', v('icNote')], []] : []),
        ['Prepared By:', v('preparedBy'), '', 'Approved By:', ''],
    ];
    const ws = XLSX.utils.aoa_to_sheet(data);
    ws['!cols'] = [{ wch: 4 }, { wch: 30 }, { wch: 28 }, { wch: 6 }, { wch: 10 }, { wch: 20 }, { wch: 20 }];
    XLSX.utils.book_append_sheet(wb, ws, 'Item Cost');
    XLSX.writeFile(wb, `${refNo}_ItemCost.xlsx`);
    showToast('✅ Na-export ang Excel!', 'success');
}

// ── TOAST ─────────────────────────────────────────────────────
let toastTimer = null;
function showToast(msg, type = 'success') {
    const el = document.getElementById('saveToast');
    el.textContent = msg;
    el.className   = `save-toast ${type} show`;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('show'), 3200);
}

// ── HELPERS ───────────────────────────────────────────────────
function v(id)      { return (document.getElementById(id) || {}).value || ''; }
function setVal(id, val) { const el = document.getElementById(id); if (el) el.value = val || ''; }
function he(s)      { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function escHtml(s) { return he(s).replace(/"/g,'&quot;'); }
function escJs(s)   { return String(s || '').replace(/\\/g,'\\\\').replace(/'/g,"\\'"); }
function fmt(n)     { return Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
function fmtDate(d) {
    if (!d) return '';
    const parts = d.split('-');
    if (parts.length !== 3) return d;
    const mo = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return `${mo[parseInt(parts[1]) - 1]} ${parseInt(parts[2])}, ${parts[0]}`;
}
function itemIcon(n) {
    n = (n || '').toLowerCase();
    if (n.includes('panel'))                           return '🔆';
    if (n.includes('inverter'))                        return '⚡';
    if (n.includes('battery') || n.includes('bater')) return '🔋';
    if (n.includes('cable')   || n.includes('wire'))  return '🔌';
    if (n.includes('mount')   || n.includes('rack'))  return '🔩';
    return '📦';
}
</script>

</body>
</html>