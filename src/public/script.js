const API_BASE = '/api';

async function apiRequest(endpoint, options = {}) {
    const response = await fetch(`${API_BASE}${endpoint}`, {
        headers: { 'Accept': 'application/json', ...(options.headers || {}) },
        ...options,
    });

    const contentType = response.headers.get('content-type') || '';
    const data = contentType.includes('application/json') ? await response.json() : await response.text();

    if (!response.ok) {
        const message = typeof data === 'object' && data ? (data.error || 'Request failed') : 'Request failed';
        throw new Error(message);
    }

    return data;
}

document.addEventListener('DOMContentLoaded', () => {
    const leadForm = document.getElementById('leadForm');
    const filterForm = document.getElementById('filterForm');

    if (leadForm) {
        initLeadForm(leadForm);
    }

    if (filterForm) {
        initStatusTable();
    }
});

function initLeadForm(form) {
    const resultBox = document.getElementById('formResult');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        resultBox.style.display = 'block';
        resultBox.textContent = 'Відправка...';
        resultBox.className = 'result-box';

        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());

        try {
            const data = await apiRequest('/index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            if (data.status === true || data.status === 'true') {
                resultBox.textContent = `Успіх! Lead ID: ${data.id}, email: ${data.email}`;
                resultBox.classList.add('success');
                form.reset();
            } else {
                resultBox.textContent = `Помилка: ${data.error || 'невідома помилка'}`;
                resultBox.classList.add('error');
            }
        } catch (err) {
            resultBox.textContent = err.message || 'Помилка з’єднання з сервером';
            resultBox.classList.add('error');
        }
    });
}

function initStatusTable() {
    const filterBtn = document.getElementById('filterBtn');

    loadStatuses();

    filterBtn.addEventListener('click', () => {
        loadStatuses();
    });
}

async function loadStatuses() {
    const tbody = document.getElementById('leadsTableBody');
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;

    tbody.innerHTML = '<tr><td colspan="4">Завантаження...</td></tr>';

    const params = new URLSearchParams();
    if (dateFrom) params.append('date_from', dateFrom + ' 00:00:00');
    if (dateTo) params.append('date_to', dateTo + ' 23:59:59');
    params.append('action', 'getstatuses');

    try {
        const data = await apiRequest('/index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: params.toString(),
        });

        if (!data || data.status === false || data.status === 'false') {
            tbody.innerHTML = `<tr><td colspan="4">Помилка: ${data?.error || 'невідома помилка'}</td></tr>`;
            return;
        }

        const leads = Array.isArray(data) ? data : (data.data || data.leads || []);

        if (!leads.length) {
            tbody.innerHTML = '<tr><td colspan="4">Немає даних за обраний період</td></tr>';
            return;
        }

        tbody.innerHTML = leads.map(lead => `
            <tr>
                <td>${escapeHtml(lead.id)}</td>
                <td>${escapeHtml(lead.email)}</td>
                <td>${escapeHtml(lead.status)}</td>
                <td>${escapeHtml(lead.ftd)}</td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="4">${escapeHtml(err.message || 'Помилка з’єднання з сервером')}</td></tr>`;
    }
}

function escapeHtml(value) {
    if (value === null || value === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(value);
    return div.innerHTML;
}