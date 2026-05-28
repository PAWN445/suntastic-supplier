// ============================================================
//  SUNTASTIC SUPPLIER — JAVASCRIPT
// ============================================================

// Auto-compute Amount = Quantity × Price
function computeAmount() {
    const qtyEl   = document.getElementById('quantity');
    const priceEl = document.getElementById('price');
    if (!qtyEl || !priceEl) return;

    const qty    = parseFloat(qtyEl.value)   || 0;
    const price  = parseFloat(priceEl.value) || 0;
    const amount = qty * price;

    const amountValueEl = document.getElementById('amountValue');
    const amountInput   = document.getElementById('amount');

    if (amountValueEl) {
        amountValueEl.textContent = '₱' + amount.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        amountValueEl.classList.remove('updated');
        void amountValueEl.offsetWidth;
        amountValueEl.classList.add('updated');
    }
    if (amountInput) amountInput.value = amount.toFixed(2);
}

// ── IMAGE UPLOAD PREVIEW ─────────────────────────────────────
function initImageUpload() {
    const zone        = document.getElementById('uploadZone');
    const fileInput   = document.getElementById('item_image');
    const previewWrap = document.getElementById('previewWrap');
    const previewImg  = document.getElementById('previewImg');
    const previewName = document.getElementById('previewName');
    const previewSize = document.getElementById('previewSize');
    const removeBtn   = document.getElementById('removeImgBtn');
    const progressEl  = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('uploadProgressBar');

    if (!zone || !fileInput) return;

    const MAX_BYTES  = 5 * 1024 * 1024; // 5 MB
    const ALLOWED    = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    function formatBytes(bytes) {
        if (bytes < 1024)        return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function showProgress() {
        progressEl.classList.add('visible');
        progressBar.style.width = '0%';
        // Animate to 80% quickly, then wait for FileReader to finish
        setTimeout(() => { progressBar.style.width = '60%'; }, 50);
        setTimeout(() => { progressBar.style.width = '80%'; }, 300);
    }

    function finishProgress() {
        progressBar.style.width = '100%';
        setTimeout(() => {
            progressEl.classList.remove('visible');
            progressBar.style.width = '0%';
        }, 400);
    }

    function showPreview(file) {
        showProgress();

        const reader = new FileReader();
        reader.onload = function (e) {
            finishProgress();

            previewImg.src        = e.target.result;
            previewName.textContent = file.name;

            const sizeText  = formatBytes(file.size);
            const sizeEl    = document.getElementById('previewSize');
            const tooBig    = file.size > MAX_BYTES;
            sizeEl.textContent  = sizeText;
            sizeEl.className    = tooBig ? 'size-warn' : 'size-ok';

            zone.style.display        = 'none';
            previewWrap.classList.add('visible');
        };
        reader.readAsDataURL(file);
    }

    function clearPreview() {
        fileInput.value           = '';
        previewImg.src            = '';
        previewName.textContent   = '—';
        previewSize.textContent   = '—';
        previewWrap.classList.remove('visible');
        zone.style.display        = '';
    }

    function validateAndShow(file) {
        if (!file) return;

        if (!ALLOWED.includes(file.type)) {
            alert('Hindi allowed ang file type na iyan.\nGamitin ang JPG, PNG, WEBP, o GIF.');
            fileInput.value = '';
            return;
        }
        if (file.size > MAX_BYTES) {
            alert('Masyadong malaki ang larawan (' + formatBytes(file.size) + ').\nMaximum ay 5 MB lamang.');
            fileInput.value = '';
            return;
        }
        showPreview(file);
    }

    // File input change
    fileInput.addEventListener('change', function () {
        validateAndShow(this.files[0]);
    });

    // Remove button
    removeBtn.addEventListener('click', function () {
        clearPreview();
    });

    // Drag-and-drop
    zone.addEventListener('dragover', function (e) {
        e.preventDefault();
        zone.classList.add('drag-over');
    });
    zone.addEventListener('dragleave', function () {
        zone.classList.remove('drag-over');
    });
    zone.addEventListener('drop', function (e) {
        e.preventDefault();
        zone.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file) {
            // Transfer to the real file input so it submits with the form
            const dt  = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            validateAndShow(file);
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const qtyEl   = document.getElementById('quantity');
    const priceEl = document.getElementById('price');

    if (qtyEl)   qtyEl.addEventListener('input',  computeAmount);
    if (qtyEl)   qtyEl.addEventListener('change', computeAmount);
    if (priceEl) priceEl.addEventListener('input',  computeAmount);
    if (priceEl) priceEl.addEventListener('change', computeAmount);

    computeAmount();

    // Init image upload preview
    initImageUpload();

    // Auto-dismiss alerts after 4 seconds
    document.querySelectorAll('.alert').forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity    = '0';
            alert.style.transform  = 'translateY(-10px)';
            setTimeout(function() { alert.remove(); }, 500);
        }, 4000);
    });

    // ── SUPPLIER PAGE SEARCH ─────────────────────────────────
    var searchInput  = document.getElementById('supplierSearch');
    if (!searchInput) return;

    var clearBtn     = document.getElementById('searchClear');
    var infoEl       = document.getElementById('searchInfo');
    var rows         = document.querySelectorAll('.data-table tbody .table-row');
    var subtotalRow  = document.querySelector('.subtotal-row');
    var noResultsRow = document.getElementById('noResultsRow');

    searchInput.addEventListener('input', function () {
        filterSupplierItems(this.value.trim());
    });

    function filterSupplierItems(query) {
        var q       = query.toLowerCase();
        var visible = 0;

        rows.forEach(function (row) {
            var itemCell = row.querySelector('.td-item');
            var text     = itemCell ? itemCell.textContent.trim().toLowerCase() : '';
            var match    = q === '' || text.includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        // Hide subtotal row while filtering so totals don't mislead
        if (subtotalRow) subtotalRow.style.display = q ? 'none' : '';

        // Show "walang nahanap" row if zero results
        if (noResultsRow) noResultsRow.style.display = (q && visible === 0) ? '' : 'none';

        // Toggle clear button
        if (clearBtn) clearBtn.style.display = q ? '' : 'none';

        // Show results count
        if (infoEl) {
            if (q) {
                infoEl.style.display = '';
                infoEl.textContent   = visible + ' item' + (visible !== 1 ? 's' : '') + ' ang nahanap para sa "' + query + '"';
            } else {
                infoEl.style.display = 'none';
            }
        }
    }

    // Clear search
    window.clearSupplierSearch = function (e) {
        e.preventDefault();
        searchInput.value = '';
        filterSupplierItems('');
        searchInput.focus();
    };
});

