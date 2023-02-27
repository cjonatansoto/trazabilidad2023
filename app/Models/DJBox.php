<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class DJBox extends Model
{

    use HasFactory;

    protected $connection = 'sqlsrv';

    protected $table = 'declared_jurisdiction_boxes';

    protected $dateFormat = 'Y-d-m H:i:s.v';

    protected $fillable = [
        'overall_box',
        'product_name',
        'product_id',
        'kg',
        'lot_id',
        'lot_number',
        'date_of_elaboration',
        'date_of_expiration',
        'declared_jurisdiction_id'
    ];

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo('App\Models\User', 'updated_by');
    }
}
