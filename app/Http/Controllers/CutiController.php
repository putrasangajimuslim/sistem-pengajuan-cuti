<?php

namespace App\Http\Controllers;

use App\Models\LeaveBalances;
use App\Models\LeaveRequests;
use App\Models\LeaveRequestTickets;
use App\Models\LeaveTypes;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CutiController extends Controller
{
    public function index()
    {
        $leaves = LeaveTypes::whereNotIn('name', ['Sakit', 'Izin Urgent'])->get();
        return view('pengajuan.cuti.index', ['leaves' => $leaves]);
    }

    public function getDataJSON(Request $request)
    {
        $userLoginId = Auth::user()->id;

        $leaveTypeIds = LeaveTypes::whereNotIn('name', ['Sakit', 'Izin Urgent'])->pluck('id')->toArray();

        $query = LeaveRequestTickets::whereHas('leaveRequests', function ($q) use ($userLoginId, $leaveTypeIds) {
            // $q->where('status', 'PENDING') // Hanya data dengan status PENDING
            $q->whereIn('leave_type_id', $leaveTypeIds)
                ->where(function ($q) use ($userLoginId) {
                    $q->where('approver_id', $userLoginId) // Kondisi approver_id
                        ->orWhere('user_id', $userLoginId); // Kondisi user_id
                });
        })
            ->with(['leaveRequests' => function ($q) use ($userLoginId, $leaveTypeIds) {
                $q->with(['approver', 'requester']) // Relasi approver dan requester
                    ->whereIn('leave_type_id', $leaveTypeIds)
                    ->where('status', 'PENDING') // Hanya data dengan status PENDING
                    ->where(function ($q) use ($userLoginId) {
                        $q->where('approver_id', $userLoginId) // Kondisi approver_id
                            ->orWhere('user_id', $userLoginId); // Kondisi user_id
                    })
                    ->orderBy('created_at', 'desc'); // Urutkan berdasarkan tanggal terbaru
            }]);

        // Filter berdasarkan pencarian (jika ada)
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('start_date', 'like', '%' . $search . '%')
                    ->orWhere('npp', 'like', '%' . $search . '%')
                    ->orWhere('no_ticket', 'like', '%' . $search . '%')
                    ->orWhereHas('leaveRequests.requester', function ($q) use ($search) {
                        $q->where('first_name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Paginate data
        $data = $query->paginate(5);

        $data->getCollection()->transform(function ($item) {
            // Mengakses relasi leaveRequestTicket alih-alih leaveRequests
            $item->approval_status = $item->leaveRequestTicket->approval_status ?? 'Permohonan cuti disetujui oleh atasan';
            return $item;
        });

        // $data->getCollection()->transform(function ($item) {
        //     $item->approval_status = $item->leaveRequests->first()?->approval_status ?? 'Permohonan cuti disetujui oleh atasan';
        //     return $item;
        // });

        $data->getCollection()->transform(function ($item) use ($userLoginId) {
            // Tambahkan validasi apakah user saat ini adalah approver
            $item->is_approver = $item->leaveRequests->contains(function ($leaveRequest) use ($userLoginId) {
                return $leaveRequest->approver_id == $userLoginId;
            });

            return $item;
        });

        return response()->json($data);
    }

    public function getDataProgramCutiJSON(Request $request)
    {
        $data = LeaveTypes::where('id', $request->cuti_id)->first();

        $idUserLogin = Auth::user()->id;

        $leaveBalanceUser = LeaveBalances::where('user_id', $idUserLogin)->where('leave_type_id', $request->cuti_id)->first();

        $leaveUser = [
            'id' => $data->id,
            'max_days' => $data->max_days,
            'name' => $data->name,
            'sisa_cuti' => $leaveBalanceUser ? $leaveBalanceUser->balance : $data->max_days, // Jika tidak ada saldo, default ke 0
            'idUserLogin' => $idUserLogin
        ];

        return response()->json($leaveUser);
    }

    public function getDataLeaveTicketJSON(Request $request, $id)
    {
        $userLoginId = Auth::user()->id;
        $roleUserLoginId = Auth::user()->role;
        $divisiUserLoginId = Auth::user()->divisi_id;
        $nppUserLoginId = Auth::user()->npp;

        $leaveRequestTicket = LeaveRequestTickets::where('id', $id)->with(['leaveRequests' => function ($q) {
            $q->with(['approver', 'requester', 'leaveTypes'])
                // ->where('status', 'PENDING') // Hanya data dengan status PENDING
                ->orderBy('created_at', 'desc'); // Jika ada lebih dari satu, pilih yang terbaru
        }]);

        $data = $leaveRequestTicket->first();

        $stepDetails = [];
        $addedRoles = [];
        $approvedRoles = []; // Inisialisasi variabel di awal
        $rejectedRoles = []; // Inisialisasi variabel di awal

        if ($data && $data->leaveRequests->isNotEmpty()) {
            foreach ($data->leaveRequests as $leaveRequest) {
                $approver = $leaveRequest->approver;

                // Tambahkan langkah "Pengajuan" jika belum ada
                if (!in_array('Pengajuan', $addedRoles)) {
                    $stepDetails[] = [
                        'step' => "Pengajuan",
                        'approver' => $approver ? $approver->role : 'Tidak ada approver yang ditentukan',
                        'status' => $leaveRequest->status,
                        'message' => "Karyawan telah mengajukan permohonan cuti."
                    ];
                    $addedRoles[] = 'Pengajuan';
                }

                // Tambahkan langkah "Diproses oleh ..." untuk approver dengan status PENDING
                if ($approver && $leaveRequest->status === 'PENDING' && !in_array($approver->role, $addedRoles)) {
                    $stepDetails[] = [
                        'step' => "Diproses oleh {$approver->role}",
                        'approver' => $approver->role,
                        'status' => $leaveRequest->status,
                        'message' => "{$approver->role} sedang meninjau permohonan cuti"
                    ];
                    $addedRoles[] = $approver->role;
                }

                // Cek apakah status sudah APPROVED
                if ($leaveRequest->status === 'APPROVED' && $approver) {
                    $approvedRoles[] = $approver->role; // Tandai role ini sebagai sudah menyetujui
                } else if ($leaveRequest->status === 'REJECTED' && $approver) {
                    $rejectedRoles[] = $approver->role;
                }
            }

            // Tambahkan langkah jika salah satu role yang relevan telah menyetujui
            $relevantRoles = ['Manager', 'Supervisor', 'HRD'];
            $approvedIntersection = array_intersect($approvedRoles, $relevantRoles);
            $rejectedIntersection = array_intersect($rejectedRoles, $relevantRoles);

            if (!empty($approvedIntersection)) {
                $stepDetails[] = [
                    'step' => "Disetujui",
                    'approver' => implode(', ', $approvedIntersection),
                    'status' => 'APPROVED',
                    'message' => "Permohonan cuti telah disetujui oleh: " . implode(', ', $approvedIntersection)
                ];
            }

            if (!empty($rejectedIntersection)) {
                $stepDetails[] = [
                    'step' => "Ditolak",
                    'approver' => implode(', ', $rejectedIntersection),
                    'status' => 'REJECTED',
                    'message' => "Permohonan cuti telah ditolak oleh: " . implode(', ', $rejectedIntersection)
                ];
            }
        } else {
            $stepDetails[] = [
                'step' => 'Tidak ada permohonan cuti yang ditemukan',
                'approver' => null,
                'status' => 'Tidak ada',
                'message' => 'Tidak ada data yang sesuai'
            ];
        }

        return response()->json([
            'data' => $data,
            'request_steps' => $stepDetails,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type_cuti' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_pengajuan_cuti' => 'required|integer|min:1',
            'keterangan' => 'required|string|max:255',
            'sisa_cuti' => 'required|integer|min:0',
        ]);

        $userLoginId = Auth::user()->id;
        $roleUserLoginId = Auth::user()->role;
        $divisiUserLoginId = Auth::user()->divisi_id;
        $nppUserLoginId = Auth::user()->npp;

        $hierarchy = [
            'HRD' => ['Manager'],
            'Manager' => ['HRD'],
            'Supervisor' => ['Manager', 'HRD'],
            'Staff' => ['Manager', 'Supervisor'],
        ];

        // Ambil approvals berdasarkan divisi dan role
        $approvals = User::where('divisi_id', $divisiUserLoginId);

        $approverRoles = $hierarchy[$roleUserLoginId] ?? [];

        $approvers = User::where('divisi_id', $divisiUserLoginId)
            ->whereIn('role', $approverRoles)
            ->orderByRaw("FIELD(role, '" . implode("','", $approverRoles) . "')") // Maintain the order in the hierarchy
            ->get();

        $latestLeaves = LeaveRequestTickets::latest('id')->first();
        $newRequestLiveId = $latestLeaves ? $latestLeaves->id + 1 : 1;

        $ticketLeaves = 'CT' . str_pad($newRequestLiveId, 7, '0', STR_PAD_LEFT);

        // Gunakan transaksi untuk memastikan konsistensi data
        DB::beginTransaction();

        try {

            $newLeaveTickets = new LeaveRequestTickets();
            $newLeaveTickets->no_ticket = $ticketLeaves;
            $newLeaveTickets->npp = $nppUserLoginId;
            $newLeaveTickets->start_date = $request->start_date;
            $newLeaveTickets->end_date = $request->end_date;
            $newLeaveTickets->years = date('Y');
            $newLeaveTickets->total_days = $request->total_pengajuan_cuti;
            $newLeaveTickets->reason = $request->keterangan;
            $newLeaveTickets->status = 'PENDING';
            $newLeaveTickets->save();

            // Simpan pengajuan cuti untuk setiap approver
            foreach ($approvers as $approver) {
                $newLeaveRequest = new LeaveRequests();
                $newLeaveRequest->user_id = $userLoginId;
                $newLeaveRequest->leave_type_id = $request->type_cuti;
                $newLeaveRequest->reason = $request->keterangan;
                $newLeaveRequest->leave_request_ticket_id = $newLeaveTickets->id;
                $newLeaveRequest->status = 'PENDING';
                $newLeaveRequest->approver_id = $approver->id;
                $newLeaveRequest->save();
            }

            $years = date('Y');
            $checkBalanceUser = LeaveBalances::where('user_id', $userLoginId)
                ->where('years', $years)
                ->first();

            // Update saldo cuti pengguna
            if (empty($checkBalanceUser)) {
                $newLeaveBalance = new LeaveBalances();
                $newLeaveBalance->user_id = $userLoginId;
                $newLeaveBalance->leave_type_id = $request->type_cuti;
                $newLeaveBalance->years = $years;
                $newLeaveBalance->balance = $request->sisa_cuti;
                $newLeaveBalance->save();
            }

            // Commit transaksi
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil Mengajukan Cuti'
            ]);
        } catch (\Exception $e) {
            // Rollback jika ada kesalahan
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengajukan cuti. Silakan coba lagi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatusLeave(Request $request)
    {

        $userLoginId = Auth::user()->id;
        $leaveRequestTicketId = $request->data_id;
        $status_approval = $request->status_approval;
        $alasan = $request->alasan;

        $checkLeaveRequestByRole = LeaveRequests::where('approver_id', $userLoginId)
            ->where('leave_request_ticket_id', $leaveRequestTicketId)
            ->first();

        $checkLeaveRequestByRole->status = $status_approval;

        if (!empty($alasan)) {
            $checkLeaveRequestByRole->reason = $alasan;
        }

        $checkLeaveRequestByRole->save();

        // Periksa semua status approval untuk tiket ini
        $allApprovals = LeaveRequests::where('leave_request_ticket_id', $leaveRequestTicketId)->get();

        // Cek apakah ada yang REJECTED
        $isRejected = $allApprovals->contains('status', 'REJECTED');

        // Cek apakah semua APPROVED
        $allApproved = $allApprovals->every(fn($approval) => $approval->status === 'APPROVED');

        // Update tabel LeaveRequestsTicket berdasarkan kondisi
        $leaveRequestTicket = LeaveRequestTickets::where('id', $leaveRequestTicketId)->first();
        $leaveRequest = LeaveRequests::where('leave_request_ticket_id', $leaveRequestTicketId)->first();

        if ($leaveRequestTicket) {
            if ($isRejected) {
                $leaveRequestTicket->status = 'REJECTED';
            } elseif ($allApproved) {
                $leaveRequestTicket->status = 'APPROVED';

                $userId = $leaveRequest->user_id;
                $leaveTypeId = $leaveRequest->leave_type_id;
                $requestDate = $leaveRequest->created_at->toDateString();
                $totalDays = $leaveRequestTicket->total_days;

                $leaveBalance = LeaveBalances::where('user_id', $userId)
                    ->where('leave_type_id', $leaveTypeId)
                    ->whereDate('created_at', $requestDate) // Pastikan tanggal cocok
                    ->first();

                if ($leaveBalance) {
                    // Kurangi max_days dengan total_days
                    $leaveBalance->balance -= $totalDays;

                    // Pastikan tidak negatif
                    if ($leaveBalance->balance < 0) {
                        $leaveBalance->balance = 0;
                    }

                    $leaveBalance->save();
                }
            } else {
                $leaveRequestTicket->status = 'PENDING';
            }
            $leaveRequestTicket->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil merubah status'
        ]);
    }
}
