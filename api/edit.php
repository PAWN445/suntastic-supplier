<?php
require_once 'config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php?error=Invalid+ID');
    exit;
}

$result = $db->getById($id);
if (empty($result['data'])) {
    header('Location: index.php?error=Item+hindi+nahanap');
    exit;
}

$item = $result['data'][0];
$errors = [];

// ── SUPABASE STORAGE UPLOAD ───────────────────────────────────
// Returns the public URL string, or throws on failure.
function uploadImageToSupabase(array $file): string {
    $supabaseUrl = SUPABASE_URL;
    $anonKey     = SUPABASE_ANON_KEY;
    $bucket      = 'supplier-images';

    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','gif'];
    if (!in_array($ext, $allowed)) {
        throw new RuntimeException('Hindi allowed ang file type. Gamitin ang JPG, PNG, WEBP, o GIF.');
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Masyadong malaki ang file. Maximum ay 5 MB.');
    }

    $filename  = time() . '_' . preg_replace('/[^a-z0-9._-]/i', '_', basename($file['name']));
    $uploadUrl = "{$supabaseUrl}/storage/v1/object/{$bucket}/{$filename}";
    $fileData  = file_get_contents($file['tmp_name']);
    $mimeTypes = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
    ];
    $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';

    $ch = curl_init($uploadUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => $fileData,
        CURLOPT_HTTPHEADER     => [
            'apikey: '               . $anonKey,
            'Authorization: Bearer ' . $anonKey,
            'Content-Type: '         . $mimeType,
            'x-upsert: true',
        ],
    ]);
    $response = curl_exec($ch);
    $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code < 200 || $code >= 300) {
        $detail = json_decode($response, true)['message'] ?? $response;
        throw new RuntimeException("Upload failed ({$code}): {$detail}");
    }

    return "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$filename}";
}

