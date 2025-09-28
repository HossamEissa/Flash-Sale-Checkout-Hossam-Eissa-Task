<?php

namespace App\Models;

use App\Traits\DynamicPagination;
use App\Traits\Filterable;
use App\Traits\Searchable;
use App\Traits\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    use  HasFactory , DynamicPagination , Filterable , Searchable , Sortable;
    protected $fillable = [
        'user_id',
        'token'
    ];

####################################### Relations ###################################################

####################################### End Relations ###############################################

################################ Accessors and Mutators #############################################

################################ End Accessors and Mutators #########################################
}
