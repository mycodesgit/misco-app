<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrailDailyTask extends Model
{
    use HasFactory;

    protected $table = 'audit_traildailytask';

    protected $fillable = [
        'user_id',
        'off_id',
        'email',
        'action',
        'actiondata',
        'ip_address',
        'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
