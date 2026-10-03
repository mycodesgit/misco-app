<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAssignedTask extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $table = 'users_assigntask';

    protected $fillable = [
        'user_id',
        'taskassigned',
        'aboutassigned',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
