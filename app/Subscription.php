<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = ['email','token'];
    public static function add($email)
    {
        $sub = new static;
        $sub->email = $email; // получение email
        $sub->token = str_random(100); // генераци токена
        $sub->save();
        return $sub;
    }
    public function remove()
    {
        $this->delete();
    }
}
