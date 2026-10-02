<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrailUserRole extends Model
{
    use HasFactory;

    protected $table = 'audit_trailusersrole';

    protected $fillable = [
        'user_id',
        'email',
        'action',
        'actiondata',
        'ip_address',
        'user_agent',
    ];
}