// ── FORM PROCESSING ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'item_name'      => trim($_POST['item_name']      ?? ''),
        'quantity'       => trim($_POST['quantity']        ?? ''),
        'price'          => trim($_POST['price']           ?? ''),
        'supplier_name'  => trim($_POST['supplier_name']  ?? ''),
        'contact_number' => trim($_POST['contact_number'] ?? ''),
    ];

    if (empty($formData['item_name']))                                   $errors[] = 'Kailangan ang pangalan ng item.';
    if (!is_numeric($formData['quantity']) || $formData['quantity'] < 0) $errors[] = 'Kailangan ang tamang quantity (numero).';
    if (!is_numeric($formData['price'])    || $formData['price']    < 0) $errors[] = 'Kailangan ang tamang price (numero).';
    if (empty($formData['supplier_name']))                               $errors[] = 'Kailangan ang pangalan ng supplier.';
    if (empty($formData['contact_number']))                              $errors[] = 'Kailangan ang contact number.';

    // Image upload (optional — only update if a new image was chosen)
    if (!empty($_FILES['item_image']['name'])) {
        if ($_FILES['item_image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'May error sa pag-upload ng larawan. Subukan ulit.';
        } else {
            try {
                $formData['image_url'] = uploadImageToSupabase($_FILES['item_image']);
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }
    }
    // If no new image uploaded, keep the existing image_url (don't overwrite)

    if (empty($errors)) {
        $formData['quantity'] = (int)$formData['quantity'];
        $formData['price']    = (float)$formData['price'];
        $formData['amount']   = $formData['quantity'] * $formData['price'];

        $result = $db->update($id, $formData);

        if ($result['code'] === 200) {
            header('Location: index.php?success=Matagumpay+na+na-update+ang+item!');
            exit;
        } else {
            $errors[] = 'May error na naganap. Subukan ulit.';
        }
    }

    $item = array_merge($item, $formData);
}
?>
<!DOCTYPE html>
<html lang="fil">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-edit ang Item — Suntastic Supplier</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Semi+Condensed:wght@600;700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ── IMAGE UPLOAD ZONE ───────────────────────────────── */
        .img-upload-zone {
            border: 2px dashed rgba(245,158,11,0.35);
            border-radius: 12px;
            padding: 1.2rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.22s ease;
            background: rgba(245,158,11,0.03);
            position: relative;
            overflow: hidden;
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 6px;
        }
        .img-upload-zone:hover,
        .img-upload-zone.drag-over {
            border-color: var(--sun, #f59e0b);
            background: rgba(245,158,11,0.07);
        }
        .img-upload-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        .upload-ico  { font-size: 2rem; line-height: 1; pointer-events: none; }
        .upload-lbl  {
            font-size: 0.82rem;
            color: rgba(255,255,255,0.45);
            pointer-events: none;
            line-height: 1.5;
        }
        .upload-lbl strong { color: var(--sun, #f59e0b); font-weight: 600; }
        .upload-hint {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.25);
            pointer-events: none;
        }

        /* ── CURRENT IMAGE ───────────────────────────────────── */
        .current-img-wrap {
            border-radius: 10px;
            overflow: hidden;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            margin-bottom: 0.6rem;
        }
        .current-img-wrap img {
            width: 100%;
            max-height: 200px;
            object-fit: contain;
            display: block;
            background: rgba(255,255,255,0.03);
        }
        .current-img-label {
            padding: 0.45rem 0.75rem;
            font-size: 0.72rem;
            color: rgba(255,255,255,0.35);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .current-img-badge {
            background: rgba(34,211,238,0.15);
            border: 1px solid rgba(34,211,238,0.25);
            color: #22d3ee;
            border-radius: 4px;
            font-size: 0.68rem;
            padding: 1px 7px;
        }

        /* ── PREVIEW AREA ────────────────────────────────────── */
        .img-preview-wrap {
            display: none;
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .img-preview-wrap.visible { display: block; }
        .img-preview-wrap img {
            width: 100%;
            max-height: 200px;
            object-fit: contain;
            display: block;
            border-radius: 10px;
            background: rgba(255,255,255,0.03);
        }
        .img-preview-meta {
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.4);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }
        .img-preview-meta .fname {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
        }
        .img-remove-btn {
            background: rgba(239,68,68,0.15);
            border: 1px solid rgba(239,68,68,0.25);
            border-radius: 6px;
            color: #f87171;
            font-size: 0.72rem;
            padding: 3px 9px;
            cursor: pointer;
            flex-shrink: 0;
            transition: background 0.15s;
            font-family: inherit;
        }
        .img-remove-btn:hover { background: rgba(239,68,68,0.3); }

        /* ── UPLOAD PROGRESS BAR ─────────────────────────────── */
        .upload-progress {
            display: none;
            height: 3px;
            border-radius: 3px;
            background: rgba(255,255,255,0.08);
            overflow: hidden;
            margin-top: 4px;
        }
        .upload-progress.visible { display: block; }
        .upload-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
            border-radius: 3px;
            width: 0%;
            transition: width 0.3s ease;
            animation: shimmer 1.5s infinite;
        }
        @keyframes shimmer {
            0%   { opacity: 1; }
            50%  { opacity: 0.7; }
            100% { opacity: 1; }
        }

        .size-ok   { color: #34d399; }
        .size-warn { color: #f87171; }
    </style>
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
            <div class="form-header-icon">✎</div>
            <h2 class="form-title">I-edit ang Item</h2>
            <p class="form-subtitle">Baguhin ang impormasyon ng item</p>
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

        <!-- multipart/form-data required para sa file upload -->
        <form method="POST" action="edit.php?id=<?= $id ?>" class="form-card" novalidate enctype="multipart/form-data">

            <div class="form-group">
                <label class="form-label" for="item_name">
                    <span class="label-dot" style="background:#f59e0b"></span>
                    Pangalan ng Item
                </label>
                <input type="text" id="item_name" name="item_name" class="form-input"
                    value="<?= htmlspecialchars($item['item_name']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="quantity">
                    <span class="label-dot" style="background:#10b981"></span>
                    Quantity
                </label>
                <input type="number" id="quantity" name="quantity" class="form-input"
                    value="<?= htmlspecialchars($item['quantity']) ?>"
                    min="0" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="price">
                    <span class="label-dot" style="background:#f59e0b"></span>
                    Price (₱)
                </label>
                <div class="input-prefix-wrap">
                    <span class="input-prefix">₱</span>
                    <input type="number" id="price" name="price" class="form-input with-prefix"
                        value="<?= htmlspecialchars($item['price'] ?? 0) ?>"
                        min="0" step="0.01" required>
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
                    <span class="amount-value" id="amountValue">
                        ₱<?= number_format(($item['amount'] ?? ($item['quantity'] * ($item['price'] ?? 0))), 2) ?>
                    </span>
                </div>
                <input type="hidden" id="amount" name="amount"
                    value="<?= htmlspecialchars($item['amount'] ?? ($item['quantity'] * ($item['price'] ?? 0))) ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="supplier_name">
                    <span class="label-dot" style="background:#6366f1"></span>
                    Pangalan ng Supplier
                </label>
                <input type="text" id="supplier_name" name="supplier_name" class="form-input"
                    value="<?= htmlspecialchars($item['supplier_name']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="contact_number">
                    <span class="label-dot" style="background:#ef4444"></span>
                    Contact Number
                </label>
                <input type="text" id="contact_number" name="contact_number" class="form-input"
                    value="<?= htmlspecialchars($item['contact_number']) ?>" required>
            </div>

            <!-- ── IMAGE UPLOAD ─────────────────────────────── -->
            <div class="form-group">
                <label class="form-label">
                    <span class="label-dot" style="background:#22d3ee"></span>
                    Larawan ng Item
                    <span style="font-size:0.7rem;color:rgba(255,255,255,0.3);font-weight:400;margin-left:6px;">(optional — mag-upload para palitan)</span>
                </label>

                <!-- Show existing image if available -->
                <?php if (!empty($item['image_url'])): ?>
                <div class="current-img-wrap" id="currentImgWrap">
                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="Kasalukuyang larawan">
                    <div class="current-img-label">
                        <span class="current-img-badge">Kasalukuyan</span>
                        <span>Mag-upload ng bago para palitan ang larawang ito</span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Drop zone -->
                <div class="img-upload-zone" id="uploadZone">
                    <input type="file" id="item_image" name="item_image"
                        accept="image/jpeg,image/png,image/webp,image/gif">
                    <div class="upload-ico">🖼️</div>
                    <div class="upload-lbl">
                        <strong>Mag-click para pumili</strong> o i-drag dito ang bagong larawan
                    </div>
                    <div class="upload-hint">JPG, PNG, WEBP, GIF · Max 5 MB</div>
                </div>

                <!-- Fake progress bar -->
                <div class="upload-progress" id="uploadProgress">
                    <div class="upload-progress-bar" id="uploadProgressBar"></div>
                </div>

                <!-- Preview ng bagong larawan -->
                <div class="img-preview-wrap" id="previewWrap">
                    <img id="previewImg" src="" alt="Preview ng larawan">
                    <div class="img-preview-meta">
                        <span class="fname" id="previewName">—</span>
                        <span id="previewSize">—</span>
                        <button type="button" class="img-remove-btn" id="removeImgBtn">✕ Alisin</button>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn btn-outline">Kanselahin</a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span>💾 I-save ang Pagbabago</span>
                </button>
            </div>
        </form>
    </div>
</main>

<script src="assets/js/main.js"></script>
</body>
</html>