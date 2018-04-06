<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    public function author()
    {
        return $this->hasMany(User::class);
    }
    public function allow()
    {
        $this->status=1;
        $this->save();
    }
    public function disAllow()
    {
        $this->status=0;
        $this->save();
    }
    public function toggleAdmin()
    {
        if($this->status = 0)
        {
            return $this->allow();
        }
        return $this-disAllow();
    }
    public function remove()
    {
        $this->delete();
    }
}
