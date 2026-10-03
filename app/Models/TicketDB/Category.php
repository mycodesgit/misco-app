<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $table = 'categories';

    protected $fillable = [
        'user_id',
        'off_id',
        'ticketcatname',
        'cattype',
        'status',
    ];
    /**
     * Cast attributes to native types.
     */
    protected $casts = [
        'status' => 'integer',
        'cattype' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function subcategories()
    {
        return $this->hasMany(Subcategory::class, 'cat_id');
    }
}
