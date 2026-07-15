<?php

namespace App\Http\Controllers\Pencatat;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\PencatatanMeter;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $currentMonth = date('n');
        $currentYear = date('Y');

        $totalPelanggan = Pelanggan::where('status', 'aktif')
            ->where(function ($query) {
                $query->doesntHave('user')
                    ->orWhereHas('user', function ($q) {
                        $q->where('is_active', true);
                    });
            })->count();

        $recordedCount = PencatatanMeter::where('bulan_periode', $currentMonth)
            ->where('tahun_periode', $currentYear)
            ->count();

        $pendingCount = max(0, $totalPelanggan - $recordedCount);

        return view('pencatat.dashboard', compact('totalPelanggan', 'recordedCount', 'pendingCount'));
    }
}
