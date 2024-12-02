<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class keyword_related extends Model
{
    public $timestamps = false;
    protected $table = 'keyword_related';
    protected $fillable = [
        'keyword_id',
        'related_id'
    ];

    public function keyword()
    {
        return $this->belongsTo(keyword::class, 'keyword_id');
    }
}
