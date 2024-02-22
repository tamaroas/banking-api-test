<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'firstname',
        'adress',
        'email',
        'phone',
        'state',
    ];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

}
