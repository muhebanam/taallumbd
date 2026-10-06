<?php

namespace App\Models;

use App\Tenancy\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $table = 'attendance';

    protected $fillable = [
        'organization_id',
        'cohort_id',
        'user_id',
        'marked_by',
        'date',
        'session_name',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'string',
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

    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
