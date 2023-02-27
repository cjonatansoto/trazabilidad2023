<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class DispatchGuide extends Model
{

    use HasFactory;

    protected $connection = 'sqlsrv';

    protected $table = 'dispatch_guides';

    protected $dateFormat ='Y-d-m H:i:s.v';

    protected $fillable = [
        'number',
        'plant_entry_date',
        'date_physical_guide',
        'target_date',
        'enterprise_id',
        'provider_id',
        'dispatch_guide_type_id',
        'observations',
        'file',
        'created_by',
        'updated_by'
    ];


    public function enterprise()
    {
        return $this->belongsTo('App\Models\Enterprise', 'enterprise_id','cod_empresa');
    }

    public function provider()
    {
        return $this->belongsTo('App\Models\Provider', 'provider_id', 'cod_proveedor');
    }

    public function dispatchGuideType()
    {
        return $this->belongsTo('App\Models\DispatchGuideType', 'dispatch_guide_type_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo('App\Models\User', 'updated_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = is_object(Auth::guard(config('app.guards.web'))->user()) ? Auth::guard(config('app.guards.web'))->user()->id : 1;
            $model->updated_by = is_object(Auth::guard(config('app.guards.web'))->user()) ? Auth::guard(config('app.guards.web'))->user()->id : 1;
        });

        static::updating(function ($model) {
            $model->updated_by = is_object(Auth::guard(config('app.guards.web'))->user()) ? Auth::guard(config('app.guards.web'))->user()->id : 1;
        });
    }

}
