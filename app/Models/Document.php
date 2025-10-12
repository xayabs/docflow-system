<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // ເພີ່ມ HasFactory
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory; // เพิ่ม HasFactory

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_code',
        'title',
        'status',
        'status_before_rejected',
        'total_amount',
        'requester_id',
        'department_id',
        'document_type_id',
        'parent_document_id',
        'rejected_reason',
        'references',
        'activity_description',
    ];

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }  

    public function documentItems() 
    { 
        return $this->hasMany(DocumentItem::class); 
    }

    public function attachments() 
    { 
        return $this->hasMany(Attachment::class); 
    } 

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function documentLogs()
    {
        return $this->hasMany(DocumentLog::class);
    }
}