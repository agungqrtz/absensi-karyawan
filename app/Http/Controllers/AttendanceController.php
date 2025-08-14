<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Import DB Facade
use Illuminate\Support\Facades\Validator;
use App\Exports\RecapExport;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    /**
     * Menampilkan halaman utama absensi.
     */
    public function index()
    {
        return view('attendance');
    }

    /**
     * Menyimpan data absensi yang di-submit secara massal.
     * API Endpoint: POST /api/attendance
     */
    public function submitAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.tanggal' => 'required|date',
            'data.*.employee_id' => 'required|exists:employees,id',
            'data.*.status' => 'required|in:Hadir,Izin,Sakit,Alpha',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
        }

        $attendanceData = $request->input('data');
        $processedCount = 0;

        foreach ($attendanceData as $record) {
            Attendance::updateOrCreate(
                ['employee_id' => $record['employee_id'], 'date' => $record['tanggal']],
                ['status' => $record['status']]
            );
            $processedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil memproses {$processedCount} data absensi!"
        ]);
    }

    /**
     * Mengambil data rekap bulanan.
     * API Endpoint: GET /api/recap
     */
    public function getRecap(Request $request)
    {
        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

        $detailData = Attendance::with('employee')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date', 'asc')
            ->orderBy('employee_id', 'asc')
            ->get();
        
        $detailData->transform(function ($item) {
            $item->tanggal = $item->date;
            return $item;
        });

        $recapSummary = $detailData->groupBy('employee.name')->map(function ($items) {
            return $items->countBy('status');
        });

        return response()->json([
            'recap' => $recapSummary,
            'detail' => $detailData
        ]);
    }

    /**
     * Menyimpan satu data absensi baru (dari form CRUD).
     * API Endpoint: POST /api/attendance-data
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'employee_id' => 'required|exists:employees,id',
            'status' => 'required|string|in:Hadir,Izin,Sakit,Alpha',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $existing = Attendance::where('date', $request->tanggal)
                               ->where('employee_id', $request->employee_id)
                               ->first();

        if ($existing) {
            return response()->json(['message' => 'Data absensi untuk karyawan ini pada tanggal tersebut sudah ada.'], 409);
        }

        Attendance::create(['employee_id' => $request->employee_id, 'date' => $request->tanggal, 'status' => $request->status]);

        return response()->json(['message' => '➕ Data berhasil ditambahkan!'], 201);
    }

    /**
     * Memperbarui data absensi yang sudah ada (dari form CRUD).
     * API Endpoint: PUT /api/attendance-data/{attendance}
     */
    public function update(Request $request, Attendance $attendance)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'status' => 'required|string|in:Hadir,Izin,Sakit,Alpha',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $attendance->update(['date' => $request->tanggal, 'status' => $request->status]);

        return response()->json(['message' => '✏️ Data berhasil diperbarui!']);
    }

    /**
     * Menghapus data absensi.
     * API Endpoint: DELETE /api/attendance-data/{attendance}
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return response()->json(['message' => '🗑️ Data absensi berhasil dihapus!'], 200);
    }

    // ==================================================
    // METODE UNTUK KELOLA KARYAWAN
    // ==================================================

    /**
     * Mengambil semua data karyawan.
     * API Endpoint: GET /api/employees
     */
    public function getEmployees()
    {
        return response()->json(Employee::orderBy('name')->get());
    }

    /**
     * Menyimpan karyawan baru.
     * API Endpoint: POST /api/employees
     */
    public function storeEmployee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:employees,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Employee::create($request->only('name'));

        return response()->json(['message' => 'Karyawan berhasil ditambahkan!'], 201);
    }

    /**
     * Menghapus karyawan beserta data absensinya.
     * API Endpoint: DELETE /api/employees/{employee}
     */
    public function destroyEmployee(Employee $employee)
    {
        // Hapus semua data absensi terkait sebelum menghapus karyawan
        $employee->attendances()->delete();
        $employee->delete();

        return response()->json(['message' => 'Karyawan dan semua data absensinya berhasil dihapus!']);
    }

    // ==================================================
    // METODE UNTUK EXPORT
    // ==================================================
    
    /**
     * Menangani permintaan ekspor rekap ke Excel.
     * API Endpoint: GET /api/export/recap
     */
    public function exportRecap(Request $request)
    {
        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

        $monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        $monthName = $monthNames[$month - 1];

        $fileName = 'rekap_absensi_' . strtolower($monthName) . '_' . $year . '.xlsx';

        return Excel::download(new RecapExport((int)$month, (int)$year), $fileName);
    }

    /**
     * Mengambil data untuk statistik dan grafik.
     * API Endpoint: GET /api/statistics
     */
    public function getStatisticsData(Request $request)
    {
        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

        // 1. Data untuk Pie Chart (Persentase Keseluruhan)
        $overallStats = Attendance::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // 2. Data untuk Line Chart (Tren Kehadiran Harian)
        $dailyAttendance = Attendance::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('status', 'Hadir')
            ->select(DB::raw('DAY(date) as day'), DB::raw('count(*) as total'))
            ->groupBy('day')
            ->orderBy('day', 'asc')
            ->pluck('total', 'day');

        // Siapkan data untuk semua hari dalam sebulan agar grafik lengkap
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $dailyTrend = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dailyTrend[$day] = $dailyAttendance->get($day, 0);
        }

        return response()->json([
            'overall' => $overallStats,
            'daily_trend' => $dailyTrend,
        ]);
    }
}
