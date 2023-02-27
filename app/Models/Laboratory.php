<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laboratory extends Model
{

    use HasFactory;

    protected $connection = 'sqlsrv';

    protected $dateFormat ='Y-d-m H:i:s.v';

    protected $table = 'laboratories';

    protected $fillable = [
        'name',
        'rut',
	'inactive'
    ];

}
