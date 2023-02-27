<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restriction extends Model
{

    use HasFactory;

    protected $connection = 'sqlsrv';

    protected $dateFormat ='Y-d-m H:i:s.v';

    protected $table = 'restrictions';

    protected $fillable = [
        'name',
        'code',
'inactive'
    ];

}
