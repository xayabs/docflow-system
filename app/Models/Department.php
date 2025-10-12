<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    //use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Departments table does not have created_at and updated_at columns.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the users associated with the role.
     * ຄວາມສໍາພັນ: Role ໜຶ່ງອັນ ມີໄດ້ຫຼາຍ User (Has Many)
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
