<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lot\CreateLotRequest;
use App\Models\DispatchGuide;
use App\Models\Enterprise;
use App\Models\Lot;
use App\Models\LotDispatchGuide;
use App\Models\MeasurementUnit;
use App\Models\Provider;
use App\Models\QuantityType;
use App\Models\StockLot;
use App\Models\ViewBins;
use App\Models\ViewLot;
use App\Models\ViewPacking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\DataTables;

class LotController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:asignar-guia-lotes', ['only' => ['index']]);
        $this->middleware('permission:asignar-guia-lotes', ['only' => ['create','store']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $years = [
            date("Y", strtotime(date("Y") . "- 2 year")),
            date("Y", strtotime(date("Y") . "- 1 year")),
            date("Y", strtotime(date("Y"))),
        ];

        $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        $enterprises = Enterprise::all();

        $providers = Provider::all();

        return view('lots.index', compact('providers', 'enterprises', 'years','months'));    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        if($lot = ViewLot::where('IdLote', $id)->first()){

            $dispatchguides = DispatchGuide::join('bdsystem.dbo.empresas', 'dispatch_guides.enterprise_id', '=', 'bdsystem.dbo.empresas.cod_empresa')
                              ->join('bdsystem.dbo.proveedores', 'dispatch_guides.provider_id', '=', 'bdsystem.dbo.proveedores.cod_proveedor')
                              ->where('bdsystem.dbo.empresas.descripcion', $lot->N_Empresa)
                              ->where('bdsystem.dbo.proveedores.descripcion', $lot->N_Proveedor)->get();

            $quantitytypes = QuantityType::all();

            $measurementunits = MeasurementUnit::all();

            $boxesResume = StockLot::join('quantity_types', 'stock_lots.quantity_type_id', '=', 'quantity_types.id')
                ->join('lot_dispatch_guides', 'stock_lots.lot_dispatch_guide_id', '=', 'lot_dispatch_guides.id')
                ->join('users', 'lot_dispatch_guides.loaded_by', '=', 'users.id')
                ->join('dispatch_guides', 'lot_dispatch_guides.dispatch_guide_id', '=', 'dispatch_guides.id')
                ->select([
                    DB::raw('lot_dispatch_guides.id as lot_dispatch_guide_id'),
                    DB::raw('quantity_types.name as quantity_type_name'),
                    DB::raw('COUNT(stock_lots.items) as count_item'),
                    DB::raw('SUM(stock_lots.kg_amount) as kg_total'),
                    DB::raw('dispatch_guides.number as dispatch_guides_number')
                ])
                ->groupBy('quantity_types.name')
                ->groupBy('dispatch_guides.number')
                ->groupBy('lot_dispatch_guides.id')
                ->where('lot_dispatch_guides.lot_id', $id)->get();


            $resumeBox = StockLot::join('quantity_types', 'stock_lots.quantity_type_id', '=', 'quantity_types.id')
                ->join('lot_dispatch_guides', 'stock_lots.lot_dispatch_guide_id', '=', 'lot_dispatch_guides.id')
                ->join('users', 'lot_dispatch_guides.loaded_by', '=', 'users.id')
                ->join('dispatch_guides', 'lot_dispatch_guides.dispatch_guide_id', '=', 'dispatch_guides.id')
                ->select([
                    DB::raw('COUNT(stock_lots.items) as count_item'),
                    DB::raw('SUM(stock_lots.kg_amount) as kg_total'),
                ])
                ->where('lot_dispatch_guides.lot_id', $id)->where('quantity_types.id', 1)->first();

            $resumeBin = StockLot::join('quantity_types', 'stock_lots.quantity_type_id', '=', 'quantity_types.id')
                ->join('lot_dispatch_guides', 'stock_lots.lot_dispatch_guide_id', '=', 'lot_dispatch_guides.id')
                ->join('users', 'lot_dispatch_guides.loaded_by', '=', 'users.id')
                ->join('dispatch_guides', 'lot_dispatch_guides.dispatch_guide_id', '=', 'dispatch_guides.id')
                ->select([
                    DB::raw('COUNT(stock_lots.items) as count_item'),
                    DB::raw('SUM(stock_lots.kg_amount) as kg_total'),
                ])
                ->where('lot_dispatch_guides.lot_id', $id)->where('quantity_types.id', 2)->first();


            $boxes = StockLot::join('quantity_types', 'stock_lots.quantity_type_id', '=', 'quantity_types.id')
                ->join('lot_dispatch_guides', 'stock_lots.lot_dispatch_guide_id', '=', 'lot_dispatch_guides.id')
                ->join('users', 'lot_dispatch_guides.loaded_by', '=', 'users.id')
                ->join('dispatch_guides', 'lot_dispatch_guides.dispatch_guide_id', '=', 'dispatch_guides.id')
                ->select([
                    DB::raw('stock_lots.id as id'),
                    DB::raw('quantity_types.name as quantity_type_name'),
                    DB::raw('stock_lots.items as item'),
                    DB::raw('dispatch_guides.number as dispatch_guides_number'),
                    DB::raw('stock_lots.kg_amount as kg_amount'),
                    DB::raw('users.name as username'),
                    DB::raw('lot_dispatch_guides.updated_at as date')
                ])->where('lot_dispatch_guides.lot_id', $id)->get();

            return view('lots.create', compact('lot', 'dispatchguides', 'quantitytypes', 'measurementunits', 'boxes', 'boxesResume', 'resumeBin', 'resumeBox'));
        }else{
            return redirect(route('lots.index'));
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateLotRequest $request)
    {
        if ($request) {

            if ($request->quantity_type_id == 1) {

                $boxes = [];

                foreach (explode("\r\n", $request->items) as $item) {

                    if (!StockLot::where("items", $item)->where('quantity_type_id', 1)->first()) {

                        if ($overallBox = ViewPacking::where('Caja_General', (integer) $item)->first()) {

                            array_push($boxes, [
                                'lot_id' => $request->lot_id,
                                'dispatch_guide_id' => $request->dispatch_guide_id,
                                'quantity_type_id' => $request->quantity_type_id,
                                'items' => $item,
                                'kg_amount' => $overallBox->N_PNom,
                                'message' => 'OK',
                                'status' => true
                            ]);
                        } else {
                            array_push($boxes, [
                                'lot_id' => $request->lot_id,
                                'dispatch_guide_id' => $request->dispatch_guide_id,
                                'quantity_type_id' => $request->quantity_type_id,
                                'items' => $item,
                                'message' => 'Error la caja no existe el sistema',
                                'status' => false
                            ]);
                        }
                    } else {
                        array_push($boxes, [
                            'lot_id' => $request->lot_id,
                            'dispatch_guide_id' => $request->dispatch_guide_id,
                            'quantity_type_id' => $request->quantity_type_id,
                            'items' => $item,
                            'message' => 'Error la caja ya se encuentra ingresada en el sistema',
                            'status' => false
                        ]);
                    }
                }

                $validateUpload = 0;

                foreach ($boxes as $box) {
                    if ($box['status'] !== true) {
                        $validateUpload = $validateUpload + 1;
                    }
                }

                if ($validateUpload == 0) {

                    $lotDispatchGuide = LotDispatchGuide::create([
                        'lot_id' => $request->lot_id,
                        'dispatch_guide_id' => $request->dispatch_guide_id,
                    ]);

                    foreach ($boxes as $row) {
                        StockLot::create([
                            'lot_dispatch_guide_id' => $lotDispatchGuide->id,
                            'quantity_type_id' => $row['quantity_type_id'],
                            'items' => $row['items'],
                            'kg_amount' => $row['kg_amount'],
                        ]);
                    }

                    Alert::success('Exito', 'Cajas ingresadas');
                    return redirect()->route('lots.assignment', $request->lot_id);

                } else {
                    session()->forget('items');
                    session()->put('items', $boxes);
                    return redirect()->route('lots.errors');
                }

            }
            else {

                $bines = [];

                foreach (explode("\r\n", $request->items) as $item) {

                    if (!StockLot::where("items", $item)->where('quantity_type_id', 2)->first()) {

                        if ($bin = ViewBins::where('id_bins', (integer)$item)->first()) {

                            array_push($bines, [
                                'lot_id' => $request->lot_id,
                                'dispatch_guide_id' => $request->dispatch_guide_id,
                                'quantity_type_id' => $request->quantity_type_id,
                                'items' => $item,
                                'kg_amount' => $bin->N_Kilos,
                                'message' => 'OK',
                                'status' => true
                            ]);

                        } else {

                            array_push($bines, [
                                'lot_id' => $request->lot_id,
                                'dispatch_guide_id' => $request->dispatch_guide_id,
                                'quantity_type_id' => $request->quantity_type_id,
                                'items' => $item,
                                'message' => 'Error el bins no existe el sistema',
                                'status' => false
                            ]);

                        }
                    } else {

                        array_push($bines, [
                            'lot_id' => $request->lot_id,
                            'dispatch_guide_id' => $request->dispatch_guide_id,
                            'quantity_type_id' => $request->quantity_type_id,
                            'items' => $item,
                            'message' => 'Error el bins ya se encuentra ingresado en el sistema',
                            'status' => false
                        ]);

                    }

                }

                $validateUpload = 0;

                foreach ($bines as $bin) {
                    if ($bin['status'] !== true) {
                        $validateUpload = $validateUpload + 1;
                    }
                }

                if ($validateUpload == 0) {

                    $lotDispatchGuide = LotDispatchGuide::create([
                        'lot_id' => $request->lot_id,
                        'dispatch_guide_id' => $request->dispatch_guide_id,
                    ]);

                    foreach ($bines as $row) {
                        StockLot::create([
                            'lot_dispatch_guide_id' => $lotDispatchGuide->id,
                            'quantity_type_id' => $row['quantity_type_id'],
                            'items' => $row['items'],
                            'kg_amount' => $row['kg_amount'],
                        ]);
                    }
                    Alert::success('Exito', 'Bines ingresados');
                    return redirect()->route('lots.assignment', $request->lot_id);

                } else {
                    session()->forget('items');
                    session()->put('items', $bines);
                    return redirect()->route('lots.errors');
                }
            }

        }
    }


    public function errors(Request $request){
        if($items = session()->get('items')) {
            Alert::error('Error', 'Revisa motivos.');
            return view('lots.error', compact('items'));
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
    public function edit($id)
    {

    }


    public function list(Request $request)
    {
        if ($request->ajax()) {

            $filterStatus = $request->get('filterStatus');
            $filterEnterprise = $request->get('filterEnterprise');
            $filterProvider = $request->get('filterProvider');
            $filterMonth = $request->get('filterMonth');
            $filterYear = $request->get('filterYear');
            $filterAssignment = $request->get('filterAssignment');
            $filterLot = $request->get('filterLot');

            $lots = ViewLot::year($filterYear)
                ->month($filterMonth)
                ->provider($filterProvider)
                ->enterprise($filterEnterprise)
                ->status($filterStatus)
                ->assignment($filterAssignment)
                ->lotNumber($filterLot)
                ->get();

	    foreach($lots as $lot){
		 $lot->N_Guia2 = utf8_encode($lot->N_Guia2);
            }

            return DataTables::of($lots)
                ->addColumn('actions', function($row){
                    return "<a href='lots/assignment/{$row->IdLote}'><i class='fa fa-plus-circle'></i>".utf8_encode('Asignar guía')."</a>";
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
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

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $detailsLot = LotDispatchGuide::find($id);
        StockLot::where('lot_dispatch_guide_id', '=', $id)->delete();
        LotDispatchGuide::find($id)->delete();
        Alert::success('Exito', 'Guia eliminada correctamente');
        return redirect()->route('lots.assignment', $detailsLot->lot_id);
    }

}