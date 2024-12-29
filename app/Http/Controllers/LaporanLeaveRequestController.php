<?php

namespace App\Http\Controllers;

use App\Models\LeaveBalances;
use App\Models\LeaveRequestTickets;
use App\Models\LeaveTypes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;


class LaporanLeaveRequestController extends Controller
{
    public function index()
    {
        $months = [];
        for ($month = 1; $month <= 12; $month++) {
            $months[$month] = Carbon::create()->month($month)->format('F');
        }

        // Generate a list of years, e.g., the last 20 years
        $years = range(Carbon::now()->year - 20, Carbon::now()->year);

        return view('admin.laporan.index', [
            'months' => $months,
            'years' => $years,
        ]);
    }

    public function getDataJSON(Request $request)
    {
        if ($request->has('tipe_pengajuan') && $request->tipe_pengajuan === 'Izin') {
            $leaveTypeIds = LeaveTypes::whereIn('name', ['Sakit', 'Izin Urgent'])->pluck('id')->toArray();
        } elseif ($request->has('tipe_pengajuan') && $request->tipe_pengajuan === 'Cuti') {
            $leaveTypeIds = LeaveTypes::whereNotIn('name', ['Sakit', 'Izin Urgent'])->pluck('id')->toArray();
        } else {
            // Jika tipe pengajuan tidak disebutkan, ambil semua tipe
            $leaveTypeIds = LeaveTypes::whereNotIn('name', ['Sakit', 'Izin Urgent'])->pluck('id')->toArray();
        }

        $query = LeaveRequestTickets::whereHas('leaveRequests', function ($q) use ($leaveTypeIds, $request) {
            $q->where('status', 'APPROVED') // Hanya data dengan status APPROVED
                ->whereIn('leave_type_id', $leaveTypeIds);

            // Filter berdasarkan bulan
            if ($request->has('bulan') && !empty($request->bulan)) {
                $q->whereMonth('created_at', $request->bulan);
            }

            // Filter berdasarkan tahun
            if ($request->has('tahun') && !empty($request->tahun)) {
                $q->whereYear('created_at', $request->tahun);
            }

            if ($request->has('filter_date') && !empty($request->filter_date)) {
                $q->whereDate('created_at', $request->filter_date);
            }
        })
            ->with(['leaveRequests' => function ($q) use ($leaveTypeIds, $request) {
                $q->with(['approver', 'requester', 'leaveBalance']) // Relasi approver dan requester
                    ->whereIn('leave_type_id', $leaveTypeIds)
                    ->orderBy('created_at', 'desc'); // Urutkan berdasarkan tanggal terbaru

                // Filter tambahan (jika ada)
                if ($request->has('bulan') && !empty($request->bulan)) {
                    $q->whereMonth('created_at', $request->bulan);
                }

                if ($request->has('tahun') && !empty($request->tahun)) {
                    $q->whereYear('created_at', $request->tahun);
                }

                if ($request->has('filter_date') && !empty($request->filter_date)) {
                    $q->whereDate('created_at', $request->filter_date);
                }
            }]);

        // Paginate data
        $data = $query->paginate(5);


        return response()->json($data);
    }

