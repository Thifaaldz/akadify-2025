<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    protected $fillable = [
        'student_id',
        'ijazah_path',
        'status',
        'reason',
    ];

    protected $casts = [
        'reason' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function ocrResult()
    {
        return $this->hasOne(OcrResult::class);
    }
}
