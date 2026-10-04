<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'type', 'parent_id', 'sort_order', 'status'];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function fatawa()
    {
        return $this->hasMany(Fatwa::class);
    }

    public function publications()
    {
        return $this->hasMany(Publication::class);
    }

    public function scopeOfType($q, string $type)
    {
        return $q->where('type', $type)->where('status', 'active');
    }
}
