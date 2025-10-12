<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    //use HasFactory; // ເພີ່ມນີ້

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Roles table does not have created_at and updated_at columns.
     *
     * @var bool
     */
    public $timestamps = false; // ບອກ Laravel ວ່າຕາຕະລາງນີ້ບໍ່ມີ created_at, updated_at

    /**
     * Get the users associated with the role.
     * ຄວາມສໍາພັນ: Role ໜຶ່ງອັນ ມີໄດ້ຫຼາຍ User (Has Many)
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
