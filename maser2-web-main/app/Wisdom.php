<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wisdom extends Model
{
    protected $table = 'wisdom';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'texto',
        'fonte'
    ];
}
