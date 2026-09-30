<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_login_logs';

    protected $fillable = [
        'user_id',
        'email',
        'action',
        'ip_address',
        'user_agent',
        'login_at',
        'logout_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
