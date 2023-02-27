<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Court extends Model
{

    use HasFactory;

    protected $connection = 'bdsystem';

    protected $table = 'corte';

    protected $primaryKey = 'cod_corte';

    protected $fillable = [
        'cod_corte',
        'codigo',
        'nombre',
        'desc1_sernap',
        'desc2_sernap',
        'transito',
        'texto1',
        'inactivo'
    ];
}
