<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'gear_id', 'status',];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function gear() {
        return $this->belongsTo(Gear::class);
    }
}
