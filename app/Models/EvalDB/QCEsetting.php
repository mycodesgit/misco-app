<?php

namespace App\Models\EvalDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QCEsetting extends Model
{
    use HasFactory;

    protected $connection = 'eval';
    protected $table = 'qcesetting';

    protected $fillable = [
        'statuseval',
    ];
}
