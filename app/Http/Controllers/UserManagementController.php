<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = DB::table('users')->orderBy('name')->get(['id', 'name', 'no_kp', 'email', 'created_at']);

        return view('pages.maklumat-login.index', compact('users'));
    }

    public function form(?int $id = null)
    {
        $user = $id ? DB::table('users')->where('id', $id)->firstOrFail() : null;

        return view('pages.maklumat-login.form', compact('user'));
    }

    public function save(Request $request, ?int $id = null)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'no_kp' => ['required', 'string', 'max:255', Rule::unique('users', 'no_kp')->ignore($id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password' => [$id ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);

        $record = [
            'name' => $data['name'],
            'no_kp' => $data['no_kp'],
            'email' => $data['email'],
            'updated_at' => now(),
        ];
        if (!empty($data['password'])) $record['password'] = Hash::make($data['password']);

        if ($id) {
            DB::table('users')->where('id', $id)->update($record);
        } else {
            $record['created_at'] = now();
            DB::table('users')->insert($record);
        }

        return redirect()->route('maklumat-login')->with('success', 'Maklumat pengguna berjaya disimpan.');
    }

    public function delete(Request $request, int $id)
    {
        abort_if((int) $request->user()->id === $id, 403, 'Akaun yang sedang digunakan tidak boleh dipadam.');
        DB::table('users')->where('id', $id)->delete();

        return redirect()->route('maklumat-login')->with('success', 'Pengguna berjaya dipadam.');
    }
}
