<?php

namespace App\Notifications;

use App\Enums\StatutDemande;
use App\Models\DemandeDepense;
use Illuminate\Notifications\Notification;

class StatutDemandeChange extends Notification
{
    public function __construct(
        public readonly DemandeDepense $demande,
        public readonly StatutDemande $newStatus,
        public readonly ?string $motif = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'demande_id' => $this->demande->id_utilisateur,
            'objet' => $this->demande->objet,
            'status' => $this->newStatus->value,
            'status_label' => $this->newStatus->label(),
            'motif' => $this->motif,
            'convention_id' => $this->demande->convention_id,
            'projet_id' => $this->demande->convention->projet_id ?? null,
        ];
    }
}
