<?php

namespace App\Http\Controllers\Ortu;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    /**
     * Tandai satu notifikasi sebagai dibaca dan redirect ke URL-nya.
     */
    public function read(Notifikasi $notifikasi): RedirectResponse
    {
        if ($notifikasi->user_id !== Auth::id()) {
            abort(403);
        }

        $notifikasi->markAsRead();

        return redirect($notifikasi->url ?? route('ortu.dashboard'));
    }

    /**
     * Tandai semua notifikasi user sebagai dibaca.
     */
    public function readAll(Request $request): RedirectResponse
    {
        Notifikasi::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->back();
    }
}
