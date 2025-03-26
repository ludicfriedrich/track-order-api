<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderLine extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'product_id', 'quantity', 'unit_price'];

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

    // Une ligne de commande appartient à une commande
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Une ligne de commande concerne un produit
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
