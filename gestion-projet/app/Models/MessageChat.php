<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id_expediteur', 'id_destinataire', 'message_contenu', 'message_lu'])]
class MessageChat extends Model
{
    protected $table = 'messages_chat';

    protected $primaryKey = 'id_message';

    const CREATED_AT = 'cree_le';

    const UPDATED_AT = 'mis_a_jour_le';

    protected function casts(): array
    {
        return ['message_lu' => 'boolean'];
    }

    public function expediteur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_expediteur');
    }

    public function destinataire(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_destinataire');
    }
}
