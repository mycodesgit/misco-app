<?php

namespace App\Models\CoasDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\AdmissionDB\User;

class QueueCounter extends Model
{
    use HasFactory;

    protected $connection = 'settings';
    protected $table = 'counters';

    protected $fillable = [
        'windowname',
        'category',
        'useridlog',
        'activeidnumber',
        'currentid',
        'callid',
        'campus',
        'counterstatus'
    ];

    public function customers()
    {
        return $this->hasMany(QueueCustomer::class, 'counter_id');
    }
}
