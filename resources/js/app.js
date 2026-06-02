const state = {
    data: null,
    charts: {},
    page: 1,
    perPage: 25,
};

const chartPalette = {
    low: '#198754',
    medium: '#b58100',
    high: '#dc3545',
    primary: '#123a63',
    secondary: '#6c757d',
    warning: '#d08a00',
};

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function formatNumber(value) {
    return new Intl.NumberFormat('id-ID').format(Math.round(Number(value) || 0));
}

function formatDecimal(value) {
    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(value) || 0);
}

function rupiah(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value) || 0);
}

function riskClass(level) {
    if (Number(level) === 2) return 'risk-high';
    if (Number(level) === 1) return 'risk-medium';
    return 'risk-low';
}

function waitForChart(callback) {
    if (window.Chart) {
        callback();
        return;
    }

    setTimeout(() => waitForChart(callback), 80);
}

function destroyChart(id) {
    if (state.charts[id]) {
        state.charts[id].destroy();
    }
}

function renderKpi(kpi) {
    const items = [
        ['Total Karyawan', formatNumber(kpi.total_employees), 'Semua baris dari CSV'],
        ['Low Risk', formatNumber(kpi.low_risk), 'Risiko keluar rendah'],
        ['Medium Risk', formatNumber(kpi.medium_risk), 'Perlu dipantau HR'],
        ['High Risk', formatNumber(kpi.high_risk), `${formatDecimal(kpi.high_risk_percentage)}% dari total`],
    ];

    document.getElementById('kpiGrid').innerHTML = items.map(([label, value, note]) => `
        <article class="kpi-card">
            <span>${label}</span>
            <strong>${value}</strong>
            <small>${note}</small>
        </article>
    `).join('');
}

function renderRiskChart(riskSummary) {
    destroyChart('riskChart');

    state.charts.riskChart = new Chart(document.getElementById('riskChart'), {
        type: 'pie',
        data: {
            labels: riskSummary.map(item => item.label),
            datasets: [{
                data: riskSummary.map(item => item.count),
                backgroundColor: [chartPalette.low, chartPalette.medium, chartPalette.high],
                borderColor: '#ffffff',
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: context => `${context.label}: ${formatNumber(context.raw)} karyawan`,
                    },
                },
            },
        },
    });
}

function renderRoleChart(roleData) {
    destroyChart('roleChart');
    const canvas = document.getElementById('roleChart');
    canvas.height = Math.max(260, roleData.length * 34);

    state.charts.roleChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: roleData.map(item => item.label),
            datasets: [
                {
                    label: 'Low Risk',
                    data: roleData.map(item => item.low),
                    backgroundColor: chartPalette.low,
                },
                {
                    label: 'Medium Risk',
                    data: roleData.map(item => item.medium),
                    backgroundColor: chartPalette.medium,
                },
                {
                    label: 'High Risk',
                    data: roleData.map(item => item.high),
                    backgroundColor: chartPalette.high,
                },
            ],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            scales: {
                x: { beginAtZero: true, stacked: true },
                y: { stacked: true, ticks: { autoSkip: false } },
            },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        afterLabel: context => {
                            const item = roleData[context.dataIndex];
                            return `Total role: ${formatNumber(item.total)} karyawan`;
                        },
                    },
                },
            },
        },
    });
}

function renderRoleSummary(roleData) {
    document.getElementById('roleSummary').innerHTML = `
        <table>
            <thead>
                <tr>
                    <th>Job Role</th>
                    <th>Low</th>
                    <th>Medium</th>
                    <th>High</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                ${roleData.map(role => `
                    <tr>
                        <td>${escapeHtml(role.label)}</td>
                        <td>${formatNumber(role.low)}</td>
                        <td>${formatNumber(role.medium)}</td>
                        <td>${formatNumber(role.high)}</td>
                        <td>${formatNumber(role.total)}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

function renderHoursChart(workload) {
    destroyChart('hoursChart');

    state.charts.hoursChart = new Chart(document.getElementById('hoursChart'), {
        type: 'bar',
        data: {
            labels: workload.map(item => item.label),
            datasets: [
                {
                    label: 'Rata-rata Jam',
                    data: workload.map(item => item.hours),
                    backgroundColor: chartPalette.primary,
                },
                {
                    label: 'Rata-rata Project',
                    data: workload.map(item => item.projects),
                    backgroundColor: chartPalette.secondary,
                    yAxisID: 'projects',
                },
            ],
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Jam/Bulan' } },
                projects: {
                    beginAtZero: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    title: { display: true, text: 'Project' },
                },
            },
            plugins: { legend: { position: 'bottom' } },
        },
    });
}

