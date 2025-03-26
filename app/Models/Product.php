<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    // Les champs que l'on peut remplir (en masse)
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock'
    ];

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

    // Méthode pour mettre à jour le stock
    public function updateStock(int $quantityChange)
    {
        // Vérifier si le stock est suffisant avant de réduire
        if ($this->stock + $quantityChange < 0) {
            throw new \Exception("Stock insuffisant pour le produit : {$this->name}");
        }

        // Mettre à jour le stock
        $this->stock += $quantityChange;
        $this->save();
    }
}
