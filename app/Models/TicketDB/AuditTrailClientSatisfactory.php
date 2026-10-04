<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrailClientSatisfactory extends Model
{
    use HasFactory;

    protected $table = 'audit_trailclientsatisfactory';

    protected $fillable = [
        'user_id',
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
