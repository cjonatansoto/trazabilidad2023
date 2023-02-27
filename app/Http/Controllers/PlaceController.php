<?php

namespace App\Http\Controllers;

use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class PlaceController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:crear-centros-de-cultivo|editar-centros-de-cultivo', ['only' => ['index']]);
        $this->middleware('permission:crear-centros-de-cultivo', ['only' => ['create','store']]);
        $this->middleware('permission:editar-centros-de-cultivo', ['only' => ['edit','update']]);
        $this->middleware('permission:activar/desactivar-centros-de-cultivo', ['only' => ['movement']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $places = Place::all();
        return view('places.index', compact('places'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('places.create');
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
            'code' => 'required|unique:sqlsrv.places',
        ]);

        Place::create($request->all());
        Alert::success('Exito', 'Registro creado!');
        return redirect()->route('places.index');
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
    public function edit(Place $place)
    {
        return view('places.edit', compact('place'));
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
            'code' => [
                'required',
                Rule::unique('sqlsrv.places')->ignore($id),
            ],
        ]);

        $input = $request->all();
        $place = Place::find($id);
        $place->update($input);
        Alert::success('Exito', 'Registro modificado!');
        return redirect()->route('places.index');
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

    public function movement(Place $place){
        $place->inactive = $place->inactive == 0 ? 1 : 0;
        $place->save();
        Alert::success('Exito', 'Registro modificado!');
        return redirect()->route('places.index');
    }
}
