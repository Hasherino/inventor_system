<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $table = 'history';

    protected $fillable = [
        'owner_id', 'user_id', 'sender_id', 'gear_id', 'event'
    ];

    public static function getUsersHistory($user) {
        $history = $user->history()->get()->push();
        $senderHistory = History::where('sender_id', $user->id)->get();
        foreach($senderHistory as $row) {
            $history = $history->push($row);
        }

        foreach($history as $row) {
            $row['gear'] = $row->gear()->get();
        }

        return $history;
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function gear() {
        return $this->belongsTo(Gear::class);
    }
}
