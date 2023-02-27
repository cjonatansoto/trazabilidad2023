<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;
use ValidateRequests;

class LaboratoryController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:crear-laboratorios|editar-laboratorios', ['only' => ['index']]);
        $this->middleware('permission:crear-laboratorios', ['only' => ['create','store']]);
        $this->middleware('permission:editar-laboratorios', ['only' => ['edit','update']]);
        $this->middleware('permission:activar/desactivar-laboratorios', ['only' => ['movement']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $laboratories = Laboratory::all();
        return view('laboratories.index', compact('laboratories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('laboratories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'rut' => 'required|unique:sqlsrv.laboratories|cl_rut',
        ]);

        Laboratory::create($request->all());
        Alert::success('Exito', 'Registro creado!');
        return redirect()->route('laboratories.index');
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
    public function edit(Laboratory $laboratory)
    {
        return view('laboratories.edit', compact('laboratory'));
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
        $this->validate($request, [
            'name' => 'required',
            'rut' => [
                'required',
                Rule::unique('sqlsrv.laboratories')->ignore($id),
                'cl_rut'
            ],
        ]);

        $input = $request->all();
        $laboratory = Laboratory::find($id);
        $laboratory->update($input);
        Alert::success('Exito', 'Registro modificado!');
        return redirect()->route('laboratories.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function movement(Laboratory $laboratory){
        $status = $laboratory->inactive == 0 ? 1 : 0;
        $laboratory->inactive = $status;
        $laboratory->save();
        Alert::success('Exito', 'Registro modificado!');
        return redirect()->route('laboratories.index');
    }
}
