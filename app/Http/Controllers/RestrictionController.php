<?php

namespace App\Http\Controllers;

use App\Models\Restriction;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class RestrictionController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:crear-restricciones-de-mercado|editar-restricciones-de-mercado', ['only' => ['index']]);
        $this->middleware('permission:crear-restricciones-de-mercado', ['only' => ['create','store']]);
        $this->middleware('permission:editar-restricciones-de-mercado', ['only' => ['edit','update']]);
        $this->middleware('permission:eliminar-restricciones-de-mercado', ['only' => ['movement']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $restrictions = Restriction::all();
        return view('restrictions.index', compact('restrictions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('restrictions.create');
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
            'name' => 'required'
        ]);

        Restriction::create($request->all());
        Alert::success('Exito', 'Registro creado!');
        return redirect()->route('restrictions.index');
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
    public function edit(Restriction $restriction)
    {
        return view('restrictions.edit', compact('restriction'));
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
        ]);

        $input = $request->all();
        $restriction = Restriction::find($id);
        $restriction->update($input);
        Alert::success('Exito', 'Registro modificado!');
        return redirect()->route('restrictions.index');
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

    public function movement(Restriction $restriction){
        $status = $restriction->inactive == 0 ? 1 : 0;
        $restriction->inactive = $status;
        $restriction->save();
        Alert::success('Exito', 'Registro modificado!');
        return redirect()->route('restrictions.index');
    }
}