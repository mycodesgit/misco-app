<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WifiVoucher extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $table = 'wifi_vouchers';

    protected $fillable = [
        'voucher_code',
        'schlyear',
        'semester',
        'status',
        'uploaded_by',
    ];

    /**
     * Cast attributes to native types.
     */
    protected $casts = [
        'status' => 'integer',
    ];

    public function isAvailable(): bool
    {
        return $this->status === 1;
    }
}
