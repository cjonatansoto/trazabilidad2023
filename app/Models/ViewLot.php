<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ViewLot extends Model
{
    protected $connection = 'sqlsrv';

    use HasFactory;

    protected $table = 'v_lot';

    public function scopeYear($query, $variable)
    {
        if (!is_null($variable)) {
            $query->where('N_Ano', $variable)->get();
        }else{
            $query->where('N_Ano', 2022)->get();
        }
        return $query;
    }

    public function scopeMonth($query, $variable)
    {
        if (!is_null($variable)) {
            if($variable !== "ALL"){
                $query->where('N_Mes', $variable)->get();
            }
        }
        return $query;
    }

    public function scopeEnterprise($query, $variable)
    {
        if (!is_null($variable)) {
            if($variable !== "ALL"){
                $query->where('N_Empresa', $variable)->get();
            }
        }
        return $query;
    }

    public function scopeProvider($query, $variable)
    {
        if (!is_null($variable)) {
            if($variable !== "ALL"){
                $query->where('N_Proveedor', $variable)->get();
            }
        }
        return $query;
    }

    public function scopeStatus($query, $variable)
    {
        if (!is_null($variable)) {
            $query->where('inactivo', $variable)->get();
        }
        return $query;
    }


    public function scopeAssignment($query, $variable)
    {
        if (!is_null($variable)) {
            if($variable == 1){
                $query->where('N_Guia2', '=', null)->get();
            }
        }
        return $query;
    }

    public function scopeLotNumber($query, $variable)
    {
        if (!is_null($variable)) {
            $query->where('N_Lote', 'LIKE', "%{$variable}%")->get();
        }
        return $query;
    }

    /**
    public function getPaginatedLots(array $filters = [], int $start = 0, int $limit = 10, string $orderField = 'id', string $orderDirection = 'DESC'): array
    {

        $query = ViewLot::when(array_key_exists('filter_year', $filters), function ($query) use ($filters) {
            $query->where('v_registro_lotes.N_Ano', $filters['filter_year']);
        })
            ->when(array_key_exists('filter_month', $filters), function ($query) use ($filters) {
                $query->where('v_registro_lotes.N_Mes', $filters['filter_month']);
            })
            ->when(array_key_exists('filter_provider', $filters), function ($query) use ($filters) {
                $query->where('v_registro_lotes.N_Proveedor', $filters['filter_provider']);
            })
            ->when(array_key_exists('filter_enterprise', $filters), function ($query) use ($filters) {
                $query->where('v_registro_lotes.N_Empresa', $filters['filter_enterprise']);
            })
            ->when(array_key_exists('filter_lot', $filters), function ($query) use ($filters) {
                $query->where('v_registro_lotes.N_Lote', $filters['filter_lot']);
            })
            ->when(array_key_exists('filter_status', $filters), function ($query) use ($filters) {
                $query->where('v_registro_lotes.inactivo', $filters['filter_status']);
            })
            ->select([
                'v_registro_lotes.IdLote',
                'v_registro_lotes.N_Lote',
                'v_registro_lotes.N_FechaElaboracion',
                'v_registro_lotes.N_TipoProceso',
                'v_registro_lotes.N_Proveedor',
                'v_registro_lotes.N_GuiaDespacho',
                'v_registro_lotes.N_Cajas',
                'v_registro_lotes.N_Piezas',
                'v_registro_lotes.N_KgProceso',
            ]);
        $count = $query->count();
        $res = $query->orderBy($orderField, $orderDirection)
            ->when($limit > 0, function ($query) use ($start, $limit) {
                $query->offset($start)
                    ->limit($limit);
            })
            ->get();
        return [
            'filtered' => $count,
            'rows' => $res,
            'total' => $count
        ];
    }
     **/
}
