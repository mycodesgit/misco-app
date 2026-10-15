<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudVoucherReg extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $table = 'studvoucherreg';

    protected $fillable = [
        'vc_id',
        'stud_id',
        'fullname',
        'role',
        'schlyear',
        'semester',
    ];

    protected $attributes = [
        'role' => 'Student',
    ];

    public function voucher()
    {
        return $this->belongsTo(WifiVoucher::class, 'vc_id');
    }
}
