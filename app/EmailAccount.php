<?php

namespace JeromeSavin\UccelloEmailClient;

use Illuminate\Database\Eloquent\Model;
use App\User;

class EmailAccount extends Model
{
    public $fillable = [
        'user_id', 'service_name', 'username', 'token', 'refresh_token', 'expiration'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
