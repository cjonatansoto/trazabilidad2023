<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StockLot extends Model
{

    use HasFactory;

    protected $connection = 'sqlsrv';

    protected $table = 'stock_lots';

    public $timestamps = false;

    protected $fillable = [
        'lot_dispatch_guide_id',
        'quantity_type_id',
        'measurement_unit_id',
        'items',
        'amount',
        'kg_amount'
    ];

}
