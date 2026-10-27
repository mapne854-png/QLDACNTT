function openModal(id) {
    const x = document.getElementById(id);
    if (x) {
        x.classList.remove('hidden');
        document.body.classList.add('modal-open');
    }
}

function closeModal(id) {
    const x = document.getElementById(id);
    if (x) {
        x.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }
}

function togglePanel(id) {
    document.querySelectorAll('.floating-panel').forEach(x => {
        if (x.id !== id) x.classList.add('hidden');
    });
    const x = document.getElementById(id);
    if (x) x.classList.toggle('hidden');
}

function toggleSidebar() {
    const x = document.getElementById('sidebar');
    if (x) x.classList.toggle('open');
}

function showToast(type, msg) {
    const x = document.createElement('div');
    x.className = 'toast ' + type;
    x.textContent = msg;
    document.body.appendChild(x);
    setTimeout(() => {
        x.style.opacity = '0';
        x.style.transform = 'translateY(10px)';
        setTimeout(() => x.remove(), 300);
    }, 3200);
}

function moneyV(v) {
    return new Intl.NumberFormat('vi-VN').format(Number(v || 0)) + ' ₫';
}

async function apiPost(body) {
    body.csrf = window.APP?.csrf || '';
    const r = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(body)
    });
    return r.json();
}

// Modal Backdrop Click & Shortcuts
document.addEventListener('click', e => {
    if (e.target.classList.contains('modal-backdrop')) {
        e.target.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-backdrop').forEach(m => m.classList.add('hidden'));
        document.querySelectorAll('.floating-panel').forEach(p => p.classList.add('hidden'));
        document.body.classList.remove('modal-open');
    }
});

// Notifications Read Handler
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.notice.unread').forEach(n => {
        n.addEventListener('click', async () => {
            const id = n.dataset.noticeId;
            if (id) {
                try {
                    await apiPost({ action: 'mark_notification_read', id });
                    n.classList.remove('unread');
                } catch (e) {}
            }
        });
    });

    initKanbanDragAndDrop();
});

// Interactive Kanban Drag and Drop
function initKanbanDragAndDrop() {
    const cards = document.querySelectorAll('.opportunity-card');
    const dropZones = document.querySelectorAll('.drop-zone');

    cards.forEach(card => {
        card.addEventListener('dragstart', e => {
            e.dataTransfer.setData('text/plain', card.dataset.id);
            card.classList.add('dragging');
        });

        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
        });
    });

    dropZones.forEach(zone => {
        zone.addEventListener('dragover', e => {
            e.preventDefault();
            zone.classList.add('drag-over');
        });

        zone.addEventListener('dragleave', () => {
            zone.classList.remove('drag-over');
        });

        zone.addEventListener('drop', async e => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            const oppId = e.dataTransfer.getData('text/plain');
            const targetStage = zone.dataset.stage;
            const card = document.querySelector(`.opportunity-card[data-id="${oppId}"]`);

            if (card && targetStage) {
                zone.appendChild(card);
                try {
                    const res = await apiPost({ action: 'update_opp_stage', id: oppId, stage: targetStage });
                    if (res.success) {
                        showToast('success', `Đã chuyển giai đoạn cơ hội sang "${targetStage}"`);
                    } else {
                        showToast('danger', 'Lỗi cập nhật giai đoạn cơ hội');
                    }
                } catch (err) {
                    showToast('danger', 'Lỗi kết nối máy chủ');
                }
            }
        });
    });
}

// Service Repair Order Calculator
function addRepairRow() {
    const tbody = document.querySelector('#repairItems tbody');
    if (!tbody) return;
    const idx = tbody.children.length;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            <select name="items[${idx}][type]">
                <option>Công việc</option>
                <option>Phụ tùng</option>
            </select>
        </td>
        <td><input name="items[${idx}][code]" placeholder="Mã"></td>
        <td><input name="items[${idx}][name]" required placeholder="Tên hạng mục"></td>
        <td><input name="items[${idx}][qty]" type="number" value="1" min="1" oninput="calcRepairTotal()"></td>
        <td><input name="items[${idx}][unit_price]" type="number" value="0" min="0" oninput="calcRepairTotal()"></td>
        <td class="row-total" style="font-weight:700">0 ₫</td>
        <td><button type="button" class="icon-btn" onclick="this.closest('tr').remove();calcRepairTotal()">×</button></td>
    `;
    tbody.appendChild(tr);
    calcRepairTotal();
}

function calcRepairTotal() {
    let subtotal = 0;
    document.querySelectorAll('#repairItems tbody tr').forEach(tr => {
        const q = Number(tr.querySelector('[name$="[qty]"]')?.value || 0);
        const p = Number(tr.querySelector('[name$="[unit_price]"]')?.value || 0);
        const t = q * p;
        subtotal += t;
        const cell = tr.querySelector('.row-total');
        if (cell) cell.textContent = moneyV(t);
    });
    const d = Number(document.getElementById('repairDiscount')?.value || 0);
    const tax = subtotal * 0.1;
    const total = Math.max(0, subtotal + tax - d);

    for (const [id, v] of [
        ['repairSubtotal', subtotal],
        ['repairTax', tax],
        ['repairDiscountView', d],
        ['repairTotal', total]
    ]) {
        const el = document.getElementById(id);
        if (el) el.textContent = moneyV(v);
    }
}

function prepareRepairSubmit() {
    calcRepairTotal();
    return true;
}
