<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_resets_custom';
    
    protected $fillable = [
        'email',
        'token',
        'created_at'
    ];
    
    public $timestamps = false;
}