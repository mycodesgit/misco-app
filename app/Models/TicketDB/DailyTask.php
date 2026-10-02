<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyTask extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $table = 'dailytask';

    protected $fillable = [
        'user_id',
        'cat_id',
        'subcat_id',
        'dailytaskdesc',
        'type',
        'started_at',
        'completed_at',
        'status',
    ];

    /**
     * Cast attributes to native Carbon date instances.
     */
    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id');
    }

    // FIX: A daily task belongs to ONE subcategory
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class, 'subcat_id');
    }
}
