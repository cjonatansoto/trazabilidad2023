<?php

namespace App\Http\Controllers;

use App\Models\AnalysisResults;
use App\Models\CountryRestriction;
use App\Models\Market;
use App\Models\MarketRestriction;
use App\Models\Restriction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MarketRestrictionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if($dispatchguide = session()->get('dispatchguide')){
            $marketrestrictions = MarketRestriction::where('dispatch_guide_id', $dispatchguide->id)->get();
            $restrictions = Market::where('inactivo', 0)->get();
            return view('marketrestrictions.index', compact('marketrestrictions', 'dispatchguide', 'restrictions'));
        }else{
            return redirect()->route('dispatchguides.index');
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($dispatchguide = session()->get('dispatchguide')){
            $this->validate($request, [
                'code' => 'required',
                'name' => 'required',
                'countries' => 'required'
            ]);

            $marketrestriction = MarketRestriction::create([
                'dispatch_guide_id' => $dispatchguide->id,
                'code' => $request->code,
                'name' => $request->name,
                'countries' => $request->countries,
            ]);

            $countries = '<ul>';

            foreach(explode(",", $marketrestriction->countries) as $country){
                $countries .= '<li>'.$country.'</li>';
            }

            $countries .= '</ul>';


            return (
            ['marketrestriction' => [
                'id' => $marketrestriction->id,
                'code' => $marketrestriction->code,
                'name' => $marketrestriction->name,
                'countries' => $countries,
            ]
            ]);
        }else{
            return ([error => true]);
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
    public function destroy(MarketRestriction $marketrestriction)
    {
        File::delete($marketrestriction->file);
        $marketrestriction->delete();
        return (['success' => true]);
    }
}