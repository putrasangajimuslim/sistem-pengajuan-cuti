<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequests;
use App\Models\LeaveRequestTickets;
use App\Models\LeaveTypes;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IzinController extends Controller
{
    public function index()
    {
        return view('pengajuan.izin.index');
    }

    public function getDataJSON(Request $request)
    {
        $userLoginId = Auth::user()->id;

        $leaveTypeIds = LeaveTypes::whereIn('name', ['Sakit', 'Izin Urgent'])->pluck('id');

        // Membuat query untuk LeaveRequestTickets dengan filter leave_type_id
        $query = LeaveRequestTickets::whereHas('leaveRequests', function ($q) use ($userLoginId, $leaveTypeIds) {
            $q->whereIn('leave_type_id', $leaveTypeIds) // Menambahkan kondisi untuk leave_type_id
                ->where(function ($q) use ($userLoginId) {
                    $q->where('approver_id', $userLoginId) // Kondisi approver_id
                        ->orWhere('user_id', $userLoginId); // Kondisi user_id
                });
        })
            ->with(['leaveRequests' => function ($q) use ($userLoginId, $leaveTypeIds) {
                $q->with(['approver', 'requester']) // Relasi approver dan requester
                    ->whereIn('leave_type_id', $leaveTypeIds) // Filter berdasarkan leave_type_id
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

        // Menambahkan status approval dan informasi approver pada collection
        $data->getCollection()->transform(function ($item) {
            // Mengakses relasi leaveRequestTicket alih-alih leaveRequests
            $item->approval_status = $item->leaveRequestTicket->approval_status ?? 'Permohonan cuti disetujui oleh atasan';
            return $item;
        });

        // Menambahkan validasi apakah user saat ini adalah approver
        $data->getCollection()->transform(function ($item) use ($userLoginId) {
            $item->is_approver = $item->leaveRequests->contains(function ($leaveRequest) use ($userLoginId) {
                return $leaveRequest->approver_id == $userLoginId;
            });

            return $item;
        });

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_pengajuan_izin' => 'required',
            'keterangan' => 'required|string|max:255',
            'jenis_izin' => 'required',
            'file_upload' => 'nullable|file|mimes:jpg,png,pdf,doc,docx|max:2048',
        ]);

        if ($request->hasFile('file_upload')) {
            $file = $request->file('file_upload');
            $filePath = $file->store('uploads', 'public');
        }

        $total_pengajuan_izin = $validatedData['total_pengajuan_izin'];
        $keterangan = $validatedData['keterangan'];
        $jenis_izin = $validatedData['jenis_izin'];
        $start_date = $validatedData['start_date'];
        $end_date = $validatedData['end_date'];

        $userLoginId = Auth::user()->id;
        $roleUserLoginId = Auth::user()->role;
        $divisiUserLoginId = Auth::user()->divisi_id;
        $nppUserLoginId = Auth::user()->npp;

        $hierarchy = [
            'HRD' => ['Manager'],
            'Manager' => ['HRD'],
            'Supervisor' => ['Manager'],
            'Staff' => ['Supervisor'],
        ];

        $approverRoles = $hierarchy[$roleUserLoginId] ?? [];

        $approvers = User::where('divisi_id', $divisiUserLoginId)
            ->whereIn('role', $approverRoles)
            ->orderByRaw("FIELD(role, '" . implode("','", $approverRoles) . "')") // Maintain the order in the hierarchy
            ->get();

        $leaveTypeId = LeaveTypes::where('name', $jenis_izin)->value('id');

        $latestLeaves = LeaveRequestTickets::latest('id')->first();
        $newRequestLiveId = $latestLeaves ? $latestLeaves->id + 1 : 1;

        $ticketLeaves = 'IS' . str_pad($newRequestLiveId, 7, '0', STR_PAD_LEFT);

        // Gunakan transaksi untuk memastikan konsistensi data
        DB::beginTransaction();

        try {

            $newLeaveTickets = new LeaveRequestTickets();
            $newLeaveTickets->no_ticket = $ticketLeaves;
            $newLeaveTickets->npp = $nppUserLoginId;
            $newLeaveTickets->start_date = $start_date;
            $newLeaveTickets->end_date = $end_date;
            $newLeaveTickets->years = date('Y');
            $newLeaveTickets->total_days = $total_pengajuan_izin;
            $newLeaveTickets->reason = $keterangan;
            $newLeaveTickets->media_url = $filePath;
            $newLeaveTickets->status = 'PENDING';
            $newLeaveTickets->save();

            // Simpan pengajuan cuti untuk setiap approver
            foreach ($approvers as $approver) {
                $newLeaveRequest = new LeaveRequests();
                $newLeaveRequest->user_id = $userLoginId;
                $newLeaveRequest->leave_type_id = $leaveTypeId;
                $newLeaveRequest->reason = $request->keterangan;
                $newLeaveRequest->leave_request_ticket_id = $newLeaveTickets->id;
                $newLeaveRequest->status = 'PENDING';
                $newLeaveRequest->approver_id = $approver->id;
                $newLeaveRequest->save();
            }

            // Commit transaksi
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil Mengajukan Izin'
            ]);
        } catch (\Exception $e) {
            // Rollback jika ada kesalahan
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengajukan izin. Silakan coba lagi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDataLeaveTicketJSON(Request $request, $id)
    {
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
                        'message' => "Karyawan telah mengajukan permohonan izin."
                    ];
                    $addedRoles[] = 'Pengajuan';
                }

                // Tambahkan langkah "Diproses oleh ..." untuk approver dengan status PENDING
                if ($approver && $leaveRequest->status === 'PENDING' && !in_array($approver->role, $addedRoles)) {
                    $stepDetails[] = [
                        'step' => "Diproses oleh {$approver->role}",
                        'approver' => $approver->role,
                        'status' => $leaveRequest->status,
                        'message' => "{$approver->role} sedang meninjau permohonan izin"
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
                    'message' => "Permohonan izin telah disetujui oleh: " . implode(', ', $approvedIntersection)
                ];
            }

            if (!empty($rejectedIntersection)) {
                $stepDetails[] = [
                    'step' => "Ditolak",
                    'approver' => implode(', ', $rejectedIntersection),
                    'status' => 'REJECTED',
                    'message' => "Permohonan izin telah ditolak oleh: " . implode(', ', $rejectedIntersection)
                ];
            }
        } else {
            $stepDetails[] = [
                'step' => 'Tidak ada permohonan izin yang ditemukan',
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

        return response()->json([
            'success' => true,
            'message' => 'Berhasil merubah status'
        ]);
    }
}
