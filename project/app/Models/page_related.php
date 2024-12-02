<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class page_related extends Model
{
    public $timestamps = false;
    protected $table = 'page_related';
    protected $fillable = [
        'page_id',
        'related_id',
        'keyword_id'
    ];
}
