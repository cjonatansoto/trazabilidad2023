<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountryRestriction extends Model
{

    use HasFactory;

    protected $connection = 'bdsystem';

    protected $table = 'pais';

    protected $fillable = [
        'CodPais',
        'codigo',
        'NomPais',
        'CodMer',
        'cod_pais_sernap',
        'inactivo'
    ];

}
