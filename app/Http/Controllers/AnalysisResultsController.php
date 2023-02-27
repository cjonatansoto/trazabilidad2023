<?php

namespace App\Http\Controllers;

use App\Models\AnalysisResults;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class AnalysisResultsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if($dispatchguide = session()->get('dispatchguide')){

            $analysisresults = AnalysisResults::leftJoin('laboratories', 'analysis_results.laboratory_id', '=', 'laboratories.id')
                ->where('dispatch_guide_id', $dispatchguide->id)
                ->select([
                    'analysis_results.id',
                    'analysis_results.number',
                    'analysis_results.file',
                    DB::raw("(CASE WHEN analysis_results.report_date IS NOT NULL
                                      THEN FORMAT (analysis_results.report_date, 'dd-MM-yy')
                                      ELSE 'No aplica'
                                 END) as reportDate"),
                    DB::raw("(CASE WHEN analysis_results.laboratory_id IS NOT NULL
                                              THEN UPPER(laboratories.name)
                                              ELSE 'No aplica'
                                         END) as laboratoryName")                ])
                ->get();

            $laboratories = Laboratory::where('inactive', '=', 0)->get();
            return view('analysisresults.index', compact('laboratories','analysisresults', 'dispatchguide'));
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
                'number' => 'required'
            ]);



            $file = null;

            if($request->file){
                $file = 'files/analysis_results/' . time() . '.' . $request->file->extension();
                $request->file->move(public_path('files/analysis_results'), time() . '.' . $request->file->extension());
            }

	  

            $analysisresults = AnalysisResults::create([
                'dispatch_guide_id' => $dispatchguide->id,
                'number' => $request->number,
                'laboratory_id' => $request->laboratoryId != null ? $request->laboratoryId : null,
                'report_date' => $request->reportDate != null ? date('Y-m-d', strtotime($request->reportDate)) : null,
                'file' => $file
            ]);

            return (['analysisresults' => [
                'id' => $analysisresults->id,
                'number'=> $analysisresults->number,
                'laboratory'=> $analysisresults->laboratory != null ? $analysisresults->laboratory->name : 'No aplica',
                'reportDate'=> $analysisresults->report_date != null ? date('d-m-Y', strtotime($analysisresults->report_date)) : 'No aplica',
                'file'=> $analysisresults->file!= null ? asset($file): $file
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
    public function destroy(AnalysisResults $analysisresult)
    {
        File::delete($analysisresult->file);
        $analysisresult->delete();
        return (['success' => true]);
    }
}
