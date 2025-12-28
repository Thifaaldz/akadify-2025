<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OcrResult extends Model
{
    protected $fillable = [
        'verification_id',
        'raw_text',
        'nisn',
        'nama',
        'tahun_lulus',
        'sekolah',
    ];

    public function verification()
    {
        return $this->belongsTo(Verification::class);
    }
}
