<?php

namespace App\Models;

use App\Tenancy\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeBook extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $table = 'grade_books';

    protected $fillable = [
        'organization_id',
        'cohort_id',
        'user_id',
        'term',
        'scores_breakdown',
        'total_marks',
        'obtained_marks',
        'overall_percentage',
        'overall_grade',
        'position_in_class',
        'remarks',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'scores_breakdown' => 'array',
            'total_marks' => 'float',
            'obtained_marks' => 'float',
            'overall_percentage' => 'float',
            'position_in_class' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
