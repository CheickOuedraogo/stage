<?php

namespace App\Notifications;

use App\Models\Projet;
use Illuminate\Notifications\Notification;

class ProjetClotureNotification extends Notification
{
    public function __construct(public readonly Projet $projet) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'projet_id' => $this->projet->id_projet,
            'titre' => $this->projet->projet_titre,
            'date_cloture' => $this->projet->projet_date_fin_reelle?->toDateString(),
            'message' => "Le projet « {$this->projet->projet_titre} » a été clôturé.",
        ];
    }
}
