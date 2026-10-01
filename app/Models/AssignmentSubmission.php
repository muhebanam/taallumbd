<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    protected $fillable = ['assignment_id', 'user_id', 'file_path', 'answer_text', 'marks', 'feedback', 'status'];
    public function assignment() { return $this->belongsTo(Assignment::class); }
    public function user() { return $this->belongsTo(User::class); }
}
