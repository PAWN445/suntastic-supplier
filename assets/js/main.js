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

document.addEventListener('DOMContentLoaded', function () {
    const qtyEl   = document.getElementById('quantity');
    const priceEl = document.getElementById('price');

    if (qtyEl)   qtyEl.addEventListener('input',  computeAmount);
    if (qtyEl)   qtyEl.addEventListener('change', computeAmount);
    if (priceEl) priceEl.addEventListener('input',  computeAmount);
    if (priceEl) priceEl.addEventListener('change', computeAmount);

    computeAmount();

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