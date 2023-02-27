<?php

namespace App\Http\Controllers;

use App\Models\AnalysisResults;
use App\Models\Court;
use App\Models\DispatchGuide;
use App\Models\DispatchGuideItem;
use App\Models\DispatchGuideType;
use App\Models\Enterprise;
use App\Models\ForbiddenSubstance;
use App\Models\GeneralBackground;
use App\Models\Lot;
use App\Models\LotDispatchGuide;
use App\Models\MarketRestriction;
use App\Models\Preservation;
use App\Models\Provider;
use App\Models\QuantityType;
use App\Models\Species;
use App\Models\StockLot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\DB;

class DispatchGuideController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-guias-de-despacho|crear-guias-de-despacho|editar-guias-de-despacho|eliminar-guias-de-despacho', ['only' => ['index']]);
        $this->middleware('permission:ver-guias-de-despacho', ['only' => ['show']]);
        $this->middleware('permission:crear-guias-de-despacho', ['only' => ['create','store']]);
        $this->middleware('permission:editar-guias-de-despacho', ['only' => ['edit','update']]);
        $this->middleware('permission:eliminar-guias-de-despacho', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dispatchguides = DispatchGuide::orderBy('id', 'DESC')->get();

        foreach ($dispatchguides as $dispatchguide) {

            $countAnalysisResults = AnalysisResults::where("dispatch_guide_id", "=", $dispatchguide->id)->count();
            $countForbiddenSubstance = ForbiddenSubstance::where("dispatch_guide_id", "=", $dispatchguide->id)->count();
            $countGeneralBackground = GeneralBackground::where("dispatch_guide_id", "=", $dispatchguide->id)->count();
            $countMarketRestriction = MarketRestriction::where("dispatch_guide_id", "=", $dispatchguide->id)->count();
            $lots = LotDispatchGuide::where("dispatch_guide_id", "=", $dispatchguide->id)->get();



            $detailLots = [];

            foreach ($lots as $lot){
                $lotName = Lot::where('cod_lote', '=', $lot->lot_id)->first();
                array_push($detailLots, $lotName->nombre);
            }



            $dispatchguide->count = [
                'countAnalysisResults' => 'Resultados de analisis: '.$countAnalysisResults,
                'countForbiddenSubstance' => 'Sustancias prohibidas: '.$countForbiddenSubstance,
                'countGeneralBackground' => 'Antecedentes generales: '.$countGeneralBackground,
                'countMarketRestriction' => 'Restricciones de mercado: '.$countMarketRestriction,
            ];


            if($detailLots){
                $lotNames = implode('</br>', $detailLots);
            }else{
                $lotNames = 'Sin lotes asignados';
            }

            $dispatchguide->lot = $lotNames;

            if ($dispatchguide->dispatchGuideType->id == 1) {
                if ($countAnalysisResults == 0 || $countForbiddenSubstance == 0 || $countGeneralBackground == 0 || $countMarketRestriction === 0) {
                    $dispatchguide->status = "table-danger";
                } else {
                    $dispatchguide->status = "table-success";
                }

            } else if ($dispatchguide->dispatchGuideType->id == 2) {
                $dispatchguide->status = "table-success";
            } else {
                $dispatchguide->status = "table-success";
            }

        }

        return view('dispatchguides.index', compact('dispatchguides'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $species = Species::where('inactivo', 0)->orderBy('descripcion', 'ASC')->get();
        $enterprises = Enterprise::where('inactivo', 0)->orderBy('descripcion', 'ASC')->get();
        $providers = Provider::where('inactivo', 0)->orderBy('descripcion', 'ASC')->get();
        $cuts = Court::where('inactivo', 0)->orderBy('nombre', 'ASC')->get();
        $preservation = Preservation::where('inactivo', 0)->orderBy('nombre', 'ASC')->get();
        $quantitytypes = QuantityType::all();
        $dispatchguidetypes = DispatchGuideType::all();
        return view('dispatchguides.create', compact('enterprises', 'providers', 'quantitytypes', 'species','cuts', 'preservation', 'dispatchguidetypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $check = DispatchGuide::where('enterprise_id', $request->enterpriseId)->where('provider_id', $request->providerId)->where('number', $request->number)->first();

        $this->validate($request, [
            'dispatchGuideTypeId' => 'required',
            'number' => 'required',
            'plantEntryDate' => 'required',
            'datePhysicalGuide' => 'required',
            'targetDate' => 'required',
            'enterpriseId' => 'required',
            'providerId' => 'required',
            'countItems' => 'required',
            'file' => 'required', //error masde2mn
            'number' => 'required'
        ]);

        if ($check) {
            return response()->json([
                'errors' => [
                    'number' => 'El numero de guía ya se encuentra en uso'
                ]
            ], 422);
        } else {

            $file = 'files/dispatch_guides/' . time() . '.' . $request->file->extension();

            $request->file->move(public_path('files/dispatch_guides'), time() . '.' . $request->file->extension());


            $dispatchguide = DispatchGuide::create([
                'number' => $request->number,
                'plant_entry_date' => date('Y-d-m H:i:s.v', strtotime($request->plantEntryDate)),
                'date_physical_guide' => date('Y-d-m H:i:s.v', strtotime($request->datePhysicalGuide)),
                'target_date' => date('Y-d-m', strtotime($request->targetDate)),
                'enterprise_id' => $request->enterpriseId,
                'provider_id' => $request->providerId,
                'dispatch_guide_type_id' => $request->dispatchGuideTypeId,
                'observations' => $request->observations,
                'file' => $file
            ]);


            foreach (json_decode(stripslashes($request->items)) as $item) {

                DispatchGuideItem::create([
                    'dispatch_guide_id' => $dispatchguide->id,
                    'quantity_type_id' => $item->quantityTypeId,
                    'amount' => $item->amount,
                    'pieces' => $item->pieces,
                    'kgs' => $item->kgs,
                    'species_id' => $item->speciesId,
                    'cut_id' => $item->cutId,
                    'preservation_id' => $item->preservationId,
                ]);

            }


            return (['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(DispatchGuide $dispatchguide)
    {
        $dispatchguideitems = DispatchGuideItem::where('dispatch_guide_id', $dispatchguide->id)->get();
        $countAnalysisResults = AnalysisResults::where("dispatch_guide_id", "=", $dispatchguide->id)->count();
        $countForbiddenSubstance = ForbiddenSubstance::where("dispatch_guide_id", "=", $dispatchguide->id)->count();
        $countGeneralBackground = GeneralBackground::where("dispatch_guide_id", "=", $dispatchguide->id)->count();
        $countMarketRestriction = MarketRestriction::where("dispatch_guide_id", "=", $dispatchguide->id)->count();
       return view('dispatchguides.show', compact('dispatchguide', 'dispatchguideitems', 'countAnalysisResults', 'countForbiddenSubstance', 'countGeneralBackground', 'countMarketRestriction'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(DispatchGuide $dispatchguide)
    {
        $species = Species::where('inactivo', 0)->orderBy('descripcion', 'ASC')->get();
        $enterprises = Enterprise::where('inactivo', 0)->orderBy('descripcion', 'ASC')->get();
        $providers = Provider::where('inactivo', 0)->orderBy('descripcion', 'ASC')->get();
        $cuts = Court::where('inactivo', 0)->orderBy('nombre', 'ASC')->get();
        $preservation = Preservation::where('inactivo', 0)->orderBy('nombre', 'ASC')->get();
        $quantitytypes = QuantityType::all();
        $dispatchguidetypes = DispatchGuideType::all();
        $dispatchguideitems = DispatchGuideItem::where("dispatch_guide_id", "=", $dispatchguide->id)->get();
        $countAnalysisResults = AnalysisResults::where("dispatch_guide_id", "=", $dispatchguide->id)->count();
        $countForbiddenSubstance = ForbiddenSubstance::where("dispatch_guide_id", "=", $dispatchguide->id)->count();
        $countGeneralBackground = GeneralBackground::where("dispatch_guide_id", "=", $dispatchguide->id)->count();
        $countMarketRestriction = MarketRestriction::where("dispatch_guide_id", "=", $dispatchguide->id)->count();


        return view('dispatchguides.edit', compact('dispatchguide','dispatchguideitems','enterprises', 'providers', 'quantitytypes', 'species','cuts', 'preservation', 'dispatchguidetypes', 'countAnalysisResults', 'countForbiddenSubstance', 'countGeneralBackground', 'countMarketRestriction'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DispatchGuide $dispatchguide)
    {


        $this->validate($request, [
            'dispatchGuideTypeId' => 'required',
            'number' => 'required',
            'plantEntryDate' => 'required',
            'datePhysicalGuide' => 'required',
            'targetDate' => 'required',
            'enterpriseId' => 'required',
            'providerId' => 'required',
            'countItems' => 'required',
        ]);


        if ($request->hasFile('file')) {

            File::delete($dispatchguide->file);

            $file = 'files/dispatch_guides/' . time() . '.' . $request->file->extension();

            $request->file->move(public_path('files/dispatch_guides'), time() . '.' . $request->file->extension());

        } else {
            $file = $dispatchguide->file;
        }


        $dispatchguide->dispatch_guide_type_id = $request->dispatchGuideTypeId;
        $dispatchguide->plant_entry_date = date('Y-d-m H:i:s.v', strtotime($request->plantEntryDate));
        $dispatchguide->date_physical_guide = date('Y-d-m H:i:s.v', strtotime($request->datePhysicalGuide));
        $dispatchguide->target_date = date('Y-d-m', strtotime($request->targetDate));
        $dispatchguide->enterprise_id = $request->enterpriseId;
        $dispatchguide->provider_id = $request->providerId;
        $dispatchguide->observations = $request->observations;
        $dispatchguide->file = $file;
        $dispatchguide->save();

        DispatchGuideItem::where('dispatch_guide_id','=',$dispatchguide->id)->delete();

        foreach ( json_decode(stripslashes($request->items)) as $item){

            DispatchGuideItem::create([
                'dispatch_guide_id' => $dispatchguide->id,
                'quantity_type_id' => $item->quantityTypeId,
                'amount' => $item->amount,
                'pieces' => $item->pieces,
                'kgs' => $item->kgs,
                'species_id' => $item->speciesId,
                'cut_id' => $item->cutId,
                'preservation_id' => $item->preservationId,
            ]);

        }


        return (['success' => true ]);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DispatchGuide $dispatchguide)
    {
        if($dispatchguide){

            $generalbackground = GeneralBackground::where('dispatch_guide_id',$dispatchguide->id)->get();

            if($generalbackground){
                foreach ($generalbackground as $item){
                    File::delete($item->file);
                    GeneralBackground::where('id',$item->id)->delete();
                }
            }

            $analysisresults = AnalysisResults::where('dispatch_guide_id',$dispatchguide->id)->get();

            if($analysisresults){
                foreach ($analysisresults as $item){
                    File::delete($item->file);
                    AnalysisResults::where('id',$item->id)->delete();
                }
            }


            $forbiddensubstance = ForbiddenSubstance::where('dispatch_guide_id',$dispatchguide->id)->get();

            if($forbiddensubstance){
                foreach ($forbiddensubstance as $item){
                    File::delete($item->file);
                    ForbiddenSubstance::where('id',$item->id)->delete();
                }
            }

            $marketrestriction = MarketRestriction::where('dispatch_guide_id',$dispatchguide->id)->get();

            if($marketrestriction){
                foreach ($marketrestriction as $item){
                    File::delete($item->file);
                    MarketRestriction::where('id',$item->id)->delete();
                }
            }


            DispatchGuideItem::where('dispatch_guide_id',$dispatchguide->id)->delete();




            $stocklots = StockLot::join('lot_dispatch_guides', 'stock_lots.lot_dispatch_guide_id', '=', 'lot_dispatch_guides.id')
                            ->where('lot_dispatch_guides.dispatch_guide_id', $dispatchguide->id)
                            ->select([
                                DB::raw('stock_lots.id as id')
                            ])
                            ->get();


		


            foreach ($stocklots as $stocklot){

                StockLot::where('id', '=', $stocklot->id)->delete();
            }

            LotDispatchGuide::where('dispatch_guide_id', $dispatchguide->id)->delete();

            File::delete($dispatchguide->file);

            $dispatchguide->delete();

            Alert::success('Exito','Guia eliminada correctamente');

            return redirect()->route('dispatchguides.index');
        }else{
            Alert::error('error','Guia no encontrado');
            return redirect()->route('dispatchguides.index');
        }
    }

    public function redirect($url, DispatchGuide $dispatchguide){

        if($url === "generalbackground"){
            session()->forget('dispatchguide');
            session()->put('dispatchguide', $dispatchguide);
            return redirect()->route('generalbackground.index');
        }

        if($url === "analysisresults"){
            session()->forget('dispatchguide');
            session()->put('dispatchguide', $dispatchguide);
            return redirect()->route('analysisresults.index');
        }

        if($url === "forbiddensubstances"){
            session()->forget('dispatchguide');
            session()->put('dispatchguide', $dispatchguide);
            return redirect()->route('forbiddensubstances.index');
        }

        if($url === "marketrestrictions"){
            session()->forget('dispatchguide');
            session()->put('dispatchguide', $dispatchguide);
            return redirect()->route('marketrestrictions.index');
        }

    }
}