// ── SINGLE DELETE MODAL ──────────────────────────────────────
function confirmDelete(id, itemName, supplierRef) {
    document.getElementById('deleteItemName').textContent = itemName;

    var url = 'delete.php?id=' + id;
    if (supplierRef) url += '&ref=' + encodeURIComponent(supplierRef);
    document.getElementById('deleteConfirmBtn').href = url;

    openModal('deleteModal');
}

function closeModal() {
    closeModalById('deleteModal');
}

// ── BULK DELETE MODAL ────────────────────────────────────────
function confirmBulkDelete(supplierName, itemCount) {
    document.getElementById('bulkDeleteSupplier').textContent = supplierName;
    document.getElementById('bulkDeleteCount').textContent    = itemCount;

    var url = 'delete.php?action=bulk&supplier=' + encodeURIComponent(supplierName);
    document.getElementById('bulkDeleteConfirmBtn').href = url;

    openModal('bulkDeleteModal');
}

function closeBulkModal() {
    closeModalById('bulkDeleteModal');
}

// ── MODAL HELPERS ────────────────────────────────────────────
function openModal(id) {
    var modal = document.getElementById(id);
    if (!modal) return;
    modal.style.removeProperty('display');
    modal.classList.add('active');
}

function closeModalById(id) {
    var modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('active');
}

// Close on backdrop click or Escape
document.addEventListener('click', function(e) {
    ['deleteModal', 'bulkDeleteModal'].forEach(function(id) {
        var modal = document.getElementById(id);
        if (modal && e.target === modal) closeModalById(id);
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModalById('deleteModal');
        closeModalById('bulkDeleteModal');
    }
});