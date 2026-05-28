<?php
require_once 'config.php';

$errors   = [];
$formData = [
    'item_name'      => '',
    'quantity'       => '',
    'price'          => '',
    'supplier_name'  => '',
    'contact_number' => '',
    'image_url'      => '',
];

// ── SUPABASE STORAGE UPLOAD ───────────────────────────────────
// Returns the public URL string, or throws on failure.
function uploadImageToSupabase(array $file): string {
    $supabaseUrl = SUPABASE_URL;    // e.g. https://xxxx.supabase.co
    $anonKey     = SUPABASE_ANON_KEY;
    $bucket      = 'supplier-images';

    // Build a unique filename: timestamp + original name (sanitised)
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed  = ['jpg','jpeg','png','webp','gif'];
    if (!in_array($ext, $allowed)) {
        throw new RuntimeException('Hindi allowed ang file type. Gamitin ang JPG, PNG, WEBP, o GIF.');
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Masyadong malaki ang file. Maximum ay 5 MB.');
    }

    $filename   = time() . '_' . preg_replace('/[^a-z0-9._-]/i', '_', basename($file['name']));
    $uploadUrl  = "{$supabaseUrl}/storage/v1/object/{$bucket}/{$filename}";
    $fileData   = file_get_contents($file['tmp_name']);
    $mimeTypes  = [
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
            'x-upsert: true',        // overwrite if same filename (safe with timestamp prefix)
        ],
    ]);
    $response = curl_exec($ch);
    $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code < 200 || $code >= 300) {
        $detail = json_decode($response, true)['message'] ?? $response;
        throw new RuntimeException("Upload failed ({$code}): {$detail}");
    }

    // Build the public URL
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
        'image_url'      => '',
    ];

    // Validation
    if (empty($formData['item_name']))                                   $errors[] = 'Kailangan ang pangalan ng item.';
    if (!is_numeric($formData['quantity']) || $formData['quantity'] < 0) $errors[] = 'Kailangan ang tamang quantity (numero).';
    if (!is_numeric($formData['price'])    || $formData['price']    < 0) $errors[] = 'Kailangan ang tamang price (numero).';
    if (empty($formData['supplier_name']))                               $errors[] = 'Kailangan ang pangalan ng supplier.';
    if (empty($formData['contact_number']))                              $errors[] = 'Kailangan ang contact number.';

    // Image upload (optional)
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

    if (empty($errors)) {
        $formData['quantity'] = (int)$formData['quantity'];
        $formData['price']    = (float)$formData['price'];
        $formData['amount']   = $formData['quantity'] * $formData['price'];

        // Remove empty image_url so we don't overwrite existing with blank
        if (empty($formData['image_url'])) unset($formData['image_url']);

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
        .upload-ico {
            font-size: 2rem;
            line-height: 1;
            pointer-events: none;
        }
        .upload-lbl {
            font-size: 0.82rem;
            color: rgba(255,255,255,0.45);
            pointer-events: none;
            line-height: 1.5;
        }
        .upload-lbl strong {
            color: var(--sun, #f59e0b);
            font-weight: 600;
        }
        .upload-hint {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.25);
            pointer-events: none;
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

        /* ── SIZE BADGE ──────────────────────────────────────── */
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

        <!-- multipart/form-data required para sa file upload -->
        <form method="POST" action="add.php" class="form-card" novalidate enctype="multipart/form-data">

            <!-- Item Name -->
            <div class="form-group">
                <label class="form-label" for="item_name">
                    <span class="label-dot" style="background:#f59e0b"></span>
                    Pangalan ng Item
                </label>
                <input type="text" id="item_name" name="item_name" class="form-input"
                    placeholder="hal. Laptop, T-Shirt, Bigas..."
                    value="<?= htmlspecialchars($formData['item_name']) ?>" required>
            </div>

            <!-- Quantity -->
            <div class="form-group">
                <label class="form-label" for="quantity">
                    <span class="label-dot" style="background:#10b981"></span>
                    Quantity
                </label>
                <input type="number" id="quantity" name="quantity" class="form-input"
                    placeholder="hal. 100"
                    value="<?= htmlspecialchars($formData['quantity']) ?>" min="0" required>
            </div>

            <!-- Price -->
            <div class="form-group">
                <label class="form-label" for="price">
                    <span class="label-dot" style="background:#f59e0b"></span>
                    Price (₱)
                </label>
                <div class="input-prefix-wrap">
                    <span class="input-prefix">₱</span>
                    <input type="number" id="price" name="price" class="form-input with-prefix"
                        placeholder="hal. 1500.00"
                        value="<?= htmlspecialchars($formData['price']) ?>" min="0" step="0.01" required>
                </div>
            </div>

            <!-- Amount (auto-computed) -->
            <div class="form-group">
                <label class="form-label">
                    <span class="label-dot" style="background:#a78bfa"></span>
                    Amount (Quantity × Price)
                </label>
                <div class="amount-display" id="amountDisplay">
                    <span class="amount-equals">=</span>
                    <span class="amount-value" id="amountValue">₱0.00</span>
                </div>
                <input type="hidden" id="amount" name="amount" value="0">
            </div>

            <!-- Supplier Name -->
            <div class="form-group">
                <label class="form-label" for="supplier_name">
                    <span class="label-dot" style="background:#6366f1"></span>
                    Pangalan ng Supplier
                </label>
                <input type="text" id="supplier_name" name="supplier_name" class="form-input"
                    placeholder="hal. ABC Trading, XYZ Corp..."
                    value="<?= htmlspecialchars($formData['supplier_name']) ?>" required>
            </div>

            <!-- Contact Number -->
            <div class="form-group">
                <label class="form-label" for="contact_number">
                    <span class="label-dot" style="background:#ef4444"></span>
                    Contact Number
                </label>
                <input type="text" id="contact_number" name="contact_number" class="form-input"
                    placeholder="hal. 09XX-XXX-XXXX"
                    value="<?= htmlspecialchars($formData['contact_number']) ?>" required>
            </div>

            <!-- Image Upload -->
            <div class="form-group">
                <label class="form-label">
                    <span class="label-dot" style="background:#22d3ee"></span>
                    Larawan ng Item
                    <span style="font-size:0.7rem;color:rgba(255,255,255,0.3);font-weight:400;margin-left:6px;">(optional)</span>
                </label>

                <!-- Drop zone (hidden when preview is shown) -->
                <div class="img-upload-zone" id="uploadZone">
                    <input type="file" id="item_image" name="item_image"
                        accept="image/jpeg,image/png,image/webp,image/gif">
                    <div class="upload-ico">🖼️</div>
                    <div class="upload-lbl">
                        <strong>Mag-click para pumili</strong> o i-drag dito ang larawan
                    </div>
                    <div class="upload-hint">JPG, PNG, WEBP, GIF · Max 5 MB</div>
                </div>

                <!-- Fake progress bar (shows while reading file) -->
                <div class="upload-progress" id="uploadProgress">
                    <div class="upload-progress-bar" id="uploadProgressBar"></div>
                </div>

                <!-- Preview -->
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
                    <span>+ Idagdag ang Item</span>
                </button>
            </div>
        </form>
    </div>
</main>

<script src="assets/js/main.js"></script>
</body>
</html>