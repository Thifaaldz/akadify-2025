<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Simpan hasil verifikasi OCR dari n8n ke database
     */
    public function store(Request $request)
    {
        // Validasi request minimal
        $request->validate([
            'student_id'   => 'required|exists:students,id',
            'ijazah_path'  => 'required|string',
            'valid'        => 'required|boolean',
            'reason'       => 'nullable|array',
        ]);

        // Tentukan status berdasarkan valid flag
        $status = $request->valid ? 'VERIFIED' : 'REJECTED';

        // Simpan ke tabel verifications
        $verification = Verification::create([
            'student_id' => $request->student_id,
            'ijazah_path' => $request->ijazah_path,
            'status' => $status,
            'reason' => $request->reason,
        ]);

        return response()->json([
            'message' => 'Verification saved successfully',
            'data' => $verification,
        ]);
    }
}
