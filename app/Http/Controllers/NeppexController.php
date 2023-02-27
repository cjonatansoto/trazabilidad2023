<?php

namespace App\Http\Controllers;


use App\Http\Requests\Neppex\CreateNeppexRequest;
use App\Http\Requests\Neppex\EditNeppexRequest;
use App\Libraries\Excel;
use App\Models\BorderCrossing;
use App\Models\Box;
use App\Models\Consignee;
use App\Models\Country;
use App\Models\DestinationPort;
use App\Models\DJBox;
use App\Models\Exporter;
use App\Models\MirrorViewPacking;
use App\Models\NeppexControl;
use App\Models\ShippingPort;
use App\Models\SlaughterPlace;
use App\Models\SlaughterPlaceNeppex;
use App\Models\StorageLocation;
use App\Models\StoreLocationNeppex;
use App\Models\Transport;
use App\Models\ViewNeppex;
use App\Models\ViewPacking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class NeppexController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:crear-neppex|editar-neppex|eliminar-neppex|filtar-neppex|filtrar-por-caja-neppex|generar-excel-neppex', ['only' => ['index']]);
        $this->middleware('permission:crear-neppex', ['only' => ['create','store']]);
        $this->middleware('permission:editar-neppex', ['only' => ['edit','update']]);
        $this->middleware('permission:eliminar-neppex', ['only' => ['destroy']]);
        $this->middleware('permission:filtar-neppex', ['only' => ['filteredout', 'filteredoutstore']]);
        $this->middleware('permission:filtrar-por-caja-neppex', ['only' => ['filteredoutBox', 'filteredoutBoxStore']]);
        $this->middleware('permission:generar-excel-neppex', ['only' => ['generateExcel']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $neppexs = NeppexControl::all();
        foreach ($neppexs as $neppex){
            if($neppex->codaut !== null){
                if(Box::where("neppex_control_id", "=", $neppex->id)->count() !== 0){
                    $neppex->excel = true;
                }
            }
        }

        return view('neppexControls.index', compact('neppexs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $shippingPorts = ShippingPort::where("inactive", "=", 0)->get();
        $countries = Country::where("inactive", "=", 0)->get();
        $destinationPorts = DestinationPort::where("inactive", "=", 0)->get();
        $exporters = Exporter::where("inactive", "=", 0)->get();
        $borderCrossings = BorderCrossing::where("inactive", "=", 0)->get();
        $consignees = Consignee::where("inactive", "=", 0)->get();
        $storageLocations = StorageLocation::where("inactive", "=", 0)->get();
        $slaughterPlaces = SlaughterPlace::where("inactive", "=", 0)->get();
        $transports = Transport::all();

        return view('neppexControls.create', compact('shippingPorts',
            'countries',
            'destinationPorts',
            'exporters',
            'borderCrossings',
            'consignees',
            'storageLocations',
            'slaughterPlaces',
            'transports'));
    }

    public function lastrecord(){
        $neppex =  DB::table('neppex_controls')->latest('updated_at')->first();
        $shippingPorts = ShippingPort::where("inactive", "=", 0)->get();
        $countries = Country::where("inactive", "=", 0)->get();
        $destinationPorts = DestinationPort::where("inactive", "=", 0)->get();
        $exporters = Exporter::where("inactive", "=", 0)->get();
        $borderCrossings = BorderCrossing::where("inactive", "=", 0)->get();
        $consignees = Consignee::where("inactive", "=", 0)->get();
        $storageLocations = StorageLocation::where("inactive", "=", 0)->get();
        $slaughterPlaces = SlaughterPlace::where("inactive", "=", 0)->get();
        $storageLocationsNeppex = StoreLocationNeppex::where("neppex_control_id", "=", $neppex->id)->get();
        $slaughterPlacesNeppex = SlaughterPlaceNeppex::where("neppex_control_id", "=", $neppex->id)->get();
        $transports = Transport::all();

        return view('neppexControls.lastrecord', compact('shippingPorts',
            'countries',
            'destinationPorts',
            'exporters',
            'borderCrossings',
            'consignees',
            'storageLocations',
            'slaughterPlaces',
            'neppex',
            'storageLocationsNeppex',
            'slaughterPlacesNeppex',
            'transports',
        ));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateNeppexRequest $request)
    {
        if($request) {

            if($request->boxes){

                $boxes = [];

                foreach (explode("\r\n", $request->boxes) as $box) {

                    if (is_numeric($box)) {

                        if (!DJBox::where('overall_box', '=', (integer)$box)->first()) {

                            if (!Box::where("overall_box", $box)->first()) {

                                $overallBox = ViewPacking::where('Caja_General', (integer)$box)->first();

                                if ($overallBox) {
                                    if ($overallBox->N_MotivoSalida === "Despacho a Cliente") {
                                        array_push($boxes, [
                                            'overallBox' => $box,
                                            'N_Equipo' => $overallBox->N_Equipo,
                                            'cod_lote' => $overallBox->cod_lote,
                                            'N_Pallet' => $overallBox->N_Pallet,
                                            'N_Pos' => $overallBox->N_Pos,
                                            'N_IDTurno' => $overallBox->N_IDTurno,
                                            'N_Turno' => $overallBox->N_Turno,
                                            'N_Lote' => $overallBox->N_Lote,
                                            'Tipo_Proceso' => $overallBox->Tipo_Proceso,
                                            'Estado_Lote' => $overallBox->Estado_Lote,
                                            'N_TEXTO1Lote' => $overallBox->N_TEXTO1Lote,
                                            'N_RestriccionMercado' => $overallBox->N_RestriccionMercado,
                                            'limite' => $overallBox->limite,
                                            'CodOT' => $overallBox->CodOT,
                                            'N_OT' => $overallBox->N_OT,
                                            'N_TEXTO1Ot' => $overallBox->N_TEXTO1Ot,
                                            'N_TEXTO1Especie' => $overallBox->N_TEXTO1Especie,
                                            'N_Especie' => $overallBox->N_Especie,
                                            'N_TEXTO1Corte' => $overallBox->N_TEXTO1Corte,
                                            'N_Corte' => $overallBox->N_Corte,
                                            'N_TEXTO1Conservacion' => $overallBox->N_TEXTO1Conservacion,
                                            'N_Conservacion' => $overallBox->N_Conservacion,
                                            'N_TEXTO1Condicion' => $overallBox->N_TEXTO1Condicion,
                                            'N_Condicion' => $overallBox->N_Condicion,
                                            'Id_Producto' => $overallBox->Id_Producto,
                                            'N_CODProducto' => $overallBox->N_CODProducto,
                                            'Producto' => $overallBox->Producto,
                                            'DescProd' => $overallBox->DescProd,
                                            'N_TEXTO1Producto' => $overallBox->N_TEXTO1Producto,
                                            'N_CODTerminacion' => $overallBox->N_CODTerminacion,
                                            'N_Terminacion' => $overallBox->N_Terminacion,
                                            'N_TEXTO1Envase' => $overallBox->N_TEXTO1Envase,
                                            'N_Envase' => $overallBox->N_Envase,
                                            'Empresa' => $overallBox->Empresa,
                                            'N_TEXTO1Calidad' => $overallBox->N_TEXTO1Calidad,
                                            'N_Calidad' => $overallBox->N_Calidad,
                                            'N_TEXTO1Calibre' => $overallBox->N_TEXTO1Calibre,
                                            'N_Calibre' => $overallBox->N_Calibre,
                                            'N_CODUnidad' => $overallBox->N_CODUnidad,
                                            'N_Unidad' => $overallBox->N_Unidad,
                                            'Cliente' => $overallBox->Cliente,
                                            'Usuario' => $overallBox->Usuario,
                                            'Caja_Lote' => $overallBox->Caja_Lote,
                                            'Caja_General' => $overallBox->Caja_General,
                                            'Kg' => $overallBox->Kg,
                                            'tara' => $overallBox->tara,
                                            'N_Medida' => $overallBox->N_Medida,
                                            'piezas' => $overallBox->piezas,
                                            'Fecha_Frigo' => $overallBox->Fecha_Frigo,
                                            'Fecha_Prod' => $overallBox->Fecha_Prod,
                                            'Fecha_Cosecha' => $overallBox->Fecha_Cosecha,
                                            'Registro_Sistema' => $overallBox->Registro_Sistema,
                                            'N_PesoBruto' => $overallBox->N_PesoBruto,
                                            'N_PNom' => $overallBox->N_PNom,
                                            'N_CODOrigen' => $overallBox->N_CODOrigen,
                                            'N_Origen' => $overallBox->N_Origen,
                                            'N_Proveedor' => $overallBox->N_Proveedor,
                                            'N_Jaula' => $overallBox->N_Jaula,
                                            'N_Etiqueta1' => $overallBox->N_Etiqueta1,
                                            'N_Etiqueta2' => $overallBox->N_Etiqueta2,
                                            'N_PesoNeto' => $overallBox->N_PesoNeto,
                                            'Fecha_Venc' => $overallBox->Fecha_Venc,
                                            'N_Barra' => $overallBox->N_Barra,
                                            'N_Tara' => $overallBox->N_Tara,
                                            'N_Tara2' => $overallBox->N_Tara2,
                                            'N_Contratista' => $overallBox->N_Contratista,
                                            'N_Estado' => $overallBox->N_Estado,
                                            'N_MotivoSalida' => $overallBox->N_MotivoSalida,
                                            'N_IdEquipo' => $overallBox->N_IdEquipo,
                                            'N_IdEti1' => $overallBox->N_IdEti1,
                                            'N_IdEti2' => $overallBox->N_IdEti2,
                                            'N_IdLote' => $overallBox->N_IdLote,
                                            'Fecha_Despacho' => $overallBox->Fecha_Despacho,
                                            'N_Contratista_Proceso' => $overallBox->N_Contratista_Proceso,
                                            'N_Guia' => $overallBox->N_Guia,
                                            'Piezas_Enteras' => $overallBox->Piezas_Enteras,
                                            'N_PesoEtiqueta' => $overallBox->N_PesoEtiqueta,
                                            'N_MMPP' => $overallBox->N_MMPP,
                                            'N_BarraMinerva' => $overallBox->N_BarraMinerva,
                                            'N_TEXTO1Desp' => $overallBox->N_TEXTO1Desp,
                                            'N_TEXTO2Desp' => $overallBox->N_TEXTO2Desp,
                                            'N_TEXTO3Desp' => $overallBox->N_TEXTO3Desp,
                                            'N_Embarque' => $overallBox->N_Embarque,
                                            'N_CertfASC' => $overallBox->N_CertfASC,
                                            'N_NumCerfASC' => $overallBox->N_NumCerfASC,
                                            'N_BapEstrellas' => $overallBox->N_BapEstrellas,
                                            'N_Ano' => $overallBox->N_Ano,
                                            'N_Mes' => $overallBox->N_Mes,
                                            'N_PesoNom2' => $overallBox->N_PesoNom2,
                                            'message' => 'OK',
                                            'status' => true
                                        ]);
                                    } else {
                                        array_push($boxes, [
                                            'overallBox' => $box,
                                            'message' => 'La caja ingresada no se encuentra con despacho a cliente, operación cancelada',
                                            'status' => false
                                        ]);
                                    }
                                } else {
                                    array_push($boxes, [
                                        'overallBox' => $box,
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
                        } else {
                            array_push($boxes, [
                                'overallBox' => $box,
                                'message' => 'La caja ingresada se encuentra en declaracion juradara',
                                'status' => false
                            ]);
                        }
                    } else {
                        array_push($boxes, [
                            'overallBox' => $box,
                            'message' => 'La caja ingresada, no aplica formato',
                            'status' => false
                        ]);
                    }

                }

                $validateNeppex = 0;

                foreach ($boxes as $box) {
                    if ($box['status'] !== true) {
                        $validateNeppex = $validateNeppex + 1;
                    }
                }

                if ($validateNeppex == 0) {

                    $neppex = NeppexControl::create([
                        'codaut' => $request->codaut,
                        'transfer_code' => $request->transfer_code,
                        'issue_certificate' => $request->issue_certificate,
                        'transport_id' => $request->transport_id,
                        'authorization_date' => $request->authorization_date ? date('Y-d-m', strtotime($request->authorization_date)) : null,//$request->authorization_date ?? date('Y-d-m', strtotime($request->authorization_date)) ?: null,
                        'container' => $request->container,
                        'container_name' => $request->container_name,
                        'shipping_port_id' => $request->shipping_port_id,
                        'country_id' => $request->country_id,
                        'destination_port_id' => $request->destination_port_id,
                        'exporter_id' => $request->export_id,
                        'border_crossing_id' => $request->border_crossing_id,
                        'consignee_id' => $request->consignee_id,
                    ]);

                    if($request->storage_location_id){
                        foreach ($request->storage_location_id as $item) {
                            StoreLocationNeppex::create([
                                'neppex_control_id' => $neppex->id,
                                'storage_location_id' => $item
                            ]);
                        }
                    }

                    if($request->slaughter_place_id){
                        foreach ($request->slaughter_place_id as $item) {
                            SlaughterPlaceNeppex::create([
                                'neppex_control_id' => $neppex->id,
                                'slaughter_place_id' => $item
                            ]);

                        }
                    }

                    if($boxes){
                        foreach ($boxes as $row) {

                            Box::create([
                                'overall_box' => (integer)$row['overallBox'],
                                'lot_id' => $row['cod_lote'],
                                'neppex_control_id' => $neppex->id
                            ]);

                            MirrorViewPacking::create([
                                'neppex_control_id' => $neppex->id,
                                'N_Equipo' => $row['N_Equipo'],
                                'cod_lote' => $row['cod_lote'],
                                'N_Pallet' => $row['N_Pallet'],
                                'N_Pos' => $row['N_Pos'],
                                'N_IDTurno' => $row['N_IDTurno'],
                                'N_Turno' => $row['N_Turno'],
                                'N_Lote' => $row['N_Lote'],
                                'Tipo_Proceso' => $row['Tipo_Proceso'],
                                'Estado_Lote' => $row['Estado_Lote'],
                                'N_TEXTO1Lote' => $row['N_TEXTO1Lote'],
                                'N_RestriccionMercado' => $row['N_RestriccionMercado'],
                                'limite' => $row['limite'],
                                'CodOT' => $row['CodOT'],
                                'N_OT' => $row['N_OT'],
                                'N_TEXTO1Ot' => $row['N_TEXTO1Ot'],
                                'N_TEXTO1Especie' => $row['N_TEXTO1Especie'],
                                'N_Especie' => $row['N_Especie'],
                                'N_TEXTO1Corte' => $row['N_TEXTO1Corte'],
                                'N_Corte' => $row['N_Corte'],
                                'N_TEXTO1Conservacion' => $row['N_TEXTO1Conservacion'],
                                'N_Conservacion' => $row['N_Conservacion'],
                                'N_TEXTO1Condicion' => $row['N_TEXTO1Condicion'],
                                'N_Condicion' => $row['N_Condicion'],
                                'Id_Producto' => $row['Id_Producto'],
                                'N_CODProducto' => $row['N_CODProducto'],
                                'Producto' => $row['Producto'],
                                'DescProd' => $row['DescProd'],
                                'N_TEXTO1Producto' => $row['N_TEXTO1Producto'],
                                'N_CODTerminacion' => $row['N_CODTerminacion'],
                                'N_Terminacion' => $row['N_Terminacion'],
                                'N_TEXTO1Envase' => $row['N_TEXTO1Envase'],
                                'N_Envase' => $row['N_Envase'],
                                'Empresa' => $row['Empresa'],
                                'N_TEXTO1Calidad' => $row['N_TEXTO1Calidad'],
                                'N_Calidad' => $row['N_Calidad'],
                                'N_TEXTO1Calibre' => $row['N_TEXTO1Calibre'],
                                'N_Calibre' => $row['N_Calibre'],
                                'N_CODUnidad' => $row['N_CODUnidad'],
                                'N_Unidad' => $row['N_Unidad'],
                                'Cliente' => $row['Cliente'],
                                'Usuario' => $row['Usuario'],
                                'Caja_Lote' => $row['Caja_Lote'],
                                'Caja_General' => $row['Caja_General'],
                                'Kg' => $row['Kg'],
                                'tara' => $row['tara'],
                                'N_Medida' => $row['N_Medida'],
                                'piezas' => $row['piezas'],
                                'Fecha_Frigo' => $row['Fecha_Frigo'],
                                'Fecha_Prod' => $row['Fecha_Prod'],
                                'Fecha_Cosecha' => $row['Fecha_Cosecha'],
                                'Registro_Sistema' => $row['Registro_Sistema'],
                                'N_PesoBruto' => $row['N_PesoBruto'],
                                'N_PNom' => $row['N_PNom'],
                                'N_CODOrigen' => $row['N_CODOrigen'],
                                'N_Origen' => $row['N_Origen'],
                                'N_Proveedor' => $row['N_Proveedor'],
                                'N_Jaula' => $row['N_Jaula'],
                                'N_Etiqueta1' => $row['N_Etiqueta1'],
                                'N_Etiqueta2' => $row['N_Etiqueta2'],
                                'N_PesoNeto' => $row['N_PesoNeto'],
                                'Fecha_Venc' => $row['Fecha_Venc'],
                                'N_Barra' => $row['N_Barra'],
                                'N_Tara' => $row['N_Tara'],
                                'N_Tara2' => $row['N_Tara2'],
                                'N_Contratista' => $row['N_Contratista'],
                                'N_Estado' => $row['N_Estado'],
                                'N_MotivoSalida' => $row['N_MotivoSalida'],
                                'N_IdEquipo' => $row['N_IdEquipo'],
                                'N_IdEti1' => $row['N_IdEti1'],
                                'N_IdEti2' => $row['N_IdEti2'],
                                'N_IdLote' => $row['N_IdLote'],
                                'Fecha_Despacho' => $row['Fecha_Despacho'],
                                'N_Contratista_Proceso' => $row['N_Contratista_Proceso'],
                                'N_Guia' => $row['N_Guia'],
                                'Piezas_Enteras' => $row['Piezas_Enteras'],
                                'N_PesoEtiqueta' => $row['N_PesoEtiqueta'],
                                'N_MMPP' => $row['N_MMPP'],
                                'N_BarraMinerva' => $row['N_BarraMinerva'],
                                'N_TEXTO1Desp' => $row['N_TEXTO1Desp'],
                                'N_TEXTO2Desp' => $row['N_TEXTO2Desp'],
                                'N_TEXTO3Desp' => $row['N_TEXTO3Desp'],
                                'N_Embarque' => $row['N_Embarque'],
                                'N_CertfASC' => $row['N_CertfASC'],
                                'N_NumCerfASC' => $row['N_NumCerfASC'],
                                'N_BapEstrellas' => $row['N_BapEstrellas'],
                                'N_Ano' => $row['N_Ano'],
                                'N_Mes' => $row['N_Mes'],
                                'N_PesoNom2' => $row['N_PesoNom2'],
                            ]);
                        }
                    }

                } else {
                    session()->forget('boxes');
                    session()->put('boxes', $boxes);
                    return redirect()->route('neppex.errors');
                }

            }

            Alert::success('Exito', 'Carga exitosa');
            return redirect()->route('neppex.index');

        }

    }

    public function errors(Request $request){
        if($boxes = session()->get('boxes')) {
            Alert::error('Error', 'Revisa motivos.');
            return view('neppexControls.error', compact('boxes'));
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
    public function edit(NeppexControl $neppex)
    {

        $shippingPorts = ShippingPort::where("inactive", "=", 0)->get();
        $countries = Country::where("inactive", "=", 0)->get();
        $destinationPorts = DestinationPort::where("inactive", "=", 0)->get();
        $exporters = Exporter::where("inactive", "=", 0)->get();
        $borderCrossings = BorderCrossing::where("inactive", "=", 0)->get();
        $consignees = Consignee::where("inactive", "=", 0)->get();
        $storageLocations = StorageLocation::where("inactive", "=", 0)->get();
        $slaughterPlaces = SlaughterPlace::where("inactive", "=", 0)->get();
        $storageLocationsNeppex = StoreLocationNeppex::where("neppex_control_id", "=", $neppex->id)->get();
        $slaughterPlacesNeppex = SlaughterPlaceNeppex::where("neppex_control_id", "=", $neppex->id)->get();
        $transports = Transport::all();
        $boxes = Box::where("neppex_control_id", "=", $neppex->id)->get();

        foreach ($storageLocations as $storageLocation) {
            foreach ($storageLocationsNeppex as $item) {
                if ($item->storage_location_id == $storageLocation->id) {
                    $storageLocation->attribute = 'selected';
                }
            }
        }


        foreach ($slaughterPlaces as $slaughterPlace) {
            foreach ($slaughterPlacesNeppex as $item) {
                if ($item->slaughter_place_id == $slaughterPlace->id) {
                    $slaughterPlace->attribute = 'selected';
                }
            }
        }


        return view('neppexControls.edit', compact('shippingPorts',
            'countries',
            'destinationPorts',
            'exporters',
            'borderCrossings',
            'consignees',
            'storageLocations',
            'slaughterPlaces',
            'neppex',
            'storageLocationsNeppex',
            'slaughterPlacesNeppex',
            'transports',
            'boxes'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EditNeppexRequest $request, $id)
    {
        if($request) {

            $neppex = NeppexControl::find($id);
            $neppex->codaut = $request->codaut;
            $neppex->transfer_code = $request->transfer_code;
            $neppex->issue_certificate = $request->issue_certificate;
            $neppex->transport_id = $request->transport_id;
            $neppex->authorization_date = $request->authorization_date ? date('Y-d-m', strtotime($request->authorization_date)) : null;
            $neppex->container = $request->container;
            $neppex->container_name = $request->container_name;
            $neppex->shipping_port_id = $request->shipping_port_id;
            $neppex->country_id = $request->country_id;
            $neppex->destination_port_id = $request->destination_port_id;
            $neppex->exporter_id = $request->export_id;
            $neppex->border_crossing_id = $request->border_crossing_id;
            $neppex->consignee_id = $request->consignee_id;
            $neppex->updated_by = Auth::id();
            $neppex->update();

            StoreLocationNeppex::where('neppex_control_id', $neppex->id)->delete();

            if ($request->storage_location_id) {

                foreach ($request->storage_location_id as $item) {
                    StoreLocationNeppex::create([
                        'neppex_control_id' => $neppex->id,
                        'storage_location_id' => $item
                    ]);
                }
            }

            SlaughterPlaceNeppex::where('neppex_control_id', $neppex->id)->delete();

            if ($request->slaughter_place_id) {



                foreach ($request->slaughter_place_id as $item) {
                    SlaughterPlaceNeppex::create([
                        'neppex_control_id' => $neppex->id,
                        'slaughter_place_id' => $item
                    ]);

                }
            }

            if($request->boxes){

                $boxes = [];

                Box::where('neppex_control_id',$neppex->id)->delete();

                MirrorViewPacking::where('neppex_control_id',$neppex->id)->delete();

                foreach (explode("\r\n", $request->boxes) as $box) {

                    if (is_numeric($box))

                        if (!DJBox::where('overall_box', '=', (integer)$box)->first()) {

                            if (!Box::where("overall_box", $box)->first()) {

                                $overallBox = ViewPacking::where('Caja_General', (integer)$box)->first();


                                if ($overallBox) {
                                    if ($overallBox->N_MotivoSalida === "Despacho a Cliente") {
                                        array_push($boxes, [
                                            'overallBox' => $box,
                                            'N_Equipo' => $overallBox->N_Equipo,
                                            'cod_lote' => $overallBox->cod_lote,
                                            'N_Pallet' => $overallBox->N_Pallet,
                                            'N_Pos' => $overallBox->N_Pos,
                                            'N_IDTurno' => $overallBox->N_IDTurno,
                                            'N_Turno' => $overallBox->N_Turno,
                                            'N_Lote' => $overallBox->N_Lote,
                                            'Tipo_Proceso' => $overallBox->Tipo_Proceso,
                                            'Estado_Lote' => $overallBox->Estado_Lote,
                                            'N_TEXTO1Lote' => $overallBox->N_TEXTO1Lote,
                                            'N_RestriccionMercado' => $overallBox->N_RestriccionMercado,
                                            'limite' => $overallBox->limite,
                                            'CodOT' => $overallBox->CodOT,
                                            'N_OT' => $overallBox->N_OT,
                                            'N_TEXTO1Ot' => $overallBox->N_TEXTO1Ot,
                                            'N_TEXTO1Especie' => $overallBox->N_TEXTO1Especie,
                                            'N_Especie' => $overallBox->N_Especie,
                                            'N_TEXTO1Corte' => $overallBox->N_TEXTO1Corte,
                                            'N_Corte' => $overallBox->N_Corte,
                                            'N_TEXTO1Conservacion' => $overallBox->N_TEXTO1Conservacion,
                                            'N_Conservacion' => $overallBox->N_Conservacion,
                                            'N_TEXTO1Condicion' => $overallBox->N_TEXTO1Condicion,
                                            'N_Condicion' => $overallBox->N_Condicion,
                                            'Id_Producto' => $overallBox->Id_Producto,
                                            'N_CODProducto' => $overallBox->N_CODProducto,
                                            'Producto' => $overallBox->Producto,
                                            'DescProd' => $overallBox->DescProd,
                                            'N_TEXTO1Producto' => $overallBox->N_TEXTO1Producto,
                                            'N_CODTerminacion' => $overallBox->N_CODTerminacion,
                                            'N_Terminacion' => $overallBox->N_Terminacion,
                                            'N_TEXTO1Envase' => $overallBox->N_TEXTO1Envase,
                                            'N_Envase' => $overallBox->N_Envase,
                                            'Empresa' => $overallBox->Empresa,
                                            'N_TEXTO1Calidad' => $overallBox->N_TEXTO1Calidad,
                                            'N_Calidad' => $overallBox->N_Calidad,
                                            'N_TEXTO1Calibre' => $overallBox->N_TEXTO1Calibre,
                                            'N_Calibre' => $overallBox->N_Calibre,
                                            'N_CODUnidad' => $overallBox->N_CODUnidad,
                                            'N_Unidad' => $overallBox->N_Unidad,
                                            'Cliente' => $overallBox->Cliente,
                                            'Usuario' => $overallBox->Usuario,
                                            'Caja_Lote' => $overallBox->Caja_Lote,
                                            'Caja_General' => $overallBox->Caja_General,
                                            'Kg' => $overallBox->Kg,
                                            'tara' => $overallBox->tara,
                                            'N_Medida' => $overallBox->N_Medida,
                                            'piezas' => $overallBox->piezas,
                                            'Fecha_Frigo' => $overallBox->Fecha_Frigo,
                                            'Fecha_Prod' => $overallBox->Fecha_Prod,
                                            'Fecha_Cosecha' => $overallBox->Fecha_Cosecha,
                                            'Registro_Sistema' => $overallBox->Registro_Sistema,
                                            'N_PesoBruto' => $overallBox->N_PesoBruto,
                                            'N_PNom' => $overallBox->N_PNom,
                                            'N_CODOrigen' => $overallBox->N_CODOrigen,
                                            'N_Origen' => $overallBox->N_Origen,
                                            'N_Proveedor' => $overallBox->N_Proveedor,
                                            'N_Jaula' => $overallBox->N_Jaula,
                                            'N_Etiqueta1' => $overallBox->N_Etiqueta1,
                                            'N_Etiqueta2' => $overallBox->N_Etiqueta2,
                                            'N_PesoNeto' => $overallBox->N_PesoNeto,
                                            'Fecha_Venc' => $overallBox->Fecha_Venc,
                                            'N_Barra' => $overallBox->N_Barra,
                                            'N_Tara' => $overallBox->N_Tara,
                                            'N_Tara2' => $overallBox->N_Tara2,
                                            'N_Contratista' => $overallBox->N_Contratista,
                                            'N_Estado' => $overallBox->N_Estado,
                                            'N_MotivoSalida' => $overallBox->N_MotivoSalida,
                                            'N_IdEquipo' => $overallBox->N_IdEquipo,
                                            'N_IdEti1' => $overallBox->N_IdEti1,
                                            'N_IdEti2' => $overallBox->N_IdEti2,
                                            'N_IdLote' => $overallBox->N_IdLote,
                                            'Fecha_Despacho' => $overallBox->Fecha_Despacho,
                                            'N_Contratista_Proceso' => $overallBox->N_Contratista_Proceso,
                                            'N_Guia' => $overallBox->N_Guia,
                                            'Piezas_Enteras' => $overallBox->Piezas_Enteras,
                                            'N_PesoEtiqueta' => $overallBox->N_PesoEtiqueta,
                                            'N_MMPP' => $overallBox->N_MMPP,
                                            'N_BarraMinerva' => $overallBox->N_BarraMinerva,
                                            'N_TEXTO1Desp' => $overallBox->N_TEXTO1Desp,
                                            'N_TEXTO2Desp' => $overallBox->N_TEXTO2Desp,
                                            'N_TEXTO3Desp' => $overallBox->N_TEXTO3Desp,
                                            'N_Embarque' => $overallBox->N_Embarque,
                                            'N_CertfASC' => $overallBox->N_CertfASC,
                                            'N_NumCerfASC' => $overallBox->N_NumCerfASC,
                                            'N_BapEstrellas' => $overallBox->N_BapEstrellas,
                                            'N_Ano' => $overallBox->N_Ano,
                                            'N_Mes' => $overallBox->N_Mes,
                                            'N_PesoNom2' => $overallBox->N_PesoNom2,
                                            'message' => 'OK',
                                            'status' => true
                                        ]);
                                    } else {
                                        array_push($boxes, [
                                            'overallBox' => $box,
                                            'message' => 'La caja ingresada no se encuentra con despacho a cliente, operación cancelada',
                                            'status' => false
                                        ]);
                                    }
                                } else {
                                    array_push($boxes, [
                                        'overallBox' => $box,
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
                        } else {
                            array_push($boxes, [
                                'overallBox' => $box,
                                'message' => 'La caja ingresada se encuentra en declaracion juradara',
                                'status' => false
                            ]);
                        }
                    else {
                        array_push($boxes, [
                            'overallBox' => $box,
                            'message' => 'La caja ingresada, no aplica formato',
                            'status' => false
                        ]);
                    }
                }

                $validateNeppex = 0;

                foreach ($boxes as $box) {
                    if ($box['status'] !== true) {
                        $validateNeppex = $validateNeppex + 1;
                    }
                }

                if ($validateNeppex == 0) {

                    if($boxes){
                        foreach ($boxes as $row) {
                            Box::create([
                                'overall_box' => (integer)$row['overallBox'],
                                'kg_amount' => (integer)$row['N_PesoNom2'],
                                'lot_id' => (integer)$row['cod_lote'],
                                'neppex_control_id' => $neppex->id
                            ]);

                            MirrorViewPacking::create([
                                'neppex_control_id' => $neppex->id,
                                'N_Equipo' => $row['N_Equipo'],
                                'cod_lote' => $row['cod_lote'],
                                'N_Pallet' => $row['N_Pallet'],
                                'N_Pos' => $row['N_Pos'],
                                'N_IDTurno' => $row['N_IDTurno'],
                                'N_Turno' => $row['N_Turno'],
                                'N_Lote' => $row['N_Lote'],
                                'Tipo_Proceso' => $row['Tipo_Proceso'],
                                'Estado_Lote' => $row['Estado_Lote'],
                                'N_TEXTO1Lote' => $row['N_TEXTO1Lote'],
                                'N_RestriccionMercado' => $row['N_RestriccionMercado'],
                                'limite' => $row['limite'],
                                'CodOT' => $row['CodOT'],
                                'N_OT' => $row['N_OT'],
                                'N_TEXTO1Ot' => $row['N_TEXTO1Ot'],
                                'N_TEXTO1Especie' => $row['N_TEXTO1Especie'],
                                'N_Especie' => $row['N_Especie'],
                                'N_TEXTO1Corte' => $row['N_TEXTO1Corte'],
                                'N_Corte' => $row['N_Corte'],
                                'N_TEXTO1Conservacion' => $row['N_TEXTO1Conservacion'],
                                'N_Conservacion' => $row['N_Conservacion'],
                                'N_TEXTO1Condicion' => $row['N_TEXTO1Condicion'],
                                'N_Condicion' => $row['N_Condicion'],
                                'Id_Producto' => $row['Id_Producto'],
                                'N_CODProducto' => $row['N_CODProducto'],
                                'Producto' => $row['Producto'],
                                'DescProd' => $row['DescProd'],
                                'N_TEXTO1Producto' => $row['N_TEXTO1Producto'],
                                'N_CODTerminacion' => $row['N_CODTerminacion'],
                                'N_Terminacion' => $row['N_Terminacion'],
                                'N_TEXTO1Envase' => $row['N_TEXTO1Envase'],
                                'N_Envase' => $row['N_Envase'],
                                'Empresa' => $row['Empresa'],
                                'N_TEXTO1Calidad' => $row['N_TEXTO1Calidad'],
                                'N_Calidad' => $row['N_Calidad'],
                                'N_TEXTO1Calibre' => $row['N_TEXTO1Calibre'],
                                'N_Calibre' => $row['N_Calibre'],
                                'N_CODUnidad' => $row['N_CODUnidad'],
                                'N_Unidad' => $row['N_Unidad'],
                                'Cliente' => $row['Cliente'],
                                'Usuario' => $row['Usuario'],
                                'Caja_Lote' => $row['Caja_Lote'],
                                'Caja_General' => $row['Caja_General'],
                                'Kg' => $row['Kg'],
                                'tara' => $row['tara'],
                                'N_Medida' => $row['N_Medida'],
                                'piezas' => $row['piezas'],
                                'Fecha_Frigo' => $row['Fecha_Frigo'],
                                'Fecha_Prod' => $row['Fecha_Prod'],
                                'Fecha_Cosecha' => $row['Fecha_Cosecha'],
                                'Registro_Sistema' => $row['Registro_Sistema'],
                                'N_PesoBruto' => $row['N_PesoBruto'],
                                'N_PNom' => $row['N_PNom'],
                                'N_CODOrigen' => $row['N_CODOrigen'],
                                'N_Origen' => $row['N_Origen'],
                                'N_Proveedor' => $row['N_Proveedor'],
                                'N_Jaula' => $row['N_Jaula'],
                                'N_Etiqueta1' => $row['N_Etiqueta1'],
                                'N_Etiqueta2' => $row['N_Etiqueta2'],
                                'N_PesoNeto' => $row['N_PesoNeto'],
                                'Fecha_Venc' => $row['Fecha_Venc'],
                                'N_Barra' => $row['N_Barra'],
                                'N_Tara' => $row['N_Tara'],
                                'N_Tara2' => $row['N_Tara2'],
                                'N_Contratista' => $row['N_Contratista'],
                                'N_Estado' => $row['N_Estado'],
                                'N_MotivoSalida' => $row['N_MotivoSalida'],
                                'N_IdEquipo' => $row['N_IdEquipo'],
                                'N_IdEti1' => $row['N_IdEti1'],
                                'N_IdEti2' => $row['N_IdEti2'],
                                'N_IdLote' => $row['N_IdLote'],
                                'Fecha_Despacho' => $row['Fecha_Despacho'],
                                'N_Contratista_Proceso' => $row['N_Contratista_Proceso'],
                                'N_Guia' => $row['N_Guia'],
                                'Piezas_Enteras' => $row['Piezas_Enteras'],
                                'N_PesoEtiqueta' => $row['N_PesoEtiqueta'],
                                'N_MMPP' => $row['N_MMPP'],
                                'N_BarraMinerva' => $row['N_BarraMinerva'],
                                'N_TEXTO1Desp' => $row['N_TEXTO1Desp'],
                                'N_TEXTO2Desp' => $row['N_TEXTO2Desp'],
                                'N_TEXTO3Desp' => $row['N_TEXTO3Desp'],
                                'N_Embarque' => $row['N_Embarque'],
                                'N_CertfASC' => $row['N_CertfASC'],
                                'N_NumCerfASC' => $row['N_NumCerfASC'],
                                'N_BapEstrellas' => $row['N_BapEstrellas'],
                                'N_Ano' => $row['N_Ano'],
                                'N_Mes' => $row['N_Mes'],
                                'N_PesoNom2' => $row['N_PesoNom2'],
                            ]);
                        }
                    }

                } else {
                    session()->forget('boxes');
                    session()->put('boxes', $boxes);
                    return redirect()->route('neppex.errors');
                }

            }

            Alert::success('Exito', 'Carga exitosa');
            return redirect()->route('neppex.edit',$neppex);

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(NeppexControl $neppex)
    {
        if($neppex){
            SlaughterPlaceNeppex::where('neppex_control_id',$neppex->id)->delete();
            StoreLocationNeppex::where('neppex_control_id',$neppex->id)->delete();
            Box::where('neppex_control_id',$neppex->id)->delete();
            MirrorViewPacking::where('neppex_control_id',$neppex->id)->delete();
            $neppex->delete();
            Alert::success('Eliminado','Neppex eliminado correctamente');
            return redirect()->route('neppex.index');
        }else{
            Alert::error('error','Neppex no encontrado');
            return redirect()->route('neppex.index');
        }

    }

    public function filteredoutBox(){
        return view('neppexControls.filteredoutbox');
    }

    public function filteredoutBoxStore(Request $request){
        $this->validate($request, [
            'overall_box' => 'required|integer',
        ]);

        $boxes = ViewNeppex::box($request->overall_box)->get();

        $validate = count($boxes);

        if($validate >= 1){

            $excel     = new Excel([
                'pathfile' => null,
                'filename' => 'NEPPEX_FILTRO',
                'title' => 'REPORTE_NEPPEX',
                'columns' => [
                    'N_Codaut',
                    'N_CodigoTraspaso',
                    'N_FechaAutorizacion',
                    'N_FechaCarga',
                    'N_Contenedor',
                    'N_NombreContenedor',
                    'N_Pais',
                    'N_Transporte',
                    'N_PuertoEmbarque',
                    'N_PuertoDestino',
                    'N_Exportador',
                    'N_Aduana',
                    'N_Consignatario',
                    'N_UsuarioTraza',
                    'N_EmisionCertificado',
                    'N_LugarFaena',
                    'N_LugarAlmacenamiento',
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
                    'N_PesoNom2'
                ]
            ]);

            $excel->setValues($boxes, [
                'N_Codaut',
                'N_CodigoTraspaso',
                'N_FechaAutorizacion',
                'N_FechaCarga',
                'N_Contenedor',
                'N_NombreContenedor',
                'N_Pais',
                'N_Transporte',
                'N_PuertoEmbarque',
                'N_PuertoDestino',
                'N_Exportador',
                'N_Aduana',
                'N_Consignatario',
                'N_UsuarioTraza',
                'N_EmisionCertificado',
                'N_LugarFaena',
                'N_LugarAlmacenamiento',
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
                'N_PesoNom2'
            ]);

            $excel->save();

        }else{

            Alert::error('error', 'No se han encontrado para filro aplicado');
            return redirect()->route('neppex.filteredoutbox');
        }


    }


    public function filteredout(){
        $countries = Country::where("inactive", "=", 0)->get();
        $exporters = Exporter::where("inactive", "=", 0)->get();
        return view('neppexControls.filteredout', compact('countries', 'exporters'));
    }

    public function filteredoutstore(Request $request){

        $boxes = ViewNeppex::authorizationDate($request->start_date_authorization, $request->end_date_authorization)
            ->loadDate($request->start_date_load, $request->end_date_load)
            ->codaut($request->codaut)
            ->transferCode($request->transfer_code)
            ->country($request->country_id)
            ->exporter($request->export_id)->get();

        $validate = count($boxes);

        if($validate >= 1){

            $excel     = new Excel([
                'pathfile' => null,
                'filename' => 'NEPPEX_FILTRO',
                'title' => 'REPORTE_NEPPEX',
                'columns' => [
                    'N_Codaut',
                    'N_CodigoTraspaso',
                    'N_FechaAutorizacion',
                    'N_FechaCarga',
                    'N_Contenedor',
                    'N_NombreContenedor',
                    'N_Pais',
                    'N_Transporte',
                    'N_PuertoEmbarque',
                    'N_PuertoDestino',
                    'N_Exportador',
                    'N_Aduana',
                    'N_Consignatario',
                    'N_UsuarioTraza',
                    'N_EmisionCertificado',
                    'N_LugarFaena',
                    'N_LugarAlmacenamiento',
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
                    'N_PesoNom2'
                ]
            ]);

            $excel->setValues($boxes, [
                'N_Codaut',
                'N_CodigoTraspaso',
                'N_FechaAutorizacion',
                'N_FechaCarga',
                'N_Contenedor',
                'N_NombreContenedor',
                'N_Pais',
                'N_Transporte',
                'N_PuertoEmbarque',
                'N_PuertoDestino',
                'N_Exportador',
                'N_Aduana',
                'N_Consignatario',
                'N_UsuarioTraza',
                'N_EmisionCertificado',
                'N_LugarFaena',
                'N_LugarAlmacenamiento',
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
                'N_PesoNom2'
            ]);

            $excel->save();

        }else{

            Alert::error('error', 'No se han encontrado para filro aplicado');
            return redirect()->route('neppex.filteredout');
        }



    }


    public function generateExcel($codaut){

        set_time_limit(300);

        $boxes = ViewNeppex::codaut($codaut)->get();

        $excel     = new Excel([
            'pathfile' => null,
            'filename' => 'REPORTE_NEPPEX_' . $codaut,
            'title' => 'REPORTE_NEPPEX',
            'columns' => [
                'N_Codaut',
                'N_CodigoTraspaso',
                'N_FechaAutorizacion',
                'N_FechaCarga',
                'N_Contenedor',
                'N_NombreContenedor',
                'N_Pais',
                'N_Transporte',
                'N_PuertoEmbarque',
                'N_PuertoDestino',
                'N_Exportador',
                'N_Aduana',
                'N_Consignatario',
                'N_UsuarioTraza',
                'N_EmisionCertificado',
                'N_LugarFaena',
                'N_LugarAlmacenamiento',
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
                'N_PesoNom2'
            ]
        ]);

        $excel->setValues($boxes, [
            'N_Codaut',
            'N_CodigoTraspaso',
            'N_FechaAutorizacion',
            'N_FechaCarga',
            'N_Contenedor',
            'N_NombreContenedor',
            'N_Pais',
            'N_Transporte',
            'N_PuertoEmbarque',
            'N_PuertoDestino',
            'N_Exportador',
            'N_Aduana',
            'N_Consignatario',
            'N_UsuarioTraza',
            'N_EmisionCertificado',
            'N_LugarFaena',
            'N_LugarAlmacenamiento',
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
            'N_PesoNom2'
        ]);

        $excel->save();
    }
}