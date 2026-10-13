<?php

namespace App\Models\CoasDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueCustomer extends Model
{
    use HasFactory;

    protected $connection = 'settings';
    protected $table = 'customers';

    protected $fillable = [
        'queue_number',
        'catname',
        'status',
        'campus',
        'counter_id'
    ];

    public function counter()
    {
        return $this->belongsTo(QueueCounter::class, 'counter_id');
    }
}
