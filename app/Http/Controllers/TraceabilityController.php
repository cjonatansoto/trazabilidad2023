<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\DJ;
use App\Models\DJBox;
use App\Models\NeppexControl;
use App\Models\TraceabilityBoxes;
use App\Models\TraceabilityMonitoring;
use App\Models\ViewPacking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class TraceabilityController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:crear-trazabilidad|ver-informe-trazabilidad|eliminar-trazabilidad', ['only' => ['index']]);
        $this->middleware('permission:crear-trazabilidad', ['only' => ['create','store']]);
        $this->middleware('permission:ver-informe-trazabilidad', ['only' => ['show','generateReport']]);
        $this->middleware('permission:eliminar-trazabilidad', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $traceabilitymonitoring = TraceabilityMonitoring::all();
        return view('traceability.index', compact('traceabilitymonitoring'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('traceability.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        session()->forget('errorsUpload');

        $this->validate($request, [
            'lot' => 'required'
        ]);

        $lots = [];

        $errorsUpload = [];

        foreach (explode("\r\n", $request->lot) as $lot) {
            if (is_numeric($lot)) {
                if (ViewPacking::where('N_Lote', $lot)->first()) {
                    array_push($lots, $lot);
                } else {
                    array_push($errorsUpload, [
                        'error' => 'El lote ingresado no existe, lote ingresado: ' . $lot
                    ]);
                }
            } else {
                array_push($errorsUpload, [
                        'error' => 'No se admiten valores alfanumericos, lote ingresado: ' . $lot]
                );
            }
        }

        if($errorsUpload){
            session()->put('errorsUpload', $errorsUpload);
            return redirect()->route('traceability.create');
        }else{
            $boxes = ViewPacking::whereIn('N_lote', $lots)
                ->where('N_MotivoSalida', '!=', 'Caja Desactivada')
                ->where('N_MotivoSalida', '!=', 'Retiquetado')
                ->where('N_MotivoSalida', '!=', 'Por Recepcionar')
                ->where('N_Estado', '=', 'Activa')
                ->select([
                    DB::raw('Caja_General as caja_general'),
                    DB::raw('cod_lote as id_lote'),
                    DB::raw('N_Lote as lote'),
                    DB::raw('N_Especie as especie'),
                    DB::raw('N_Corte as corte'),
                    DB::raw('N_Jaula as jaula'),
                    DB::raw('N_Conservacion as conservacion'),
                    DB::raw('N_PNom as peso_caja'),
                    DB::raw("FORMAT (Fecha_Prod, 'dd/MM/yyyy')  as fecha_produccion"),
                    DB::raw('N_MotivoSalida as motivo_salida'),
                    DB::raw('Id_Producto as producto_Id'),
                ])->get();

            $sumboxes = ViewPacking::whereIn('N_lote', $lots)
                ->where('N_MotivoSalida', '!=', 'Caja Desactivada')
                ->where('N_MotivoSalida', '!=', 'Retiquetado')
                ->where('N_MotivoSalida', '!=', 'Por Recepcionar')
                ->where('N_Estado', '=', 'Activa')
                ->sum('N_PNom');

            $countTotal = count($boxes);
            $countNeppex = 0;
            $kgNeppex = 0;
            $countDj = 0;
            $kgDj = 0;
            $countUnallocated = 0;
            $kgUnallocated = 0;

            $boxesNeppex = [];

            foreach ($boxes as $box) {

                $cagesWithPlaces = DB::select("EXECUTE sp_get_cages_with_places '{$box->id_lote}'");

                $productoSernap = DB::select("EXECUTE sp_get_producto_sernap '{$box->producto_Id}'");

                $codigo_sernap = null;
                $presentacion_sernap = null;
                $jaula = null;
                $centro = null;
                $codigo_centro = null;


                if ($productoSernap) {
                    $codigo_sernap = $productoSernap[0]->codigo;
                    $presentacion_sernap = $productoSernap[0]->nombre;
                }

                if ($cagesWithPlaces) {
                    $jaula = $cagesWithPlaces[0]->jaula;
                    $centro = $cagesWithPlaces[0]->centro;
                    $codigo_centro = $cagesWithPlaces[0]->cod_centro;
                }
                


                if ($boxNeppex = Box::where('overall_box', '=', $box->caja_general)->first()) {

                    $neppex = NeppexControl::join('countries', 'neppex_controls.country_id', '=', 'countries.id')
                        ->join('shipping_ports', 'neppex_controls.shipping_port_id', '=', 'shipping_ports.id')
                        ->join('destination_ports', 'neppex_controls.destination_port_id', '=', 'destination_ports.id')
                        ->join('exporters', 'neppex_controls.exporter_id', '=', 'exporters.id')
                        ->join('border_crossings', 'neppex_controls.border_crossing_id', '=', 'border_crossings.id')
                        ->join('consignees', 'neppex_controls.consignee_id', '=', 'consignees.id')
                        ->join('transports', 'neppex_controls.transport_id', '=', 'transports.id')
                        ->where('neppex_controls.id', $boxNeppex->neppex_control_id)
                        ->select([
                            DB::raw('neppex_controls.id as cod_neppex'),
                            DB::raw('neppex_controls.codaut as numero_neppex'),
                            DB::raw('neppex_controls.authorization_date as fecha_autorizacion'),
                            DB::raw('shipping_ports.name as puerto_embarque'),
                            DB::raw('neppex_controls.container_name as contenedor'),
                            DB::raw('transports.name as medio_de_transporte'),
                            DB::raw("CASE WHEN neppex_controls.issue_certificate = 0 THEN 'Si' Else 'No' END AS emision_de_certificado"),
                            DB::raw('destination_ports.name as puerto_destino'),
                            DB::raw('UPPER(exporters.name) as empresa'),
                            DB::raw('countries.name as pais')
                        ])->first();

                    $countNeppex = $countNeppex + 1;
                    $kgNeppex = $kgNeppex + $box->peso_caja;

                    array_push($boxesNeppex, [
                        'caja_general' => $box->caja_general,
                        'cod_lote' => $box->id_lote,
                        'lote' => $box->lote,
                        'jaula' => $jaula,
                        'centro' => $centro,
                        'codigo_centro' => $codigo_centro,
                        'especie' => $box->especie,
                        'producto' => $box->corte,
                        'producto_id' => $box->producto_Id,
                        'conservacion' => $box->conservacion,
                        'codigo_sernap' => $codigo_sernap,
                        'presentacion_sernap' => $presentacion_sernap,
                        'fecha_elaboracion' => $box->fecha_produccion,
                        'peso_caja' => $box->peso_caja,
                        'mercado_destino' => $neppex->pais,
                        'numero_de_neppex' => $neppex->numero_neppex,
                        'fecha_de_salida' => $neppex->fecha_autorizacion,
                        'destino' => $neppex->pais,
                        'contenedor' => $neppex->contenedor,
                        'ubicacion_producto' => $box->motivo_salida,
                        'neppex_empresa' => $neppex->empresa,
                        'declaracion_jurada_empresa' => null,
                        'declaracion_jurada_destino' => null,
                        'declaracion_jurada_conservacion' => null,
                        'declaracion_jurada_correlativo' => null,
                        'medio_de_transporte' => $neppex->medio_de_transporte,
                        'fecha_de_arribo' => null,
                        'certificado_emitido' => $neppex->emision_de_certificado,
                        'puerto_de_salida' => $neppex->puerto_embarque,
                        'fecha_embarcadas' => $box->fecha_produccion, /** corresponde  a fecha de produccion */
                        'guia_despacho' => null,
                        'neppex_id' => $neppex->cod_neppex,
                        'declaracion_jurada_id' => null,
                        'neppex' => 1,
                        'declaracion_jurada' => 0,
                        'sin_localizacion' => 0
                    ]);

                }else if($dj = DJBox::where('overall_box', '=', $box->caja_general)->first()){

                    $countDj = $countDj + 1;
                    $kgDj = $kgDj + $box->peso_caja;

                    $declaracionJurada = DJ::where('id', $dj->declared_jurisdiction_id)->first();

                    array_push($boxesNeppex, [
                        'caja_general' => $box->caja_general,
                        'cod_lote' => $box->id_lote,
                        'lote' => $box->lote,
                        'jaula' => $jaula,
                        'centro' => $centro,
                        'codigo_centro' => $codigo_centro,
                        'especie' => $box->especie,
                        'producto' => $box->corte,
                        'producto_id' => $box->producto_Id,
                        'conservacion' => $box->conservacion,
                        'codigo_sernap' => $codigo_sernap,
                        'presentacion_sernap' => $presentacion_sernap,
                        'fecha_elaboracion' => $box->fecha_produccion,
                        'peso_caja' => $box->peso_caja,
                        'mercado_destino' => null,//$neppex->pais,
                        'numero_de_neppex' => null,//$neppex->numero_neppex,
                        'fecha_de_salida' => null,//$neppex->fecha_autorizacion,
                        'destino' => null,//$neppex->pais,
                        'contenedor' => null,//$neppex->contenedor,
                        'ubicacion_producto' => $box->motivo_salida,
                        'neppex_empresa' => null,//$neppex->empresa,
                        'declaracion_jurada_empresa' => 'VENDIDO A OTRA EMPRESA - '.strtoupper($declaracionJurada->client),
                        'declaracion_jurada_destino' => strtoupper($declaracionJurada->destination),
                        'declaracion_jurada_conservacion' => strtoupper($declaracionJurada->conservation),
                        'declaracion_jurada_correlativo' => strtoupper($declaracionJurada->correlative),
                        'medio_de_transporte' => null,//$neppex->medio_de_transporte,
                        'fecha_de_arribo' => null,
                        'certificado_emitido' => null,//$neppex->emision_de_certificado,
                        'puerto_de_salida' => null,//$neppex->puerto_embarque,
                        'fecha_embarcadas' => $box->fecha_produccion, /** corresponde  a fecha de produccion */
                        'guia_despacho' =>  $declaracionJurada->dispatch_guide,
                        'neppex_id' => null,//$neppex->cod_neppex,
                        'declaracion_jurada_id' => $dj->declared_jurisdiction_id,
                        'neppex' => 0,
                        'declaracion_jurada' => 1,
                        'sin_localizacion' => 0
                    ]);

                }else {
                    $countUnallocated = $countUnallocated + 1;
                    $kgUnallocated = $kgUnallocated + $box->peso_caja;

                    array_push($boxesNeppex, [
                        'caja_general' => $box->caja_general,
                        'cod_lote' => $box->id_lote,
                        'lote' => $box->lote,
                        'jaula' => $jaula,
                        'centro' => $centro,
                        'codigo_centro' => $codigo_centro,
                        'especie' => $box->especie,
                        'producto' => $box->corte,
                        'producto_id' => $box->producto_Id,
                        'conservacion' => $box->conservacion,
                        'codigo_sernap' => $codigo_sernap,
                        'presentacion_sernap' => $presentacion_sernap,
                        'fecha_elaboracion' => $box->fecha_produccion,
                        'peso_caja' => $box->peso_caja,
                        'mercado_destino' => null,//$neppex->pais,
                        'numero_de_neppex' => null,//$neppex->numero_neppex,
                        'fecha_de_salida' => null,//$neppex->fecha_autorizacion,
                        'destino' => null,//$neppex->pais,
                        'contenedor' => null,//$neppex->contenedor,
                        'ubicacion_producto' => $box->motivo_salida,
                        'neppex_empresa' => null,//$neppex->empresa,
                        'declaracion_jurada_empresa' => null,
                        'declaracion_jurada_destino' => null,
                        'declaracion_jurada_conservacion' => null,
                        'declaracion_jurada_correlativo' => null,
                        'medio_de_transporte' => null,//$neppex->medio_de_transporte,
                        'fecha_de_arribo' => null,
                        'certificado_emitido' => null,//$neppex->emision_de_certificado,
                        'puerto_de_salida' => null,//$neppex->puerto_embarque,
                        'fecha_embarcadas' => $box->fecha_produccion, /** corresponde  a fecha de produccion */
                        'guia_despacho' => null,
                        'neppex_id' => null,//$neppex->cod_neppex,
                        'declaracion_jurada_id' => null,//$dj->declared_jurisdiction_id,
                        'neppex' => 0,
                        'declaracion_jurada' => 0,
                        'sin_localizacion' => 1
                    ]);
                }
            }

            $traceabilityMonitoring = TraceabilityMonitoring::create([
                'loaded_boxes' => $countTotal,
                'kg_loaded' => $sumboxes,
                'lots_declared' => implode('-',$lots),
                'boxes_in_dj' => $countDj,
                'kg_in_dj' => $kgDj,
                'boxes_in_neppex' => $countNeppex,
                'kg_in_neppex' => $kgNeppex,
                'boxes_unallocated' => $countUnallocated,
                'kg_unallocated' => $kgUnallocated,
            ]);



            foreach ($boxesNeppex as $iteration){
                TraceabilityBoxes::create([
                    'traceability_monitoring_id' => $traceabilityMonitoring->id,
                    'caja_general' => $iteration['caja_general'],
                    'cod_lote' => $iteration['cod_lote'],
                    'lote' => $iteration['lote'],
                    'jaula' => $iteration['jaula'],
                    'centro' => $iteration['centro'],
                    'codigo_centro' => $iteration['codigo_centro'],
                    'especie' => $iteration['especie'],
                    'producto' => $iteration['producto'],
                    'producto_id' => $iteration['producto_id'],
                    'conservacion' => $iteration['conservacion'],
                    'codigo_sernap' => $iteration['codigo_sernap'],
                    'presentacion_sernap' => $iteration['presentacion_sernap'],
                    'fecha_elaboracion' => $iteration['fecha_elaboracion'],
                    'peso_caja' => $iteration['peso_caja'],
                    'mercado_destino' => $iteration['mercado_destino'],
                    'numero_de_neppex' => $iteration['numero_de_neppex'],
                    'fecha_de_salida' => $iteration['fecha_de_salida'],
                    'destino' => $iteration['destino'],
                    'contenedor' => $iteration['contenedor'],
                    'ubicacion_producto' => $iteration['ubicacion_producto'],
                    'neppex_empresa' => $iteration['neppex_empresa'],
                    'declaracion_jurada_empresa' => $iteration['declaracion_jurada_empresa'],
                    'declaracion_jurada_destino' => $iteration['declaracion_jurada_destino'],
                    'declaracion_jurada_conservacion' => $iteration['declaracion_jurada_conservacion'],
                    'declaracion_jurada_correlativo' => $iteration['declaracion_jurada_correlativo'],
                    'medio_de_transporte' => $iteration['medio_de_transporte'],
                    'fecha_de_arribo' => $iteration['fecha_de_arribo'],
                    'certificado_emitido' => $iteration['certificado_emitido'],
                    'puerto_de_salida' => $iteration['puerto_de_salida'],
                    'fecha_embarcadas' => $iteration['fecha_embarcadas'],
                    'guia_despacho' => $iteration['guia_despacho'],
                    'neppex_id' => $iteration['neppex_id'],
                    'declaracion_jurada_id' => $iteration['declaracion_jurada_id'],
                    'neppex' => $iteration['neppex'],
                    'declaracion_jurada' => $iteration['declaracion_jurada'],
                    'sin_localizacion' => $iteration['sin_localizacion'],
                ]);
            }

            Alert::success('Exito', 'Registro generado exitosamente');
            return redirect()->route('traceability.index');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if($traceabilityMonitoring = TraceabilityMonitoring::find($id)){

            $boxes_neppex = TraceabilityBoxes::where('traceability_monitoring_id', $traceabilityMonitoring->id)
                ->whereNull('declaracion_jurada_id')
                ->whereNull('guia_despacho')
                ->whereNotNull('neppex_id')
                ->get();

            $boxes_dj = TraceabilityBoxes::where('traceability_monitoring_id', $traceabilityMonitoring->id)
                ->whereNull('neppex_id')
                ->whereNull('guia_despacho')
                ->whereNotNull('declaracion_jurada_id')
                ->get();

            $boxes_unallocated = TraceabilityBoxes::where('traceability_monitoring_id', $traceabilityMonitoring->id)
                ->whereNull('neppex_id')
                ->whereNull('guia_despacho')
                ->whereNull('declaracion_jurada_id')
                ->get();

            return view('traceability.show', [
                'traceabilityMonitoring' => $traceabilityMonitoring,
                'boxes_unallocated' => $boxes_unallocated,
                'boxes_dj' => $boxes_dj,
                'boxes_neppex' => $boxes_neppex
            ]);
        }else{
            Alert::error('Error', 'Registro no existe');
            return redirect()->route('traceability.index');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if($traceabilitymonitoring = TraceabilityMonitoring::find($id)){
            TraceabilityBoxes::where('traceability_monitoring_id', $traceabilitymonitoring->id)->delete();
            $traceabilitymonitoring->delete();
            Alert::success('Exito', 'Registro eliminado correctamente');
            return redirect()->route('traceability.index');
        }else{
            Alert::error('Error', 'Registro trazabilidad no existe!');
            return redirect()->route('traceability.index');
        }
    }

	
	public function generateReport($id){
        if(TraceabilityBoxes::where('traceability_monitoring_id', $id)->get()){



            $item_01 = TraceabilityBoxes::where('traceability_monitoring_id', $id)
                ->select([
                     'lote',
                    'jaula',
                    'centro',
                    'codigo_centro',
                    'especie',
                    'producto',
                    'conservacion',
                    'codigo_sernap',
                    'ubicacion_producto',
                    'presentacion_sernap',
                    'fecha_elaboracion',
                    DB::raw('SUM(peso_caja) as sum_total'),
                    'mercado_destino',
                    'numero_de_neppex',
                    'puerto_de_salida',
                    'declaracion_jurada_id',
                    'guia_despacho'
                ])
                ->groupBy(DB::raw('ubicacion_producto, codigo_sernap, lote, jaula, centro, codigo_centro, especie, producto, conservacion, presentacion_sernap, fecha_elaboracion, mercado_destino, numero_de_neppex, puerto_de_salida, declaracion_jurada_id, guia_despacho'))
                ->get();

		$item_02 = TraceabilityBoxes::where('traceability_monitoring_id', $id)
                ->where('neppex_id', '!=', null)
                ->select([
                    'numero_de_neppex',
                    'fecha_de_salida',
                    'destino',
                    'contenedor',
                    'medio_de_transporte',
                    'fecha_de_arribo',
                    'certificado_emitido',
                    'neppex_empresa',
                    'presentacion_sernap',
                    'fecha_embarcadas',
                    DB::raw('COUNT(id) as bultos'),
                    DB::raw('SUM(peso_caja) as sum_total')
                ])
                ->groupBy(DB::raw('numero_de_neppex, fecha_de_salida, destino, contenedor ,
                medio_de_transporte, fecha_de_arribo,certificado_emitido,presentacion_sernap,  fecha_embarcadas, neppex_empresa '))
                ->get();
            
	    $item_03 = TraceabilityBoxes::where('traceability_monitoring_id', $id)
                ->where('neppex_id', '=', null)
                ->where('declaracion_jurada_id', '=', null)
                ->select([
                    'presentacion_sernap',
                    'fecha_elaboracion',
                    'ubicacion_producto',
                    DB::raw('COUNT(id) as bultos'),
                    DB::raw('SUM(peso_caja) as sum_total'),
                ])
                ->groupBy(DB::raw('presentacion_sernap, fecha_elaboracion , ubicacion_producto'))
                ->get();

$item_04 = TraceabilityBoxes::where('traceability_monitoring_id', $id)
                ->where('declaracion_jurada_id', '!=', null)
                ->select([
                    'presentacion_sernap',
                    'fecha_elaboracion',
                    'declaracion_jurada_empresa',
                    DB::raw('COUNT(id) as bultos'),
                    DB::raw('SUM(peso_caja) as sum_total'),
                ])
                ->groupBy(DB::raw('presentacion_sernap, fecha_elaboracion, declaracion_jurada_empresa'))
                ->get();

            
            header("Pragma: public");
            header("Expires: 0");
            $filename = "GENERAR_TRAZA_BY_".auth()->user()->name.".xls";
            header("Content-type: application/x-msdownload");
            header("Content-Disposition: attachment; filename=$filename");
            header("Pragma: no-cache");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

            $view = view('traceability.excel', [
                'item_01' => $item_01,
                'item_02' => $item_02,
                'item_03' => $item_03,
                'item_04' => $item_04,
            ]);
            echo $view->render();
        }else{
            Alert::error('Error', 'Registro trazabilidad no existe!');
            return redirect()->route('traceability.index');
        }
    }
    
}