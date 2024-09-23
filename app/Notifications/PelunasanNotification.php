<?php

namespace App\Notifications;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification as BaseNotification;

class PelunasanNotification extends BaseNotification
{
    // Data arsip yang akan dikirim dalam notifikasi
    protected $arsip;

    /**
     * Create a new notification instance.
     *
     * @param $arsip
     */
    public function __construct($arsip)
    {
        $this->arsip = $arsip;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database']; // Kirim notifikasi melalui database
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  User  $notifiable
     * @return array
     */
    public function toDatabase(User $notifiable): array
    {
        return Notification::make()
            ->title('Pelunasan berhasil diproses')
            ->body('Status pelunasan diperbarui untuk arsip: ' . $this->arsip->kode . ' - ' . $this->arsip->nama_lengkap)
            ->getDatabaseMessage();
    }
}
