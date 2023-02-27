<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class Species extends Model
{

    use HasFactory;

    protected $connection = 'bdsystem';

    protected $table = 'especies';

    protected $primaryKey = 'cod_especie';

    protected $fillable = [
        'cod_especie',
        'codigo',
        'descripcion',
        'cien_name',
        'texto1',
        'inactivo'
    ];
}
