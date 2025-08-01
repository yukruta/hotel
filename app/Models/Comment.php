<?php

namespace App\Models;

use FontLib\Table\Type\post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }
    public function post(){
        return $this->belongsTo(Post::class, 'post_id','id');
    }
}
