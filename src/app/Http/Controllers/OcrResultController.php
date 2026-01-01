<?php

namespace App\Http\Controllers;

use App\Models\OcrResult;
use App\Models\Student;
use Illuminate\Http\Request;

class OcrResultController extends Controller
{
    /**
     * Terima data OCR dari n8n dan simpan ke database.
     */
    public function store(Request $request)
    {
        // Validasi input
        $data = $request->validate([
            'phone' => 'nullable|string|max:20',
            'file_path' => 'nullable|string|max:255',
            'student_id' => 'nullable|integer|exists:students,id',
            'nama_ocr' => 'nullable|string|max:255',
            'nisn_ocr' => 'nullable|string|max:255',
            'sekolah_ocr' => 'nullable|string|max:255',
            'tahun_lulus_ocr' => 'nullable|string|max:4',
            'raw_text' => 'nullable|string',
        ]);

        // Simpan ke tabel ocr_results
        $ocrResult = OcrResult::create([
            'verification_id' => $data['student_id'] ?? null, // hubungkan ke student
            'nama' => $data['nama_ocr'] ?? null,
            'nisn' => $data['nisn_ocr'] ?? null,
            'sekolah' => $data['sekolah_ocr'] ?? null,
            'tahun_lulus' => $data['tahun_lulus_ocr'] ?? null,
            'raw_text' => $data['raw_text'] ?? null,
        ]);

        return response()->json([
            'message' => 'OCR Result saved successfully',
            'data' => $ocrResult,
        ], 201);
    }
}
