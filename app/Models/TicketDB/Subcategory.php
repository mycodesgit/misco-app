<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\TicketDB\User;
use App\Models\TicketDB\Category;

class Subcategory extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $table = 'subcategories';

    protected $fillable = [
        'user_id',
        'cat_id',
        'ticketsubcatname',
        'status',
    ];
    /**
     * Cast attributes to native types.
     */
    protected $casts = [
        'status' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id');
    }
}
