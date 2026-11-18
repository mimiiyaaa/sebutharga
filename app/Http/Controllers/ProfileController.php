<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function updateSignature(Request $request)
    {
        $request->validate([
            'signature' => [
                'required',
                'image',
                'mimes:png,jpg,jpeg',
                'max:2048',
            ],
        ]);

        $user = $request->user();

        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
        }

        $path = $request->file('signature')->store('signatures', 'public');

        $user->update([
            'signature_path' => $path,
        ]);

        return back()->with('success', 'Tandatangan berjaya dimuat naik.');
    }
}