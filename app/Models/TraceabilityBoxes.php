<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TraceabilityBoxes extends Model
{

    use HasFactory;

    protected $connection = 'sqlsrv';

    protected $dateFormat ='Y-d-m H:i:s.v';

    protected $table = 'traceability_boxes';

    protected $fillable = [
        'traceability_monitoring_id',
        'caja_general',
        'cod_lote',
        'lote',
        'jaula',
        'centro',
        'codigo_centro',
        'especie',
        'producto',
        'producto_id',
        'conservacion',
        'codigo_sernap',
        'presentacion_sernap',
        'fecha_elaboracion',
        'peso_caja',
        'mercado_destino',
        'numero_de_neppex',
        'fecha_de_salida',
        'destino',
        'contenedor',
        'ubicacion_producto',
        'neppex_empresa',
        'declaracion_jurada_empresa',
        'declaracion_jurada_destino',
        'declaracion_jurada_conservacion',
        'declaracion_jurada_correlativo',
        'medio_de_transporte',
        'fecha_de_arribo',
        'certificado_emitido',
        'puerto_de_salida',
        'fecha_embarcadas',
        'guia_despacho',
        'neppex_id',
        'declaracion_jurada_id',
        'neppex',
        'declaracion_jurada',
        'sin_localizacion'
    ];

}