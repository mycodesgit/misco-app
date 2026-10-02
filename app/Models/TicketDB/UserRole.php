<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $table = 'users_role';

    protected $fillable = [
        'rolename',
        'status',
    ];
}
