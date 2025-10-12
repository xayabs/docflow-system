<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrivateNote extends Model
{
    protected $fillable = [
        'document_id',
        'sender_id',
        'recipient_id',
        'note',
        'is_read',
    ];

    /**
     * Get the user who sent the note.
     */
    public function sender()
    {
        // โน้ตนี้ "เป็นของ" User คนหนึ่ง (ผ่าน sender_id)
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the user who received the note.
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
    
    /**
     * Get the document associated with the note.
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
