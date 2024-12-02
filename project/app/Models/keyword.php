<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class keyword extends Model
{
    protected $table = 'keywords';
    protected $fillable = [
        'keyword',
        'slug',
        'category_id',
        'value',
        'search_value',
        'degree_difficulty'
    ];

    public function keyword_related()
    {
        return $this->belongsTo(keyword_related::class, 'id');
    }
}
