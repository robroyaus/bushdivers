<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fleet extends Model
{
    use HasFactory;

    public function aircraft()
    {
        return $this->hasMany(Aircraft::class, 'fleet_id', 'id');
    }
}
