<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class DispatchGuideItem extends Model
{

    use HasFactory;

    protected $connection = 'sqlsrv';

    protected $table = 'dispatch_guide_items';

    protected $dateFormat ='Y-d-m H:i:s.v';

    protected $fillable = [
        'dispatch_guide_id',
        'quantity_type_id',
        'amount',
        'pieces',
        'kgs',
        'species_id',
        'cut_id',
        'preservation_id',
    ];

    public function quantityType()
    {
        return $this->belongsTo('App\Models\QuantityType', 'quantity_type_id');
    }

    public function species()
    {
        return $this->belongsTo('App\Models\Species', 'species_id');
    }

    public function court()
    {
        return $this->belongsTo('App\Models\Court', 'cut_id');
    }

    public function preservation()
    {
        return $this->belongsTo('App\Models\Preservation', 'preservation_id');
    }

}
