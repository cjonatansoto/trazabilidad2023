<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class Enterprise extends Model
{

    use HasFactory;

    protected $connection = 'bdsystem';

    protected $table = 'empresas';

    protected $primaryKey = 'cod_empresa';

    protected $fillable = [
        'cod_empresa',
        'descripcion',
        'descripcion2',
        'Direccion',
        'NRegistro',
        'inactivo',
    ];
}
