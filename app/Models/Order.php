<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;
use App\Models\OrderLine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['client_name', 'client_phone', 'total_price', 'status', 'user_id'];

    // Formatage de la date de création
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y H:i:s');
    }

    // Formatage de la date de mise à jour
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y H:i:s');
    }

    // Une commande est enregistré par un utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Une commande peut avoir plusieurs lignes de commande
    public function orderLines()
    {
        return $this->hasMany(OrderLine::class);
    }
}
