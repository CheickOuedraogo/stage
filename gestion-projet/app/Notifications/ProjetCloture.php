<?php

namespace App\Notifications;

use App\Models\Projet;
use Illuminate\Notifications\Notification;

class ProjetCloture extends Notification
{
    public function __construct(public readonly Projet $projet) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'projet_id' => $this->projet->id,
            'titre' => $this->projet->titre,
            'date_cloture' => $this->projet->date_fin_reelle?->toDateString(),
            'message' => "Le projet « {$this->projet->titre} » a été clôturé.",
        ];
    }
}
