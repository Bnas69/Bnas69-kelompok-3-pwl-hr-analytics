<?php

namespace App\Http\Controllers;

use App\Models\UploadedDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HrAnalyticsController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (! session('hr_logged_in')) {
            return redirect()->route('login');
        }

        $uploadedDocuments = collect();
        $documentStorageNotice = null;

        try {
            $uploadedDocuments = UploadedDocument::latest()->take(50)->get();
        } catch (\Throwable) {
            $documentStorageNotice = 'Tabel uploaded_documents belum siap. Jalankan php artisan migrate agar upload/download aktif.';
        }

        return view('dashboard', [
            'course' => 'Pemrograman Web Lanjut',
            'university' => 'Universitas Dian Nusantara',
            'group' => 'Kelompok 3 - Human Resource Analytics',
            'username' => session('hr_username', 'admin'),
            'uploadedDocuments' => $uploadedDocuments,
            'documentStorageNotice' => $documentStorageNotice,
            'members' => [
                'Septian Dwi Saputra - 411232056',
                'Tiara Adisa Marcianda - 411232040',
                'Izatul Janah - 411232019',
            ],
        ]);
    }

    public function analytics(): JsonResponse
    {
        if (! session('hr_logged_in')) {
            return response()->json([
                'message' => 'Silakan login terlebih dahulu.',
            ], 401);
        }

        $csvPath = $this->resolveCsvPath();

        if ($csvPath === null) {
            return response()->json([
                'message' => 'File CSV tidak ditemukan. Simpan file di public/data/hr_employee_attrition_data.csv atau set HR_ANALYTICS_CSV di file .env.',
            ], 404);
        }

        $rows = $this->readCsv($csvPath);
        $total = count($rows);

        $riskLabels = [0 => 'Low Risk', 1 => 'Medium Risk', 2 => 'High Risk'];
        $riskTone = [0 => 'Aman', 1 => 'Perlu Dipantau', 2 => 'Kritis'];
        $riskCounts = [0 => 0, 1 => 0, 2 => 0];
        $riskSummary = [];
        $byRole = [];
        $byGender = [];
        $byEducation = [];
        $byAgeGroup = [];
        $workloadByRisk = [];
        $satisfactionByRisk = [];
        $incomeByRisk = [];
        $employees = [];
        $highRiskEmployees = [];

        $sumAge = 0;
        $sumIncome = 0;
        $sumHours = 0;
        $sumSatisfaction = 0;
        $sumWorkLife = 0;
        $sumDistance = 0;

        foreach ($rows as $row) {
            $risk = (int) ($row['Attrition_Risk_Level'] ?? 0);
            $risk = array_key_exists($risk, $riskCounts) ? $risk : 0;
            $role = trim((string) ($row['Job_Role'] ?? 'Tidak diketahui'));
            $gender = trim((string) ($row['Gender'] ?? '-'));
            $education = 'Level '.trim((string) ($row['Education_Level'] ?? '-'));
            $age = (int) ($row['Age'] ?? 0);
            $ageGroup = $this->ageGroup($age);

            $income = (float) ($row['Monthly_Income'] ?? 0);
            $hours = (float) ($row['Avg_Monthly_Hours'] ?? 0);
            $projects = (float) ($row['Num_Projects'] ?? 0);
            $satisfaction = (float) ($row['Job_Satisfaction'] ?? 0);
            $workLife = (float) ($row['Work_Life_Balance'] ?? 0);
            $distance = (float) ($row['Distance_From_Home_KM'] ?? 0);

            $riskCounts[$risk] = ($riskCounts[$risk] ?? 0) + 1;

            $this->incrementRiskBucket($byRole, $role, $risk);
            $this->incrementRiskBucket($byGender, $gender, $risk);
            $this->incrementRiskBucket($byEducation, $education, $risk);
            $this->incrementRiskBucket($byAgeGroup, $ageGroup, $risk);

            $this->appendMetric($workloadByRisk, $risk, 'hours', $hours);
            $this->appendMetric($workloadByRisk, $risk, 'projects', $projects);
            $this->appendMetric($satisfactionByRisk, $risk, 'job_satisfaction', $satisfaction);
            $this->appendMetric($satisfactionByRisk, $risk, 'work_life_balance', $workLife);
            $this->appendMetric($incomeByRisk, $risk, 'income', $income);

            $sumAge += $age;
            $sumIncome += $income;
            $sumHours += $hours;
            $sumSatisfaction += $satisfaction;
            $sumWorkLife += $workLife;
            $sumDistance += $distance;

            $employee = [
                'employee_id' => trim((string) ($row['Employee_ID'] ?? '-')),
                'age' => $age,
                'gender' => $gender,
                'job_role' => $role,
                'monthly_income' => $income,
                'job_satisfaction' => $satisfaction,
                'work_life_balance' => $workLife,
                'distance_from_home_km' => $distance,
                'num_projects' => $projects,
                'avg_monthly_hours' => $hours,
                'years_at_company' => (int) ($row['Years_at_Company'] ?? 0),
                'years_since_last_promotion' => (int) ($row['Years_Since_Last_Promotion'] ?? 0),
                'training_times_last_year' => (int) ($row['Training_Times_Last_Year'] ?? 0),
                'education_level' => (int) ($row['Education_Level'] ?? 0),
                'risk_level' => $risk,
                'risk_label' => $riskLabels[$risk] ?? 'Unknown',
                'risk_tone' => $riskTone[$risk] ?? '-',
            ];

            $employees[] = $employee;

            if ($risk === 2) {
                $highRiskEmployees[] = $employee;
            }
        }

        foreach ($riskCounts as $risk => $count) {
            $riskSummary[] = [
                'level' => $risk,
                'label' => $riskLabels[$risk] ?? 'Unknown',
                'tone' => $riskTone[$risk] ?? '-',
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0,
            ];
        }

        usort($highRiskEmployees, function ($a, $b) {
            return [$a['job_satisfaction'], $a['work_life_balance'], -$a['avg_monthly_hours']]
                <=> [$b['job_satisfaction'], $b['work_life_balance'], -$b['avg_monthly_hours']];
        });

        return response()->json([
            'metadata' => [
                'title' => 'Human Resource Analytics',
                'dataset_rows' => $total,
                'csv_file' => basename($csvPath),
                'target' => 'Attrition_Risk_Level: 0 Low, 1 Medium, 2 High',
                'updated_at' => now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            ],
            'kpi' => [
                'total_employees' => $total,
                'high_risk' => $riskCounts[2],
                'medium_risk' => $riskCounts[1],
                'low_risk' => $riskCounts[0],
                'high_risk_percentage' => $total > 0 ? round(($riskCounts[2] / $total) * 100, 2) : 0,
                'avg_age' => $this->average($sumAge, $total),
                'avg_monthly_income' => $this->average($sumIncome, $total),
                'avg_monthly_hours' => $this->average($sumHours, $total),
                'avg_job_satisfaction' => $this->average($sumSatisfaction, $total),
                'avg_work_life_balance' => $this->average($sumWorkLife, $total),
                'avg_distance_from_home' => $this->average($sumDistance, $total),
            ],
            'risk_summary' => $riskSummary,
            'risk_by_role' => $this->formatRiskBuckets($byRole),
            'risk_by_gender' => $this->formatRiskBuckets($byGender),
            'risk_by_education' => $this->formatRiskBuckets($byEducation),
            'risk_by_age_group' => $this->formatRiskBuckets($byAgeGroup),
            'workload_by_risk' => $this->formatMetricAverages($workloadByRisk, ['hours', 'projects'], $riskLabels),
            'satisfaction_by_risk' => $this->formatMetricAverages($satisfactionByRisk, ['job_satisfaction', 'work_life_balance'], $riskLabels),
            'income_by_risk' => $this->formatMetricAverages($incomeByRisk, ['income'], $riskLabels),
            'employees' => $employees,
            'high_risk_employees' => $highRiskEmployees,
            'priority_employees' => array_slice($highRiskEmployees, 0, 25),
            'insights' => $this->buildInsights($riskCounts, $total, $byRole, $workloadByRisk, $satisfactionByRisk),
        ]);
    }

    private function resolveCsvPath(): ?string
    {
        $configuredPath = env('HR_ANALYTICS_CSV');
        $candidates = [];

        if ($configuredPath) {
            $candidates[] = $this->absolutePath((string) $configuredPath);
        }

        $candidates = array_merge($candidates, [
            public_path('data/hr_employee_attrition_data.csv'),
            public_path('data/CSV.csv'),
            public_path('data/CSV'),
            public_path('CSV.csv'),
            public_path('CSV'),
            base_path('CSV.csv'),
            base_path('CSV'),
            storage_path('app/CSV.csv'),
            storage_path('app/CSV'),
        ]);

        foreach (array_unique($candidates) as $path) {
            if (is_string($path) && is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function absolutePath(string $path): string
    {
        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }

        return base_path($path);
    }

    private function readCsv(string $path): array
    {
        $file = new \SplFileObject($path);
        $file->setCsvControl($this->detectDelimiter($path));
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);

        $headers = [];
        $rows = [];

        foreach ($file as $index => $row) {
            if ($row === [null] || $row === false) {
                continue;
            }

            if ($index === 0) {
                $headers = array_map(fn ($header) => trim((string) $header, "\xEF\xBB\xBF \t\n\r\0\x0B"), $row);
                continue;
            }

            if ($headers === []) {
                continue;
            }

            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), null);
            }

            if (count($row) > count($headers)) {
                $row = array_slice($row, 0, count($headers));
            }

            $rows[] = array_combine($headers, $row);
        }

        return $rows;
    }

    private function detectDelimiter(string $path): string
    {
        $handle = fopen($path, 'r');
        $firstLine = $handle ? (fgets($handle) ?: '') : '';

        if ($handle) {
            fclose($handle);
        }

        $delimiters = [
            ',' => substr_count($firstLine, ','),
            ';' => substr_count($firstLine, ';'),
            "\t" => substr_count($firstLine, "\t"),
        ];

        arsort($delimiters);

        return array_key_first($delimiters) ?: ',';
    }

    private function incrementRiskBucket(array &$bucket, string $key, int $risk): void
    {
        if (! isset($bucket[$key])) {
            $bucket[$key] = ['label' => $key, 'low' => 0, 'medium' => 0, 'high' => 0, 'total' => 0];
        }

        $field = match ($risk) {
            0 => 'low',
            1 => 'medium',
            2 => 'high',
            default => 'unknown',
        };

        $bucket[$key][$field] = ($bucket[$key][$field] ?? 0) + 1;
        $bucket[$key]['total']++;
    }

    private function appendMetric(array &$bucket, int $risk, string $metric, float $value): void
    {
        if (! isset($bucket[$risk])) {
            $bucket[$risk] = [];
        }
        if (! isset($bucket[$risk][$metric])) {
            $bucket[$risk][$metric] = ['sum' => 0, 'count' => 0];
        }

        $bucket[$risk][$metric]['sum'] += $value;
        $bucket[$risk][$metric]['count']++;
    }

    private function formatRiskBuckets(array $bucket): array
    {
        $items = array_values($bucket);
        foreach ($items as &$item) {
            $item['high_percentage'] = $item['total'] > 0 ? round(($item['high'] / $item['total']) * 100, 2) : 0;
        }
        unset($item);

        usort($items, fn ($a, $b) => $b['high'] <=> $a['high']);

        return $items;
    }

    private function formatMetricAverages(array $bucket, array $metrics, array $riskLabels): array
    {
        $result = [];

        foreach ([0, 1, 2] as $risk) {
            $row = ['level' => $risk, 'label' => $riskLabels[$risk] ?? 'Unknown'];
            foreach ($metrics as $metric) {
                $sum = $bucket[$risk][$metric]['sum'] ?? 0;
                $count = $bucket[$risk][$metric]['count'] ?? 0;
                $row[$metric] = $this->average($sum, $count);
            }
            $result[] = $row;
        }

        return $result;
    }

    private function average(float $sum, int $count): float
    {
        return $count > 0 ? round($sum / $count, 2) : 0;
    }

    private function ageGroup(int $age): string
    {
        return match (true) {
            $age < 25 => '< 25 Tahun',
            $age <= 34 => '25-34 Tahun',
            $age <= 44 => '35-44 Tahun',
            $age <= 54 => '45-54 Tahun',
            default => '55+ Tahun',
        };
    }

    private function buildInsights(array $riskCounts, int $total, array $byRole, array $workload, array $satisfaction): array
    {
        $roles = $this->formatRiskBuckets($byRole);
        $topRole = $roles[0] ?? ['label' => '-', 'high' => 0, 'high_percentage' => 0];
        $highPercent = $total > 0 ? round(($riskCounts[2] / $total) * 100, 2) : 0;

        $highHours = $this->average($workload[2]['hours']['sum'] ?? 0, $workload[2]['hours']['count'] ?? 0);
        $lowHours = $this->average($workload[0]['hours']['sum'] ?? 0, $workload[0]['hours']['count'] ?? 0);
        $highSatisfaction = $this->average($satisfaction[2]['job_satisfaction']['sum'] ?? 0, $satisfaction[2]['job_satisfaction']['count'] ?? 0);
        $lowSatisfaction = $this->average($satisfaction[0]['job_satisfaction']['sum'] ?? 0, $satisfaction[0]['job_satisfaction']['count'] ?? 0);
        $riskStatus = match (true) {
            $highPercent >= 20 => 'tinggi',
            $highPercent >= 10 => 'cukup tinggi',
            default => 'masih rendah',
        };
        $workloadNote = $highHours > $lowHours
            ? 'Jam kerja kelompok High Risk lebih besar, jadi beban kerja perlu dicek lebih dulu.'
            : 'Jam kerja High Risk tidak lebih besar dari Low Risk, jadi penyebab lain seperti kepuasan dan promosi perlu dilihat.';
        $satisfactionNote = $highSatisfaction < $lowSatisfaction
            ? 'Kepuasan kerja High Risk lebih rendah dari Low Risk, sehingga engagement menjadi prioritas.'
            : 'Kepuasan kerja High Risk tidak lebih rendah dari Low Risk, jadi analisis bisa lanjut ke faktor jarak, promosi, atau training.';

        return [
            "Persentase High Risk adalah {$highPercent}% dan statusnya {$riskStatus}. Angka ini dihitung dari jumlah High Risk dibagi total karyawan.",
            "Role dengan jumlah High Risk terbesar adalah {$topRole['label']} sebanyak {$topRole['high']} karyawan ({$topRole['high_percentage']}% dari role tersebut).",
            "Rata-rata jam kerja High Risk adalah {$highHours} jam/bulan, sedangkan Low Risk {$lowHours} jam/bulan. {$workloadNote}",
            "Rata-rata kepuasan kerja High Risk adalah {$highSatisfaction}, sedangkan Low Risk {$lowSatisfaction}. {$satisfactionNote}",
        ];
    }
}
