<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id_expediteur', 'id_destinataire', 'message_contenu', 'message_lu'])]
class ChatMessage extends Model
{
    protected $primaryKey = 'id_message';

    protected function casts(): array
    {
        return ['message_lu' => 'boolean'];
    }

    /** Transparent id accessor so $chatMessage->id still works */
    public function getIdAttribute(): mixed
    {
        return $this->getAttribute($this->getKeyName());
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_expediteur');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_destinataire');
    }
}
