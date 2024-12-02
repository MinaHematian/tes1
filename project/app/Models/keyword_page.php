<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class keyword_page extends Model
{
    public $timestamps = false;
    protected $table = 'keyword_page';
    protected $fillable = [
        'page_id',
        'keyword_id'
    ];
}
