<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_resets_custom';
    
    protected $fillable = [
        'user_id',
        'email',
        'token',
        'status',
        'requested_at',
        'completed_at'
    ];
}