function renderSatisfactionChart(satisfaction) {
    destroyChart('satisfactionChart');

    state.charts.satisfactionChart = new Chart(document.getElementById('satisfactionChart'), {
        type: 'bar',
        data: {
            labels: satisfaction.map(item => item.label),
            datasets: [
                {
                    label: 'Job Satisfaction',
                    data: satisfaction.map(item => item.job_satisfaction),
                    backgroundColor: chartPalette.primary,
                },
                {
                    label: 'Work-Life Balance',
                    data: satisfaction.map(item => item.work_life_balance),
                    backgroundColor: chartPalette.warning,
                },
            ],
        },
        options: {
            responsive: true,
            scales: {
                y: { min: 0, max: 5, ticks: { stepSize: 1 } },
            },
            plugins: { legend: { position: 'bottom' } },
        },
    });
}

function renderWellbeingSummary(satisfaction) {
    const low = satisfaction.find(item => Number(item.level) === 0) ?? {};
    const high = satisfaction.find(item => Number(item.level) === 2) ?? {};
    const satisfactionGap = Number(high.job_satisfaction ?? 0) - Number(low.job_satisfaction ?? 0);
    const balanceGap = Number(high.work_life_balance ?? 0) - Number(low.work_life_balance ?? 0);

    const cards = satisfaction.map(item => `
        <div>
            <span>${escapeHtml(item.label)}</span>
            <strong>${formatDecimal(item.job_satisfaction)} / ${formatDecimal(item.work_life_balance)}</strong>
            <small>Job Satisfaction / WLB</small>
        </div>
    `).join('');

    document.getElementById('wellbeingSummary').innerHTML = `
        ${cards}
        <div>
            <span>Selisih High - Low</span>
            <strong>${formatDecimal(satisfactionGap)} / ${formatDecimal(balanceGap)}</strong>
            <small>Nilai negatif berarti High Risk lebih rendah</small>
        </div>
    `;
}

function renderAgeChart(ageData) {
    destroyChart('ageChart');

    state.charts.ageChart = new Chart(document.getElementById('ageChart'), {
        type: 'bar',
        data: {
            labels: ageData.map(item => item.label),
            datasets: [
                { label: 'Low', data: ageData.map(item => item.low), backgroundColor: chartPalette.low },
                { label: 'Medium', data: ageData.map(item => item.medium), backgroundColor: chartPalette.medium },
                { label: 'High', data: ageData.map(item => item.high), backgroundColor: chartPalette.high },
            ],
        },
        options: {
            responsive: true,
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true },
            },
            plugins: { legend: { position: 'bottom' } },
        },
    });
}

function renderInsights(insights) {
    document.getElementById('insightList').innerHTML = insights.map((item, index) => `
        <article class="insight-item">
            <button class="insight-toggle" type="button" aria-expanded="${index === 0 ? 'true' : 'false'}">
                <span>Insight ${index + 1}</span>
                <strong>${index === 0 ? '-' : '+'}</strong>
            </button>
            <p ${index === 0 ? '' : 'hidden'}>${escapeHtml(item)}</p>
        </article>
    `).join('');

    document.querySelectorAll('.insight-toggle').forEach(button => {
        button.addEventListener('click', () => {
            const body = button.nextElementSibling;
            const isOpen = button.getAttribute('aria-expanded') === 'true';

            button.setAttribute('aria-expanded', String(!isOpen));
            button.querySelector('strong').textContent = isOpen ? '+' : '-';
            body.hidden = isOpen;
        });
    });
}

function populateRoleFilter(roleData) {
    const select = document.getElementById('roleFilter');
    const options = roleData.map(role => `<option value="${escapeHtml(role.label)}">${escapeHtml(role.label)}</option>`).join('');
    select.innerHTML = `<option value="all">Semua Job Role</option>${options}`;
}

