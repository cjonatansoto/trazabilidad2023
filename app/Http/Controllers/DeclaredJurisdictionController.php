<?php

namespace App\Http\Controllers;


use App\Models\Box;
use App\Models\Property;
use App\Models\ViewDJDetails;
use App\Models\ViewDJMain;
use PDF;
use App\Models\DJ;
use App\Models\DJBox;
use App\Models\Preservation;
use App\Models\ViewDJ;
use App\Models\ViewPacking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\DeclaredJurisdiction\CreateRequest;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Round;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\LotDispatchGuide;

class DeclaredJurisdictionController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:generar-correlativo-declaraciones-juradas|generar-pdf-declaraciones-juradas|crear-declaraciones-juradas|editar-declaraciones-juradas|eliminar-declaraciones-juradas', ['only' => ['index']]);
        $this->middleware('permission:crear-declaraciones-juradas', ['only' => ['create','store']]);
        $this->middleware('permission:editar-declaraciones-juradas', ['only' => ['edit','update']]);
        $this->middleware('permission:eliminar-declaraciones-juradas', ['only' => ['destroy']]);
        $this->middleware('permission:generar-pdf-declaraciones-juradas', ['only' => ['visualizePdf','generatePDF']]);
        $this->middleware('permission:generar-correlativo-declaraciones-juradas', ['only' => ['generateCorrelative']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $djs = DJ::all();

        foreach ($djs as $dj){
            $dj->kg = DJBox::where('declared_jurisdiction_id', $dj->id)->sum('kg');
            $dj->box = DJBox::where('declared_jurisdiction_id', $dj->id)->count('overall_box');
        }

        return view('declaredjurisdictions.index', compact('djs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $preservations = Preservation::where('inactivo', 0)->get();
        return view('declaredjurisdictions.create', compact('preservations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateRequest $request)
    {
        if($request) {
            $dj = DJ::create([
                'dispatch_guide' => $request->dispatch_guide,
                'date_guide' => $request->date_guide,
                'client' => $request->client,
                'destination' => $request->destination,
                'conservation' => $request->conservation,
                'correlative' => $request->correlative,
                'observations' => $request->observations,
            ]);

            if($request->boxes){



                $boxes = [];

		foreach (explode("\r\n", $request->boxes) as $box) {

                    if (is_numeric($box)){

                        if(!Box::where("overall_box", $box)->first()){

                            if (!DJBox::where("overall_box", $box)->first()) {

                                $overallBox = ViewPacking::where('Caja_General', (integer)$box)->first();

                                if ($overallBox) {

					
                                    $lotAvailable = LotDispatchGuide::where('lot_id', $overallBox->cod_lote)->count();

                                    if($lotAvailable !== 0){
						
					
                                        if ($overallBox->N_MotivoSalida == "Despacho a Cliente" || $overallBox->N_MotivoSalida == "Reproceso") {
						


                                            $productSernap = DB::select("select bdsystem.dbo.producto_sernap.nombre as name from bdsystem.dbo.producto
                                                        inner join bdsystem.dbo.producto_sernap ON bdsystem.dbo.producto.cod_prodsernap = bdsystem.dbo.producto_sernap.cod_prodsernap
                                                        where cod_prod = {$overallBox->Id_Producto}");


                                            if($productSernap){

                                                $nombSernap = $productSernap[0]->name;

                                                array_push($boxes, [
                                                    'overallBox' => $box,
                                                    'productId' => $overallBox->Id_Producto,
                                                    'productName' => $nombSernap,
                                                    'kg' => $overallBox->N_PNom,
                                                    'lotId' => $overallBox->N_IdLote,
                                                    'lotNumber' => $overallBox->N_Lote,
                                                    'dateOfElaboration' => date('Y-d-m', strtotime($overallBox->Fecha_Prod)),
                                                    'dateOfExpiration' => date('Y-d-m', strtotime($overallBox->Fecha_Venc)),
                                                    'message' => 'OK',
                                                    'status' => true
                                                ]);
                                            }else{
                                                array_push($boxes, [
                                                    'overallBox' => $box,
                                                    'message' => 'La caja ingresada no se encuentra con producto Sernap ID Producto: '.$overallBox->Id_Producto,
                                                    'status' => false
                                                ]);
                                            }
                                        } else {
 						
				           array_push($boxes, [
                                            'overallBox' => $box,
                                            'message' => "La caja ingresada no se encuentra con despacho a cliente o reproceso, operación cancelada",
                                            'status' => false
                                        ]);
					
					

                                        }
                                    }else{
                                        array_push($boxes, [
                                            'overallBox' => $box,
                                            'message' => 'La caja ingresada no se encuentra con su lote asignado a guia numero de lote: '.$overallBox->N_Lote,
                                            'status' => false
                                        ]);
                                    }
                                } else {
                                    array_push($boxes, [
                                        'overallBox' => $overallBox,
                                        'message' => 'La caja ingresada no existe, operación cancelada',
                                        'status' => false
                                    ]);
                                }
                            } else {
                                array_push($boxes, [
                                    'overallBox' => $box,
                                    'message' => 'La caja ingresada ya existe en el sistema, operación cancelada',
                                    'status' => false
                                ]);
                            }
                        }else{
                            array_push($boxes, [
                                'overallBox' => $box,
                                'message' => 'La caja ingresada ya se encuentra en neppex ingresada',
                                'status' => false
                            ]);
                        }
                    }else{
                        array_push($boxes, [
                            'overallBox' => $box,
                            'message' => 'La caja ingresada no aplica formato numerico',
                            'status' => false
                        ]);
                    }


                }          
		
                $validateDJ = 0;

                foreach ($boxes as $box) {
                    if ($box['status'] !== true) {
                        $validateDJ = $validateDJ + 1;
                    }
                }

                if ($validateDJ == 0) {

                    if($boxes){
                        foreach ($boxes as $row) {
                            DJBox::create([
                                'overall_box' => (integer)$row['overallBox'],
                                'product_id' => $row['productId'],
                                'product_name' => $row['productName'],
                                'kg'  => $row['kg'],
                                'lot_id'  => $row['lotId'],
                                'lot_number'  => $row['lotNumber'],
                                'date_of_elaboration'  => $row['dateOfElaboration'],
                                'date_of_expiration'  => $row['dateOfExpiration'],
                                'declared_jurisdiction_id' => $dj->id
                            ]);
                        }
                        Alert::success('Exito','Registro creado');
                        return redirect()->route('declaredjurisdictions.index');
                    }

                } else {
                    session()->forget('boxes');
                    session()->put('boxes', $boxes);
                    return redirect()->route('declaredjurisdictions.errors');
                }

            }

            Alert::success('Exito', 'Registro creado');
            return redirect()->route('declaredjurisdictions.index');
        }
    }


    public function errors(Request $request){
        if($boxes = session()->get('boxes')) {
            Alert::error('Error', 'Revisa motivos.');
            return view('declaredjurisdictions.error', compact('boxes'));
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(DJ $declaredjurisdiction)
    {
        $preservations = Preservation::where('inactivo', 0)->get();
        $uploadedBoxes = DJBox::where('declared_jurisdiction_id', $declaredjurisdiction->id)->count();
        $uploadedKG = DJBox::where('declared_jurisdiction_id', $declaredjurisdiction->id)->sum('kg');
        $detailBoxes = DJBox::where('declared_jurisdiction_id', $declaredjurisdiction->id)->get();
        return view('declaredjurisdictions.edit', compact('preservations', 'declaredjurisdiction', 'uploadedBoxes', 'uploadedKG', 'detailBoxes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DJ $declaredjurisdiction)
    {
        if($request){

            if($request->boxes){

                $boxes = [];

                DJBox::where('declared_jurisdiction_id', $declaredjurisdiction->id)->delete();

                foreach (explode("\r\n", $request->boxes) as $box) {
                    if (is_numeric($box)){
                        if(!Box::where("overall_box", $box)->first()){
                            if (!DJBox::where("overall_box", $box)->first()) {

                                $overallBox = ViewPacking::where('Caja_General', (integer)$box)->first();

                                if ($overallBox) {
                                    $lotAvailable = LotDispatchGuide::where('lot_id', $overallBox->cod_lote)->count();

                                    if($lotAvailable !== 0){
                                        if ($overallBox->N_MotivoSalida === "Despacho a Cliente" || $overallBox->N_MotivoSalida == "Reproceso") {

                                            $productSernap = DB::select("select bdsystem.dbo.producto_sernap.nombre as name from bdsystem.dbo.producto
                                                        inner join bdsystem.dbo.producto_sernap ON bdsystem.dbo.producto.cod_prodsernap = bdsystem.dbo.producto_sernap.cod_prodsernap
                                                        where cod_prod = {$overallBox->Id_Producto}");


                                            if($productSernap){

                                                $nombSernap = $productSernap[0]->name;

                                                array_push($boxes, [
                                                    'overallBox' => $box,
                                                    'productId' => $overallBox->Id_Producto,
                                                    'productName' => $nombSernap,
                                                    'kg' => $overallBox->N_PNom,
                                                    'lotId' => $overallBox->N_IdLote,
                                                    'lotNumber' => $overallBox->N_Lote,
                                                    'dateOfElaboration' => date('Y-d-m', strtotime($overallBox->Fecha_Prod)),
                                                    'dateOfExpiration' => date('Y-d-m', strtotime($overallBox->Fecha_Venc)),
                                                    'message' => 'OK',
                                                    'status' => true
                                                ]);
                                            }else{
                                                array_push($boxes, [
                                                    'overallBox' => $box,
                                                    'message' => 'La caja ingresada no se encuentra con producto Sernap ID Producto: '.$overallBox->Id_Producto,
                                                    'status' => false
                                                ]);
                                            }
                                        } else {
                                            array_push($boxes, [
                                                'overallBox' => $box,
                                                'message' => 'La caja ingresada no se encuentra con despacho a cliente, operación cancelada',
                                                'status' => false
                                            ]);
                                        }
                                    }else{
                                        array_push($boxes, [
                                            'overallBox' => $box,
                                            'message' => 'La caja ingresada no se encuentra con su lote asignado a guia numero de lote: '.$overallBox->N_Lote,
                                            'status' => false
                                        ]);
                                    }
                                } else {
                                    array_push($boxes, [
                                        'overallBox' => $overallBox,
                                        'message' => 'La caja ingresada no existe, operación cancelada',
                                        'status' => false
                                    ]);
                                }
                            } else {
                                array_push($boxes, [
                                    'overallBox' => $box,
                                    'message' => 'La caja ingresada ya existe en el sistema, operación cancelada',
                                    'status' => false
                                ]);
                            }
                        }else{
                            array_push($boxes, [
                                'overallBox' => $box,
                                'message' => 'La caja ingresada ya se encuentra en neppex ingresada',
                                'status' => false
                            ]);
                        }
                    }else{
                        array_push($boxes, [
                            'overallBox' => $box,
                            'message' => 'La caja ingresada no aplica formato numerico',
                            'status' => false
                        ]);
                    }


                }

                $validateDJ = 0;

                foreach ($boxes as $box) {
                    if ($box['status'] !== true) {
                        $validateDJ = $validateDJ + 1;
                    }
                }

                if ($validateDJ == 0) {

                    if($boxes){
                        foreach ($boxes as $row) {
                            DJBox::create([
                                'overall_box' => (integer)$row['overallBox'],
                                'product_id' => $row['productId'],
                                'product_name' => $row['productName'],
                                'kg'  => $row['kg'],
                                'lot_id'  => $row['lotId'],
                                'lot_number'  => $row['lotNumber'],
                                'date_of_elaboration'  => $row['dateOfElaboration'],
                                'date_of_expiration'  => $row['dateOfExpiration'],
                                'declared_jurisdiction_id' => $declaredjurisdiction->id
                            ]);
                        }
                        Alert::success('Exito','Registro Actualizado');
                        return redirect()->route('declaredjurisdictions.edit', $declaredjurisdiction->id);
                    }

                } else {
                    session()->forget('boxes');
                    session()->put('boxes', $boxes);
                    return redirect()->route('declaredjurisdictions.errors');
                }

                if($request->observations){
                    $declaredjurisdiction->observations = $request->observations;
                    $declaredjurisdiction->update();
                }

                Alert::success('Exito','Registro Actualizado');
                return redirect()->route('declaredjurisdictions.edit', $declaredjurisdiction->id);

            }


        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DJ $declaredjurisdiction)
    {
        if($declaredjurisdiction){
            DJBox::where('declared_jurisdiction_id',$declaredjurisdiction->id)->delete();
            $declaredjurisdiction->delete();
            Alert::success('Eliminado','DJ eliminado correctamente');
            return redirect()->route('declaredjurisdictions.index');
        }else{
            Alert::error('error','DJ no encontrada!');
            return redirect()->route('declaredjurisdictions.index');
        }
    }

public function visualizePdf($id){

        $dj = DJ::where('id', $id)->first();

        $kg = DJBox::where('declared_jurisdiction_id', $id)->sum('kg');

        $box = ViewDJ::where('N_IdDeclaracion', $id)->distinct('N_CajaGeneral')->count('N_CajaGeneral');

        $species = ViewDJ::where('N_IdDeclaracion', $id)->select(['N_Producto'])->distinct()->get();

        $main = ViewDJMain::where('N_DeclaracionId', $id)->get();

        $properties = Property::find(2);

        $djs = null;

        $resultN = [];

        foreach ($main as $item) {

            $djs =
                [
                    'N_Producto'  => $item->N_Producto,
                    'N_Lote' => "0{$item->N_Lote}",
                    'N_Cajas' => $item->N_Cajas,
                    'N_KG' => round($item->N_KG, 2),
                    'N_FechaProduccion' => $item->N_FechaProduccion,
                    'N_FechaVencimiento' => $item->N_FechaVencimiento,
                    'details' => []
                ];

            $details = ViewDJDetails::where('N_DeclaracionId', $id)->where('lote_Nombre', "0{$item->N_Lote}")->get();


            foreach ($details as $detail) {

                $finalRestriccion = "";

                foreach (explode(',', $detail->N_RestriccionMercado) as $item){
                    $finalRestriccion = $finalRestriccion.$item.'</br>';
                }

                $djs['details'][] = [
                    'N_GuiaRecepcion' => $detail->N_GuiaRecepcion,
                    'N_FechaCosecha' => $detail->N_FechaCosecha,
                    'N_NombreCentro' => $detail->N_NombreCentro,
                    'N_CodigoCentro' => $detail->N_CodigoCentro,
                    'N_Jaula' => $detail->N_Jaula,
                    'N_DeclaracionGarantia' => $detail->N_DeclaracionGarantia,
                    'N_RestriccionMercado' => $finalRestriccion,
                    'N_AntNumero' => $detail->N_AntNumero,
                    'N_AntFecha' => $detail->N_AntFecha,
                    'N_AntLaboratorio' => $detail->N_AntLaboratorio,
                    'N_SusPhNumero' => $detail->N_SusPhNumero,
                    'N_SusPhFecha' => $detail->N_SusPhFecha,
                    'N_SusPhLaboratorio' => $detail->N_SusPhLaboratorio
                ];
            }

            array_push($resultN, $djs);
        }



        if ($resultN) {
            $dj_reports = $resultN;
        }else{
		Alert::error('Error', 'Sin cajas ingresadas');
                return back();

	 }

	foreach ($dj_reports as $report){
            if(!$report['details']){
                Alert::error('Error', 'El lote Sin guias ingresadas: 0'.$report['N_Lote']);
                return back();
            }
        }


        if((count($details) !== 0)){
            return view('pdf.report_dj',
                compact('dj_reports',  'species', 'dj', 'kg', 'box', 'properties'));
        }else{
            Alert::error('Error', 'Sin guias ingresadas');
            return back();
        }


    }

	
    public function generateReportExcel($id)
    {
	
	set_time_limit(300);

        $dj = DJ::where('id', $id)->first();

        $kg = DJBox::where('declared_jurisdiction_id', $id)->sum('kg');

        $box = ViewDJ::where('N_IdDeclaracion', $id)->distinct('N_CajaGeneral')->count('N_CajaGeneral');

        $species = ViewDJ::where('N_IdDeclaracion', $id)->select(['N_Producto'])->distinct()->get();

        $main = ViewDJMain::where('N_DeclaracionId', $id)->get();

        $properties = Property::find(2);

        $djs = null;

        $resultN = [];

        foreach ($main as $item) {

            $djs =
                [
                    'N_Producto'  => $item->N_Producto,
                    'N_Lote' => "0{$item->N_Lote}",
                    'N_Cajas' => $item->N_Cajas,
                    'N_KG' => round($item->N_KG, 2),
                    'N_FechaProduccion' => $item->N_FechaProduccion,
                    'N_FechaVencimiento' => $item->N_FechaVencimiento,
                    'details' => []
                ];

            $details = ViewDJDetails::where('N_DeclaracionId', $id)->where('lote_Nombre', "0{$item->N_Lote}")->get();


            foreach ($details as $detail) {

                $finalRestriccion = "";

                foreach (explode(',', $detail->N_RestriccionMercado) as $item){
                    $finalRestriccion = $finalRestriccion.$item.' - ';
                }

                $djs['details'][] = [
                    'N_GuiaRecepcion' => $detail->N_GuiaRecepcion,
                    'N_FechaCosecha' => $detail->N_FechaCosecha,
                    'N_NombreCentro' => $detail->N_NombreCentro,
                    'N_CodigoCentro' => $detail->N_CodigoCentro,
                    'N_Jaula' => $detail->N_Jaula,
                    'N_DeclaracionGarantia' => $detail->N_DeclaracionGarantia,
                    'N_RestriccionMercado' => $finalRestriccion,
                    'N_AntNumero' => $detail->N_AntNumero,
                    'N_AntFecha' => $detail->N_AntFecha,
                    'N_AntLaboratorio' => $detail->N_AntLaboratorio,
                    'N_SusPhNumero' => $detail->N_SusPhNumero,
                    'N_SusPhFecha' => $detail->N_SusPhFecha,
                    'N_SusPhLaboratorio' => $detail->N_SusPhLaboratorio
                ];
            }

            array_push($resultN, $djs);
        }



        if ($resultN) {
            $dj_reports = $resultN;
        }else{
		Alert::error('Error', 'Sin cajas ingresadas');
                return back();

	 }


	foreach ($dj_reports as $report){
            if(!$report['details']){
                Alert::error('Error', 'El lote Sin guias ingresadas: 0'.$report['N_Lote']);
                return back();
            }
        }


        if((count($details) !== 0)){
		try {
                header("Pragma: public");
                header("Expires: 0");
                $filename = "DECLARACION_JURADA_" . auth()->user()->name . ".xls";
                header("Content-type: application/x-msdownload");
                header("Content-Disposition: attachment; filename=$filename");
                header("Pragma: no-cache");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

                $view = view('pdf.report_dj2', [
                        'dj_reports' => $dj_reports,
                        'species' => $species,
                        'dj' => $dj,
                        'kg' => $kg,
                        'box' => $box,
                        'properties' => $properties]
                );
                echo $view->render();
            }catch (\Exception $exception){
                dd('error'. $exception->getMessage());
            }		
        }else{
            Alert::error('Error', 'Sin guias ingresadas');
            return back();
        }

    }

    public function generateCorrelative($id)
    {
        if ($dj = DJ::where('id', $id)->first()) {

            if($dj->correlative){
                Alert::error('Error', 'Ya posee correlativo');
                return redirect()->route('declaredjurisdictions.edit', $dj->id);
            }

            $djLast = DJ::select([DB::raw("max(correlative) as correlative")])->first();
		
	
		
            if($djLast){
                $number = explode('/', $djLast->correlative);
            }

            $generate = str_pad((int) $number[0] + 1 . '/' . date('Y', strtotime(now())), 11, "0", STR_PAD_LEFT);

            $dj->correlative = $generate;
            $dj->update();

            Alert::success('Exito', 'Correlativo Generado');
            return redirect()->route('declaredjurisdictions.edit', $dj->id);

        } else {
            Alert::success('Exito', 'Error no existe, declaración jurada');
            return redirect()->route('declaredjurisdictions.index');
        }
    }

public function generatePDF($id){

        $dj = DJ::where('id', $id)->first();

        $kg = DJBox::where('declared_jurisdiction_id', $id)->sum('kg');

        $box = ViewDJ::where('N_IdDeclaracion', $id)->distinct('N_CajaGeneral')->count('N_CajaGeneral');

        $species = ViewDJ::where('N_IdDeclaracion', $id)->select(['N_Producto'])->distinct()->get();

        $main = ViewDJMain::where('N_DeclaracionId', $id)->get();

        $properties = Property::find(2);

        $djs = null;

        $resultN = [];

        foreach ($main as $item) {

            $djs =
                [
                    'N_Producto'  => $item->N_Producto,
                    'N_Lote' => "0{$item->N_Lote}",
                    'N_Cajas' => $item->N_Cajas,
                    'N_KG' => round($item->N_KG, 2),
                    'N_FechaProduccion' => $item->N_FechaProduccion,
                    'N_FechaVencimiento' => $item->N_FechaVencimiento,
                    'details' => []
                ];

            $details = ViewDJDetails::where('N_DeclaracionId', $id)->where('lote_Nombre', "0{$item->N_Lote}")->get();


            foreach ($details as $detail) {

                $finalRestriccion = "";

                foreach (explode(',', $detail->N_RestriccionMercado) as $item){
                    $finalRestriccion = $finalRestriccion.$item.'</br>';
                }

                $djs['details'][] = [
                    'N_GuiaRecepcion' => $detail->N_GuiaRecepcion,
                    'N_FechaCosecha' => $detail->N_FechaCosecha,
                    'N_NombreCentro' => $detail->N_NombreCentro,
                    'N_CodigoCentro' => $detail->N_CodigoCentro,
                    'N_Jaula' => $detail->N_Jaula,
                    'N_DeclaracionGarantia' => $detail->N_DeclaracionGarantia,
                    'N_RestriccionMercado' => $finalRestriccion,
                    'N_AntNumero' => $detail->N_AntNumero,
                    'N_AntFecha' => $detail->N_AntFecha,
                    'N_AntLaboratorio' => $detail->N_AntLaboratorio,
                    'N_SusPhNumero' => $detail->N_SusPhNumero,
                    'N_SusPhFecha' => $detail->N_SusPhFecha,
                    'N_SusPhLaboratorio' => $detail->N_SusPhLaboratorio
                ];
            }

            array_push($resultN, $djs);
        }



        if ($resultN) {
            $dj_reports = $resultN;
        }else{
		Alert::error('Error', 'Sin cajas ingresadas');
                return back();

	 }


        foreach ($dj_reports as $report){
            if(!$report['details']){
                Alert::error('Error', 'El lote Sin guias ingresadas: 0'.$report['N_Lote']);
                return back();
            }
        }



        if((count($details) !== 0)){

            return PDF::loadView('pdf.report_dj', [
                'dj_reports' => $dj_reports,
                'species' => $species,
                'dj' => $dj,
                'kg' => $kg,
                'box' => $box,
                'properties' => $properties
            ])->setPaper('a4')
                ->setOption('margin-bottom', 15)
                ->setOption('orientation', "Landscape")
                ->download(utf8_decode('dj_report') . '.pdf');
        }else{
            Alert::error('Error', 'Sin guias ingresadas');
            return back();
        }
        
    }

    }