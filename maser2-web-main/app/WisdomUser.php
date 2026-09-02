<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WisdomUser extends Model
{
    protected $table = 'wisdom_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'wisdom_id'
    ];
}
