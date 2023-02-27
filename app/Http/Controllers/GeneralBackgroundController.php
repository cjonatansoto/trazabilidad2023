<?php

namespace App\Http\Controllers;

use App\Models\GeneralBackground;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class GeneralBackgroundController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if($dispatchguide = session()->get('dispatchguide')){
            $places = Place::orderBy('code', 'ASC')->get();
            $generalbackground = GeneralBackground::where('dispatch_guide_id', $dispatchguide->id)->get();
            return view('generalbackground.index', compact('places','generalbackground','dispatchguide'));
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
                'placeId' => 'required',
                'cage' => 'required',
                'warrantyStatement' => 'required',
                'harvestDate' => 'required',
                'file' => 'required',
            ]);

            $file = 'files/general_backgrounds/' . time() . '.' . $request->file->extension();

            $request->file->move(public_path('files/general_backgrounds'), time() . '.' . $request->file->extension());


            $generalbackground = GeneralBackground::create([
                'dispatch_guide_id' => $dispatchguide->id,
                'place_id' => $request->placeId,
                'cage' => $request->cage,
                'warranty_statement' => $request->warrantyStatement,
                'harvest_date' => date('Y-m-d', strtotime($request->harvestDate)),
                'file' => $file
            ]);

            return (['generalBackground' => [
                'id' => $generalbackground->id,
                'place'=> $generalbackground->place->name,
                'cage'=> $generalbackground->cage,
                'warrantyStatement'=> $generalbackground->warranty_statement,
                'harvestDate'=> date('d-m-Y', strtotime($generalbackground->harvest_date)),
                'file'=> asset($file),
            ]]);
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
    public function destroy(GeneralBackground $generalbackground)
    {
        File::delete($generalbackground->file);
        $generalbackground->delete();
        return (['success' => true]);
    }
}
