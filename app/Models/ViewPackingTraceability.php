<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ViewPackingTraceability extends Model
{

    protected $connection = 'bdsystem';

    use HasFactory;

    protected $table = 'v_empaque_traza';

}
