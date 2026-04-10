<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lending extends Model
{
    use HasFactory;

    protected $fillable = ['item_id', 'borrower_name', 'total', 'lending_date', 'return_date'];

    protected $casts = [
        'lending_date' => 'date',
        'return_date' => 'date',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}