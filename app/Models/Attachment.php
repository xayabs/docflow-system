<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_id',
        'file_name',
        'file_path',
    ];
    
    /**
     * Indicates if the model should be timestamped.
     * ບອກ Laravel ວ່າຕາຕະລາງນີ້ບໍ່ມີ created_at, updated_at
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the document that the attachment belongs to.
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
