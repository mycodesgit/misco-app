<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrailCategorySub extends Model
{
    use HasFactory;

    protected $table = 'audit_trailcategorysub';

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
