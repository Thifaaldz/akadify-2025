<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
      protected $fillable = [
        'nama',
        'nisn',
        'tahun_lulus',
        'sekolah',
        'phone',
    ];

    public function verification()
    {
        return $this->hasOne(Verification::class);
    }
}
