<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TagihanBaruNotification extends Notification
{
    use Queueable;

    protected $tagihan;
    protected $bulan;
    protected $tahun;

    // Kita passing data tagihan ke dalam notifikasi
    public function __construct($tagihan, $bulan, $tahun)
    {
        $this->tagihan = $tagihan;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    // Untuk sekarang kita pakai mode 'database' (In-App)
    // Nanti kalau mau tambah WA, tinggal tambah 'whatsapp' di array ini
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    // Format data yang akan disimpan di tabel notifications
    public function toDatabase(object $notifiable): array
    {
        $namaBulan = \Carbon\Carbon::create()->month($this->bulan)->isoFormat('MMMM');

        return [
            'tipe' => 'tagihan_baru',
            'title' => 'Tagihan Air ' . $namaBulan . ' ' . $this->tahun,
            'message' => 'Tagihan air Anda bulan ini sebesar Rp ' . number_format($this->tagihan->total_tagihan, 0, ',', '.') . ' sudah terbit. Silakan lakukan pembayaran.',
            'url' => route('pelanggan.dashboard'), // Link saat notifikasi di-klik
            'tagihan_id' => $this->tagihan->id
        ];
    }
}
