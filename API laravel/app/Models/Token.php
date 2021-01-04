<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;
    protected $table='token' ;
    protected $fillable = [
        'token',
        'user_id'
    ];
    public static function where($string, $get)
    {

    }

}
