<?php

namespace App\Console\Commands;

use App\Models\Arsip;
use App\Models\User; // Import User model
use App\Notifications\PelunasanNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification as FacadesNotification;

class Pelunasan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pelunasan:cek';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek tanggal selesai jika kurang dari atau sama dengan hari ini maka status pelunasan diproses.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Ambil tanggal hari ini
        $today = Carbon::today();

        // Query untuk mengambil data yang tanggal selesai-nya kurang dari atau sama dengan hari ini
        $items = Arsip::whereDate('tanggal_selesai', '<=', $today)
            ->where('status', '!=', '1') // Tambahkan filter untuk yang belum diproses
            ->get();

        if ($items->isEmpty()) {
            $this->info('Tidak ada arsip yang perlu diproses.');
            return;
        }

        // Ambil semua pengguna
        $users = User::all();

        // Ubah status arsip yang ditemukan
        foreach ($items as $item) {
            $item->status = '1'; // Ubah '1' ke status yang lebih deskriptif jika ada
            $item->save();

            // Kirim notifikasi ke semua pengguna
            FacadesNotification::sendNow($users, new PelunasanNotification($item));
        }

        $this->info('Status pelunasan diperbarui dan notifikasi dikirim ke semua pengguna.');
    }
}
