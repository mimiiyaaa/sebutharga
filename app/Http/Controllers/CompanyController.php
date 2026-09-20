<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function index()
    {
        return view('pages.syarikat', [
            'title' => 'Syarikat',
            'companies' => DB::table('companies')->orderBy('nama_syarikat')->get(),
        ]);
    }

    public function form(?int $id = null)
    {
        $company = $id ? DB::table('companies')->where('company_id', $id)->firstOrFail() : null;

        return view('pages.companies.form', compact('company'));
    }

    public function save(Request $request, ?int $id = null)
    {
        $data = $request->validate([
            'nama_syarikat' => 'required|max:255',
            'no_telefon' => 'nullable|max:50',
            'emel' => 'nullable|email|max:255',
            'person_in_charge' => 'nullable|max:255',
            'alamat_syarikat' => 'nullable|max:10000',
        ]);

        $duplicate = DB::table('companies')
            ->where('nama_syarikat', $data['nama_syarikat'])
            ->when($id, fn ($query) => $query->where('company_id', '<>', $id))
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['nama_syarikat' => 'Nama syarikat telah didaftarkan.'])->withInput();
        }

        $data['updated_at'] = now();

        if ($id) {
            DB::table('companies')->where('company_id', $id)->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('companies')->insert($data);
        }

        return redirect()->route('syarikat');
    }

    public function show(int $id)
    {
        $company = DB::table('companies')->where('company_id', $id)->firstOrFail();

        return view('pages.companies.show', compact('company'));
    }

    public function delete(int $id)
    {
        DB::table('companies')->where('company_id', $id)->delete();

        return redirect()->route('syarikat');
    }
}
