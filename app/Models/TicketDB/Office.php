<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $table = 'offices';

    protected $fillable = [
        'office_name',
        'office_abbr',
        'remember_token',
    ];
}
