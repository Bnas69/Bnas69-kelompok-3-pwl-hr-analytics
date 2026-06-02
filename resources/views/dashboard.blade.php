<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Dashboard Human Resource Analytics untuk tugas besar Pemrograman Web Lanjut.">
    <title>Human Resource Analytics Dashboard | Kelompok 3</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="page-shell">
        <header class="academic-header">
            <div>
                <p>Tugas Besar Pemrograman Web Lanjut</p>
                <h1>{{ $university }}</h1>
                <span>{{ $group }}</span>
            </div>
            <form method="post" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <span>Login: {{ $username }}</span>
                <button type="submit">Logout</button>
            </form>
        </header>

        <section class="dashboard-title-card">
            <div>
                <p class="eyebrow">Dashboard Akademik</p>
                <h2>Human Resource Analytics Dashboard</h2>
                <p class="dashboard-subtitle">
                    Analisis risiko attrition karyawan berdasarkan dataset internal perusahaan
                    dengan klasifikasi Low, Medium, dan High Risk.
                </p>
            </div>
        </section>

        <section class="intro-grid">
            <article class="panel-card">
                <div class="section-heading plain-heading">
                    <div>
                        <p class="eyebrow">Informasi Tugas</p>
                        <h3>Detail Project</h3>
                    </div>
                </div>

                <div class="task-info-grid">
                    <div class="info-row">
                        <span>Nama Tugas</span>
                        <strong>Tugas Besar Pemrograman Web Lanjut</strong>
                    </div>
                    <div class="info-row">
                        <span>Topik</span>
                        <strong>Human Resource Analytics</strong>
                    </div>
                    <div class="info-row">
                        <span>Dataset</span>
                        <strong>HR Employee Attrition Risk</strong>
                    </div>
                    <div class="info-row">
                        <span>Target</span>
                        <strong>Attrition_Risk_Level</strong>
                    </div>
                    <div class="info-row">
                        <span>Jumlah Data</span>
                        <strong>15.000 karyawan</strong>
                    </div>
                </div>
            </article>

            <article class="panel-card">
                <div class="section-heading plain-heading">
                    <div>
                        <p class="eyebrow">Kelompok 3</p>
                        <h3>Anggota Kelompok</h3>
                    </div>
                </div>

                <div class="simple-table-wrapper">
                    <table class="member-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>NIM</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Septian Dwi Saputra</td>
                                <td>411232056</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Tiara Adisa Marcianda</td>
                                <td>411232040</td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Izatul Janah</td>
                                <td>411232019</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        <section class="upload-card">
            <div class="section-heading table-heading">
                <div>
                    <p class="eyebrow">Manajemen File</p>
                    <h3>Upload & Download Dokumen</h3>
                </div>
                <span class="table-note">{{ $uploadedDocuments->count() }} file tersimpan</span>
            </div>

            @if (session('document_status'))
                <div class="success-state">{{ session('document_status') }}</div>
            @endif

            @if ($documentStorageNotice)
                <div class="error-state">{{ $documentStorageNotice }}</div>
            @endif

            @if ($errors->any())
                <div class="error-state">
                    {{ $errors->first() }}
                </div>
            @endif

            <form class="upload-form" method="post" action="{{ route('documents.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="filter-group">
                    <label for="documentTitle">Nama Tampilan</label>
                    <input id="documentTitle" name="title" type="text" value="{{ old('title') }}" maxlength="150" placeholder="Opsional">
                </div>
                <div class="filter-group">
                    <label for="storageMode">Mode Penyimpanan</label>
                    <select id="storageMode" name="storage_mode">
                        <option value="database" @selected(old('storage_mode', 'database') === 'database')>Database / tidak lokal</option>
                        <option value="local" @selected(old('storage_mode') === 'local')>Storage lokal</option>
                    </select>
                </div>
                <div class="filter-group upload-file-field">
                    <label for="documentFile">File</label>
                    <input id="documentFile" name="document" type="file" required>
                </div>
                <button type="submit">Upload File</button>
            </form>

            <div class="table-wrapper document-table-wrapper">
                <table class="document-table">
                    <thead>
                        <tr>
                            <th>Nama File</th>
                            <th>Tipe</th>
                            <th>Ukuran</th>
                            <th>Penyimpanan</th>
                            <th>Upload</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($uploadedDocuments as $document)
                            <tr>
                                <td>
                                    <strong>{{ $document->display_name }}</strong>
                                    <small>{{ $document->original_name }}</small>
                                </td>
                                <td>{{ strtoupper($document->extension ?: 'FILE') }}</td>
                                <td>{{ $document->formatted_size }}</td>
                                <td>
                                    <span class="storage-badge {{ $document->storage_mode === 'local' ? 'storage-local' : 'storage-database' }}">
                                        {{ $document->mode_label }}
                                    </span>
                                </td>
                                <td>{{ $document->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="document-actions">
                                        <a href="{{ route('documents.download', $document) }}" class="action-link">Download</a>
                                        <form method="post" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('Hapus file ini?')">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="danger-button">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">Belum ada file yang diupload.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="toolbar-card">
            <div class="toolbar-title">
                <p class="eyebrow">Filter Data</p>
                <h3>Pengaturan Tampilan Data</h3>
            </div>

            <div class="filter-group">
                <label for="searchInput">Cari Karyawan</label>
                <input id="searchInput" type="search" placeholder="EMP-00001 / role / gender">
            </div>
            <div class="filter-group">
                <label for="roleFilter">Filter Job Role</label>
                <select id="roleFilter">
                    <option value="all">Semua Job Role</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="riskFilter">Filter Risiko</label>
                <select id="riskFilter">
                    <option value="all">Semua Risiko</option>
                    <option value="0">Low Risk</option>
                    <option value="1">Medium Risk</option>
                    <option value="2">High Risk</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="pageSize">Data per Halaman</label>
                <select id="pageSize">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="250">250</option>
                </select>
            </div>
            <button id="resetFilter" type="button" class="secondary-button">Reset Filter</button>
        </section>

        <section class="kpi-grid" id="kpiGrid" aria-label="KPI Human Resource Analytics"></section>

        <section class="content-grid two-columns">
            <article class="chart-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Distribusi Risiko</p>
                        <h3>Distribusi Risiko Attrition</h3>
                    </div>
                </div>
                <div class="chart-area">
                    <canvas id="riskChart" height="240"></canvas>
                </div>
            </article>

            <article class="chart-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Job Role</p>
                        <h3>Risiko Attrition Berdasarkan Job Role</h3>
                    </div>
                </div>
                <div class="chart-area chart-area-scroll">
                    <canvas id="roleChart" height="240"></canvas>
                </div>
                <div class="role-summary" id="roleSummary"></div>
            </article>
        </section>

        <section class="content-grid two-columns">
            <article class="chart-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Workload</p>
                        <h3>Rata-rata Jam Kerja Berdasarkan Risiko</h3>
                    </div>
                </div>
                <div class="chart-area">
                    <canvas id="hoursChart" height="240"></canvas>
                </div>
            </article>

            <article class="chart-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Engagement</p>
                        <h3>Kepuasan Kerja dan Work-Life Balance</h3>
                    </div>
                </div>
                <div class="chart-area">
                    <canvas id="satisfactionChart" height="240"></canvas>
                </div>
                <div class="mini-summary" id="wellbeingSummary"></div>
            </article>
        </section>

        <section class="content-grid two-columns">
            <article class="chart-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Kelompok Usia</p>
                        <h3>Risiko Berdasarkan Kelompok Usia</h3>
                    </div>
                </div>
                <div class="chart-area">
                    <canvas id="ageChart" height="240"></canvas>
                </div>
            </article>

            <article class="chart-card insight-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Analisis</p>
                        <h3>Rekomendasi Analisis HR</h3>
                    </div>
                </div>
                <div id="insightList"></div>
            </article>
        </section>

        <section class="table-card">
            <div class="section-heading table-heading">
                <div>
                    <p class="eyebrow">Data CSV</p>
                    <h3>Data Karyawan Berdasarkan Dataset CSV</h3>
                </div>
                <span class="table-note" id="tableInfo">Memuat data...</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Job Role</th>
                            <th>Gender</th>
                            <th>Age</th>
                            <th>Income</th>
                            <th>Job Satisfaction</th>
                            <th>WLB</th>
                            <th>Hours</th>
                            <th>Projects</th>
                            <th>Risk</th>
                        </tr>
                    </thead>
                    <tbody id="employeeTable"></tbody>
                </table>
            </div>
            <div class="pagination-bar">
                <button id="prevPage" type="button">Sebelumnya</button>
                <span id="pageInfo">Halaman 1</span>
                <button id="nextPage" type="button">Berikutnya</button>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js" defer></script>
</body>
</html>
