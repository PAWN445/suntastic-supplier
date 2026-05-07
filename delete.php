<?php
require_once 'config.php';

$action       = $_GET['action'] ?? 'single';
$ref          = $_GET['ref']    ?? '';   // supplier name para sa redirect
$redirectBack = $ref
    ? 'supplier.php?name=' . urlencode($ref)
    : 'index.php';

// ── BULK DELETE (lahat ng items ng isang supplier) ──────────
if ($action === 'bulk') {
    $supplierName = $_GET['supplier'] ?? '';

    if (!$supplierName) {
        header('Location: index.php?error=Invalid+supplier');
        exit;
    }

    $result = $db->deleteBySupplier($supplierName);

    if ($result['code'] === 204 || $result['code'] === 200) {
        header('Location: index.php?success=' . urlencode('Matagumpay na na-delete ang lahat ng items ni ' . $supplierName . '!'));
    } else {
        header('Location: ' . $redirectBack . '?error=' . urlencode('May error sa bulk delete. Subukan ulit.'));
    }
    exit;
}

// ── SINGLE DELETE ───────────────────────────────────────────
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: ' . $redirectBack . '?error=Invalid+ID');
    exit;
}

$result = $db->delete($id);

if ($result['code'] === 204 || $result['code'] === 200) {
    header('Location: ' . $redirectBack . '?success=' . urlencode('Matagumpay na na-delete ang item!'));
} else {
    header('Location: ' . $redirectBack . '?error=' . urlencode('May error sa pag-delete. Subukan ulit.'));
}
exit;
?>
