<?php

namespace App\Models\CoasDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueMode extends Model
{
    use HasFactory;

    protected $connection = 'settings';
    protected $table = 'queuemode';

    protected $fillable = [
        'statusqueue',
    ];
}
