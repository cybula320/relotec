<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PaymentMethod extends Model
{
    //

    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'nazwa',
        'opis',
        'termin',
        'aktywny',
    ];

    public function firmy()
     {
         return $this->belongsToMany(Firma::class, 'firma_payment_method');
      }

      public function getActivitylogOptions(): LogOptions
      {
          return LogOptions::defaults()
              ->logFillable()
              ->useLogName('Metody Płatności')
              ->logOnlyDirty(false)
              ->submitEmptyLogs();
      }

}