function getFilteredRows() {
    const role = document.getElementById('roleFilter').value;
    const risk = document.getElementById('riskFilter').value;
    const keyword = document.getElementById('searchInput').value.trim().toLowerCase();

    return (state.data?.employees ?? []).filter(row => {
        const matchRole = role === 'all' || row.job_role === role;
        const matchRisk = risk === 'all' || String(row.risk_level) === risk;
        const text = [
            row.employee_id,
            row.job_role,
            row.gender,
            row.risk_label,
            row.education_level,
        ].join(' ').toLowerCase();
        const matchKeyword = keyword === '' || text.includes(keyword);

        return matchRole && matchRisk && matchKeyword;
    });
}

function renderTable() {
    const rows = getFilteredRows();
    const totalRows = rows.length;
    const pageCount = Math.max(1, Math.ceil(totalRows / state.perPage));
    state.page = Math.min(state.page, pageCount);

    const startIndex = (state.page - 1) * state.perPage;
    const pageRows = rows.slice(startIndex, startIndex + state.perPage);
    const firstRow = totalRows === 0 ? 0 : startIndex + 1;
    const lastRow = Math.min(startIndex + state.perPage, totalRows);

    document.getElementById('employeeTable').innerHTML = pageRows.map(row => `
        <tr>
            <td><strong>${escapeHtml(row.employee_id)}</strong></td>
            <td>${escapeHtml(row.job_role)}</td>
            <td>${escapeHtml(row.gender)}</td>
            <td>${formatNumber(row.age)}</td>
            <td>${rupiah(row.monthly_income)}</td>
            <td>${formatDecimal(row.job_satisfaction)}</td>
            <td>${formatDecimal(row.work_life_balance)}</td>
            <td>${formatDecimal(row.avg_monthly_hours)}</td>
            <td>${formatDecimal(row.num_projects)}</td>
            <td><span class="risk-badge ${riskClass(row.risk_level)}">${escapeHtml(row.risk_label)}</span></td>
        </tr>
    `).join('') || `
        <tr>
            <td colspan="10">Tidak ada data yang sesuai dengan filter.</td>
        </tr>
    `;

    document.getElementById('tableInfo').textContent = `Menampilkan ${formatNumber(firstRow)}-${formatNumber(lastRow)} dari ${formatNumber(totalRows)} data`;
    document.getElementById('pageInfo').textContent = `Halaman ${formatNumber(state.page)} dari ${formatNumber(pageCount)}`;
    document.getElementById('prevPage').disabled = state.page <= 1;
    document.getElementById('nextPage').disabled = state.page >= pageCount;
}

function resetPageAndRender() {
    state.page = 1;
    state.perPage = Number(document.getElementById('pageSize').value);
    renderTable();
}

function bindFilters() {
    document.getElementById('searchInput').addEventListener('input', resetPageAndRender);
    document.getElementById('roleFilter').addEventListener('change', resetPageAndRender);
    document.getElementById('riskFilter').addEventListener('change', resetPageAndRender);
    document.getElementById('pageSize').addEventListener('change', resetPageAndRender);
    document.getElementById('prevPage').addEventListener('click', () => {
        state.page -= 1;
        renderTable();
    });
    document.getElementById('nextPage').addEventListener('click', () => {
        state.page += 1;
        renderTable();
    });
    document.getElementById('resetFilter').addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        document.getElementById('roleFilter').value = 'all';
        document.getElementById('riskFilter').value = 'all';
        document.getElementById('pageSize').value = '25';
        resetPageAndRender();
    });
}

function renderDashboard(data) {
    state.data = data;
    renderKpi(data.kpi);
    populateRoleFilter(data.risk_by_role);
    renderInsights(data.insights);
    renderWellbeingSummary(data.satisfaction_by_risk);
    resetPageAndRender();

    waitForChart(() => {
        renderRiskChart(data.risk_summary);
        renderRoleChart(data.risk_by_role);
        renderRoleSummary(data.risk_by_role);
        renderHoursChart(data.workload_by_risk);
        renderSatisfactionChart(data.satisfaction_by_risk);
        renderAgeChart(data.risk_by_age_group);
    });
}

async function boot() {
    document.getElementById('kpiGrid').innerHTML = '<div class="loading">Memuat data CSV...</div>';

    try {
        const response = await fetch('/api/hr-analytics');
        if (!response.ok) {
            throw new Error('API dashboard tidak dapat diakses.');
        }
        const data = await response.json();
        renderDashboard(data);
        bindFilters();
    } catch (error) {
        document.getElementById('kpiGrid').innerHTML = `<div class="error-state">${escapeHtml(error.message)}</div>`;
        console.error(error);
    }
}

document.addEventListener('DOMContentLoaded', boot);
