<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hint extends Model
{
    use HasFactory;

    protected $fillable = [
        'puzzle_id',
        'level',
        'content',
    ];

    public function puzzle()
    {
        return $this->belongsTo(Puzzle::class);
    }
}
