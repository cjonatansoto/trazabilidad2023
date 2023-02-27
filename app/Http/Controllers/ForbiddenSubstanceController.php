<?php

namespace App\Http\Controllers;

use App\Models\ForbiddenSubstance;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ForbiddenSubstanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if($dispatchguide = session()->get('dispatchguide')){
            $forbiddensubstances = ForbiddenSubstance::where('dispatch_guide_id', $dispatchguide->id)->get();
            $laboratories = Laboratory::where('inactive', '=', 0)->get();
            return view('forbiddensubstances.index', compact('forbiddensubstances', 'laboratories','dispatchguide'));
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
                'number' => 'required',
                'laboratoryId' => 'required',
                'reportDate' => 'required',
                'file' => 'required',
            ]);

            $file = 'files/forbidden_substances/' . time() . '.' . $request->file->extension();

            $request->file->move(public_path('files/forbidden_substances'), time() . '.' . $request->file->extension());


            $forbiddensubstances = ForbiddenSubstance::create([
                'dispatch_guide_id' => $dispatchguide->id,
                'number' => $request->number,
                'laboratory_id' => $request->laboratoryId,
                'report_date' => date('Y-m-d', strtotime($request->reportDate)),
                'file' => $file
            ]);

            return (['forbiddensubstances' => [
                'id' => $forbiddensubstances->id,
                'number'=> $forbiddensubstances->number,
                'laboratory'=> $forbiddensubstances->laboratory->name,
                'reportDate'=> date('d-m-Y', strtotime($forbiddensubstances->report_date)),
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
    public function destroy(ForbiddenSubstance $forbiddensubstance)
    {
        File::delete($forbiddensubstance->file);
        $forbiddensubstance->delete();
        return (['success' => true]);
    }
}
