<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DivisiController extends Controller
{
    public function index()
    {
        if (Auth::user()->hasRole('HRD')) {
            return view('admin.divisi.index');
        } else {
            return redirect()->route('login');
        }
    }

    public function getDataJSON(Request $request)
    {
        if (Auth::user()->hasRole('HRD')) {
            $query = Divisi::query();

            // Filter berdasarkan pencarian (jika ada)
            if ($request->has('search') && !empty($request->search)) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Paginate data
            $data = $query->paginate(5); // Ganti 5 dengan jumlah data per halaman

            return response()->json($data);
        } else {
            return redirect()->route('login');
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->hasRole('HRD')) {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $data = Divisi::create([
                'name' => $validatedData['name'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil Menambahkan Divisi',
            ]);
        } else {
            return redirect()->route('login');
        }
    }

    public function edit(Request $request, $id)
    {
        if (Auth::user()->hasRole('HRD')) {
            $divisi = Divisi::findOrFail($id);
            return response()->json($divisi);
        } else {
            return redirect()->route('login');
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->hasRole('HRD')) {
            $validated = $request->validate([
                'name' => 'required|string|max:255'
            ]);

            $divisi = Divisi::findOrFail($id);
            $divisi->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui'
            ]);
        } else {
            return redirect()->route('login');
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->hasRole('HRD')) {
            $divisi = Divisi::findOrFail($id);
            $divisi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } else {
            return redirect()->route('login');
        }
    }
}
