<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'action',
        'comment',
    ];

    // ບອກ Laravel ວ່າເຮົາຈັດການ created_at ເອງໃນ migration
    // ດັ່ງນັ້ນ updated_at ບໍ່ຈຳເປັນ
    public const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
