<?php

namespace App\Http\Controllers;

use App\Models\ShippingPort;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ShippingPortController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:crear-puertos-de-embarque|editar-puertos-de-embarque|eliminar-puertos-de-embarque', ['only' => ['index']]);
        $this->middleware('permission:crear-puertos-de-embarque', ['only' => ['create','store']]);
        $this->middleware('permission:editar-puertos-de-embarque', ['only' => ['edit','update']]);
        $this->middleware('permission:activar/desactivar-puertos-de-embarque', ['only' => ['movement']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shippingPorts = ShippingPort::all();
        return view('shippingPorts.index', compact('shippingPorts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('shippingPorts.create');
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
        $input = $request->all();
        $shippingPort = ShippingPort::create($input);
        return redirect()->route('shippingports.index');
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
        $shippingPort = ShippingPort::find($id);
        return view('shippingPorts.edit',compact('shippingPort'));
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
            'name' => 'required'
        ]);

        $input = $request->all();
        $shippingPort = ShippingPort::find($id);
        $shippingPort->update($input);
        Alert::success('Exito', 'Registro modificado!');
        return redirect()->route('shippingports.index');
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

    public function movement(ShippingPort $shippingPort){
        $status = $shippingPort->inactive == 0 ? 1 : 0;
        $shippingPort->inactive = $status;
        $shippingPort->save();
        Alert::success('Exito', 'Registro modificado!');
        return redirect()->route('shippingports.index');
    }
}
