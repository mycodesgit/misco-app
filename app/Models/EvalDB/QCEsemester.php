<?php

namespace App\Models\EvalDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QCEsemester extends Model
{
    use HasFactory;

    protected $connection = 'eval';
    protected $table = 'qceschlyearsem';

    protected $fillable = [
        'qceschlyear', 
        'qcesemester',
        'qceratingfrom',
        'qceratingto',
        'qcesemstat',
        'postedBy',
    ];
}
