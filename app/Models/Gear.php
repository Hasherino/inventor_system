<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gear extends Model
{
    use HasFactory;

    protected $table = 'gear';

    protected $fillable = [
        'name', 'description', 'user_id', 'unit_price', 'code',
        'serial_number', 'long_term', 'lend_stage'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function request() {
        return $this->hasOne(Request::class);
    }

    public function history() {
        return $this->hasMany(History::class);
    }
}
