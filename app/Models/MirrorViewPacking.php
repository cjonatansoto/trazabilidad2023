<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class MirrorViewPacking extends Model
{

    protected $connection = 'sqlsrv';

    use HasFactory;

    protected $table = 'mirror_view_packing';

    protected $dateFormat = 'Y-d-m H:i:s.v';

    protected $fillable = [
        'neppex_control_id',
        'N_Equipo',
        'cod_lote',
        'N_Pallet',
        'N_Pos',
        'N_IDTurno',
        'N_Turno',
        'N_Lote',
        'Tipo_Proceso',
        'Estado_Lote',
        'N_TEXTO1Lote',
        'N_RestriccionMercado',
        'limite',
        'CodOT',
        'N_OT',
        'N_TEXTO1Ot',
        'N_TEXTO1Especie',
        'N_Especie',
        'N_TEXTO1Corte',
        'N_Corte',
        'N_TEXTO1Conservacion',
        'N_Conservacion',
        'N_TEXTO1Condicion',
        'N_Condicion',
        'Id_Producto',
        'N_CODProducto',
        'Producto',
        'DescProd',
        'N_TEXTO1Producto',
        'N_CODTerminacion',
        'N_Terminacion',
        'N_TEXTO1Envase',
        'N_Envase',
        'Empresa',
        'N_TEXTO1Calidad',
        'N_Calidad',
        'N_TEXTO1Calibre',
        'N_Calibre',
        'N_CODUnidad',
        'N_Unidad',
        'Cliente',
        'Usuario',
        'Caja_Lote',
        'Caja_General',
        'Kg',
        'tara',
        'N_Medida',
        'piezas',
        'Fecha_Frigo',
        'Fecha_Prod',
        'Fecha_Cosecha',
        'Registro_Sistema',
        'N_PesoBruto',
        'N_PNom',
        'N_CODOrigen',
        'N_Origen',
        'N_Proveedor',
        'N_Jaula',
        'N_Etiqueta1',
        'N_Etiqueta2',
        'N_PesoNeto',
        'Fecha_Venc',
        'N_Barra',
        'N_Tara',
        'N_Tara2',
        'N_Contratista',
        'N_Estado',
        'N_MotivoSalida',
        'N_IdEquipo',
        'N_IdEti1',
        'N_IdEti2',
        'N_IdLote',
        'Fecha_Despacho',
        'N_Contratista_Proceso',
        'N_Guia',
        'Piezas_Enteras',
        'N_PesoEtiqueta',
        'N_MMPP',
        'N_BarraMinerva',
        'N_TEXTO1Desp',
        'N_TEXTO2Desp',
        'N_TEXTO3Desp',
        'N_Embarque',
        'N_CertfASC',
        'N_NumCerfASC',
        'N_BapEstrellas',
        'N_Ano',
        'N_Mes',
        'N_PesoNom2',
        'created_at',
        'updated_at',
    ];
}
