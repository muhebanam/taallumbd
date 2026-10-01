<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'category_id', 'title', 'slug', 'excerpt', 'body', 'thumbnail', 'status', 'published_at'];
    protected $casts = ['published_at' => 'datetime'];

    public function getRouteKeyName(): string { return 'slug'; }
    public function author() { return $this->belongsTo(User::class, 'user_id'); }
    public function category() { return $this->belongsTo(Category::class); }
    public function scopePublished($q) { return $q->where('status', 'published'); }
}
