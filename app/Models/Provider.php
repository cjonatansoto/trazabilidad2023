<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class Provider extends Model
{

    use HasFactory;

    protected $connection = 'bdsystem';

    protected $table = 'proveedores';

    protected $primaryKey = 'cod_proveedor';

    protected $fillable = [
        'cod_proveedor',
        'descripcion',
        'codigo_ggn',
        'inactivo'
    ];
}
