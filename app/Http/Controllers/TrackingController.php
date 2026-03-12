<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\TrackingHistory;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    public function __construct(private TrackingService $trackingService) {}

    /**
     * Run tracking untuk satu alumni.
     */
    public function runSingle(Alumni $alumni)
    {
        $result = $this->trackingService->runTracking($alumni);

        return redirect()->route('alumni.show', $alumni)
            ->with('success', "Pelacakan selesai. Status: {$result['status_baru']} | Skor: " . round($result['skor'] * 100) . '%');
    }

    /**
     * Run tracking untuk semua alumni yang perlu dilacak.
     */
    public function runBatch()
    {
        $results = $this->trackingService->runBatchTracking();
        $count = count($results);

        return redirect()->route('dashboard')
            ->with('success', "Pelacakan batch selesai. {$count} alumni telah diproses.");
    }

    /**
     * Admin melakukan verifikasi manual.
     */
    public function verify(Request $request, Alumni $alumni)
    {
        $request->validate([
            'status_verifikasi' => 'required|in:Teridentifikasi dari PDDIKTI,Belum Ditemukan',
            'catatan' => 'nullable|string|max:500',
        ]);

        $statusSebelum = $alumni->status;
        $statusBaru = $request->status_verifikasi;

        $alumni->update([
            'status' => $statusBaru,
        ]);

        TrackingHistory::create([
            'alumni_id' => $alumni->id,
            'status_sebelum' => $statusSebelum,
            'status_sesudah' => $statusBaru,
            'skor_kecocokan' => $alumni->skor_kecocokan,
            'query_pencarian' => null,
            'hasil_mentah' => null,
            'catatan' => '[VERIFIKASI MANUAL] ' . ($request->catatan ?? 'Diverifikasi oleh admin.'),
            'diverifikasi_oleh' => Auth::user()->name ?? 'Admin',
        ]);

        return redirect()->route('alumni.show', $alumni)
            ->with('success', "Verifikasi manual selesai. Status diperbarui ke: {$statusBaru}");
    }
}
