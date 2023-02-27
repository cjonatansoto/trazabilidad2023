<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Market extends Model
{

    use HasFactory;

    protected $connection = 'bdsystem';

    protected $table = 'mercado';

    protected $primaryKey = 'CodMer';

    protected $fillable = [
        'CodMer',
        'Codigo',
        'NomMercado',
        'inactivo'
    ];

}
