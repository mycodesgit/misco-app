<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrailUserAssignedTask extends Model
{
    use HasFactory;

    protected $table = 'audit_trailusers_assigntask';

    protected $fillable = [
        'user_id',
        'email',
        'action',
        'activity',
        'actiondata',
        'ip_address',
        'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