    public function exportExcel(Request $request)
    {
        if ($request->has('tipe_pengajuan_export') && $request->tipe_pengajuan_export === 'Izin') {
            $leaveTypeIds = LeaveTypes::whereIn('name', ['Sakit', 'Izin Urgent'])->pluck('id')->toArray();
        } elseif ($request->has('tipe_pengajuan_export') && $request->tipe_pengajuan_export === 'Cuti') {
            $leaveTypeIds = LeaveTypes::whereNotIn('name', ['Sakit', 'Izin Urgent'])->pluck('id')->toArray();
        } else {
            // Jika tipe pengajuan tidak disebutkan, ambil semua tipe
            $leaveTypeIds = LeaveTypes::whereNotIn('name', ['Sakit', 'Izin Urgent'])->pluck('id')->toArray();
        }

        $query = LeaveRequestTickets::whereHas('leaveRequests', function ($q) use ($leaveTypeIds, $request) {
            $q->where('status', 'APPROVED') // Hanya data dengan status APPROVED
                ->whereIn('leave_type_id', $leaveTypeIds);

            // Filter berdasarkan bulan
            if ($request->has('bulan_export') && !empty($request->bulan_export)) {
                $q->whereMonth('created_at', $request->bulan_export);
            }

            // Filter berdasarkan tahun
            if ($request->has('tahun_export') && !empty($request->tahun_export)) {
                $q->whereYear('created_at', $request->tahun_export);
            }

            if ($request->has('hari_export') && !empty($request->hari_export)) {
                $q->whereDate('created_at', $request->hari_export);
            }
        })
            ->with(['leaveRequests' => function ($q) use ($leaveTypeIds, $request) {
                $q->with(['approver', 'requester', 'leaveBalance']) // Relasi approver dan requester
                    ->whereIn('leave_type_id', $leaveTypeIds)
                    ->orderBy('created_at', 'desc'); // Urutkan berdasarkan tanggal terbaru

                // Filter tambahan (jika ada)
                if ($request->has('bulan_export') && !empty($request->bulan_export)) {
                    $q->whereMonth('created_at', $request->bulan_export);
                }

                if ($request->has('tahun_export') && !empty($request->tahun_export)) {
                    $q->whereYear('created_at', $request->tahun_export);
                }

                if ($request->has('hari_export') && !empty($request->hari_export)) {
                    $q->whereDate('created_at', $request->hari_export);
                }
            }]);

        // Paginate data
        $data = $query->get();

        $bulan = $request->bulan_export;
        $tahun = $request->tahun_export;
        $hari = $request->hari_export;

        if (!empty($bulan) && !empty($tahun)) {
            $tgl_transaksi = $bulan . ' ' . $tahun;
        } else {
            $tgl_transaksi = isset($hari) ? $hari : 'Tanggal tidak tersedia';
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Tambahkan header di baris pertama
        $sheet->setCellValue('A1', 'Filter By: ');
        $sheet->setCellValue('B1', $tgl_transaksi);

        $labels = ['Nomor', 'NPP', 'Nama Karyawan', 'Tanggal Awal', 'Tanggal Akhir', 'Total Cuti', 'Sisa Cuti', 'Status'];

        $startColumn = 'A';
        foreach ($labels as $index => $label) {
            $cell = $startColumn . '3';
            $sheet->setCellValue($cell, $label);

            // Menambahkan styling untuk header
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('1E90FF'); // Warna hijau
            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF'); // Warna putih
            $startColumn++;
        }

        // Mendapatkan indeks baris awal dari data
        $startRow = 4;

        foreach ($data as $index => $d) {
            $startColumn = 'A';

            // Menentukan indeks baris untuk setiap data
            $currentRow = $startRow + $index;

            $requesterFirstName = isset($d->leave_requests[0]->requester) ? $d->leave_requests[0]->requester->first_name : '';
            $requesterId = isset($d->leave_requests[0]->requester) ? $d->leave_requests[0]->requester->id : '';

            $years =  date('Y');

            $leaveBalance = null;
            if ($requesterId) {
                $leaveBalance = LeaveBalances::where('user_id', $requesterId)->where('years', $years)->first();
            }

            // Menentukan nilai untuk saldo cuti
            $leaveBalanceAmount = $leaveBalance ? $leaveBalance->balance : 0;

            $sheet->setCellValue('A' . $currentRow, $d->no_ticket);
            $sheet->setCellValue('B' . $currentRow, $d->npp);
            $sheet->setCellValue('C' . $currentRow, $requesterFirstName);
            $sheet->setCellValue('D' . $currentRow, $d->start_date);
            $sheet->setCellValue('E' . $currentRow, $d->end_date);
            $sheet->setCellValue('F' . $currentRow, $d->total_days);
            $sheet->setCellValue('G' . $currentRow, $leaveBalanceAmount);
            $sheet->setCellValue('H' . $currentRow, $d->status);

            for ($i = 0; $i < count($labels); $i++) {
                $cell = chr(ord('A') + $i) . $currentRow;
                $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'laporan-pengajuan-cuti&izin.xlsx';
        $filePath = storage_path('app/public/' . $fileName);

        // Simpan file di storage
        $writer->save($filePath);

        // Kembalikan respon download
        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }
}
