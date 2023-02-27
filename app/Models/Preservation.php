<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preservation extends Model
{

    use HasFactory;

    protected $connection = 'bdsystem';

    protected $table = 'conservacion';

    protected $primaryKey = 'cod_cons';

    protected $fillable = [
        'cod_cons',
        'codigo',
        'nombre',
        'desc1_sernap',
        'inactivo',
        'texto1',
        'texto2'
    ];
}
