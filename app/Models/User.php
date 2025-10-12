<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute; 

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_id',        
        'department_id',  
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the role associated with the user.
     * ຄວາມສຳພັນ: User ໜຶ່ງຄົນ ເປັນຂອງ Role ດຽວ (Belongs To)
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the department associated with the user.
     * ຄວາມສຳພັນ: User ໜຶ່ງຄົນ ເປັນຂອງ Department ດຽວ (Belongs To)
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /*
     * Get the user's display name in Lao.
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                // สร้าง Array สำหรับแปลชื่อ
                $nameTranslations = [
                    'Admin User' => 'ຜູ້ບໍລິຫານລະບົບ',
                    'Staff Maths' => 'ພາກວິຊາຄະນິດສາດ',
                    'Staff Physics' => 'ພາກວິຊາຟິຊິກສາດ',
                    'Staff Chemistry' => 'ພາກວິຊາເຄມີສາດ',
                    'Staff Biology' => 'ພາກວິຊາຊີວະວິທະຍາ',
                    'Staff Computer Science' => 'ພາກວິຊາວິທະຍາສາດຄອມພິວເຕີ',
                    'Staff Management' => 'ພະແນກຈັດຕັ້ງ-ສັງລວມ',
                    'Staff Academic' => 'ພະແນກວິຊາການ',
                    'Staff Finance' => 'ພະແນກແຜນການການເງິນ',
                    'Staff Student Affair' => 'ພະແນກຄຸ້ມຄອງນັກສຶກສາ',
                    'Staff Post Graduate' => 'ພະແນກຫຼັງປະລີນຍາຕີ',
                    'Staff Research/Acedemic Service' => 'ພະແນກຄົ້ນຄວ້າ ແລະ ບໍລິການວິຊາການ',
                    'Dean' => 'ຄະນະບໍດີ',
                    'Vice Dean' => 'ຮອງຄະນະບໍດີ',
                    'Head of Finance' => 'ຫົວໜ້າພະແນກການເງິນ',
                    'Financial Preparer' => 'ຝ່າຍກະກຽມເອກະສານ',
                    'Cashier Staff' => 'ຄັງເງິນສົດ',
                    'Accountant Staff' => 'ນາຍບັນຊີ',
                    'Procurement Staff' => 'ຝ່າຍຈັດຊື້/ສ້ອມແປງ',
                    'Dean Secretary' => 'ເລຂາຄະນະ',
                ];

                // ตรวจสอบว่ามีคำแปลหรือไม่
                // ถ้ามี, ให้คืนค่าคำแปล. ถ้าไม่มี, ให้คืนค่าชื่อเดิม.
                return $nameTranslations[$attributes['name']] ?? $attributes['name'];
            }
        );
    }
}
