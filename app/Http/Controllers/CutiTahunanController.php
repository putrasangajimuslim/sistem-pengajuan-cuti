<?php

namespace App\Http\Controllers;

use App\Models\ProgramCuti;
use Illuminate\Http\Request;

class CutiTahunanController extends Controller
{
    public function index()
    {
        return view('admin.cutitahunan.index');
    }

    public function getDataJSON(Request $request)
    {
        $query = ProgramCuti::query();

        // Filter berdasarkan pencarian (jika ada)
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('years', 'like', '%' . $request->search . '%');
        }

        // Paginate data
        $data = $query->paginate(5); // Ganti 5 dengan jumlah data per halaman

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'max_days' => 'required',
            'years' => 'required|digits:4|integer',
        ]);

        $data = ProgramCuti::create([
            'name' => $validatedData['name'],
            'max_days' => $validatedData['max_days'],
            'years' => $validatedData['years'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil Menambahkan Program Cuti',
        ]);
    }

    public function edit(Request $request, $id)
    {
        $divisi = ProgramCuti::findOrFail($id);
        return response()->json($divisi);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'max_days' => 'required',
            'years' => 'required|digits:4|integer',
        ]);

        $divisi = ProgramCuti::findOrFail($id);
        $divisi->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui'
        ]);
    }

    public function destroy($id)
    {
        $divisi = ProgramCuti::findOrFail($id);
        $divisi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus'
        ]);
    }
}
