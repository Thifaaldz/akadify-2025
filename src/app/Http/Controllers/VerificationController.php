<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use App\Jobs\SendN8nWebhook;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VerificationController extends Controller
{
    /**
     * Simpan hasil verifikasi OCR dari n8n ke database
     */
    public function store(Request $request)
    {
        // Validasi request minimal
        $request->validate([
            'verification_id' => 'nullable|exists:verifications,id',
            'student_id'   => 'required_without:verification_id|exists:students,id',
            'ijazah_path'  => 'required_without:verification_id|string',
            // n8n can send either `valid` (boolean) OR a `status` string like VERIFIED/REJECTED
            'valid'        => 'required_without:status|boolean',
            'status'       => 'nullable|string|in:VERIFIED,REJECTED',
            'reason'       => 'nullable|array',
        ]);

        // Prefer explicit status if provided by n8n; otherwise derive from `valid` boolean
        if ($request->filled('status')) {
            $status = strtoupper($request->status);
        } else {
            $status = $request->valid ? 'VERIFIED' : 'REJECTED';
        }

        // Jika ada verification_id, update record yang ada; jika tidak, buat baru
        if ($request->filled('verification_id')) {
            $verification = Verification::findOrFail($request->verification_id);
            $verification->update([
                'student_id' => $request->student_id ?? $verification->student_id,
                'ijazah_path' => $request->ijazah_path ?? $verification->ijazah_path,
                'status' => $status,
                'reason' => $request->reason,
            ]);
            // Log source for debugging
            \Log::info('Verification updated via n8n callback', ['id' => $verification->id, 'status' => $status]);
        } else {
            // Try to find an existing PENDING_OCR verification to update instead of creating a duplicate.
            $incomingFilename = null;
            if ($request->filled('ijazah_path')) {
                $incomingFilename = basename($request->ijazah_path);
            } elseif ($request->filled('file_path')) {
                $incomingFilename = basename($request->file_path);
            }

            $found = null;
            if ($incomingFilename && $request->filled('student_id')) {
                $found = Verification::where('student_id', $request->student_id)
                    ->where('status', 'PENDING_OCR')
                    ->where('ijazah_path', 'like', "%{$incomingFilename}%")
                    ->latest()
                    ->first();
            }

            if ($found) {
                $found->update([
                    'status' => $status,
                    'reason' => $request->reason,
                    'ijazah_path' => $request->ijazah_path ?? $found->ijazah_path,
                ]);
                $verification = $found;
                \Log::info('Matched and updated existing PENDING verification via n8n', ['id' => $verification->id, 'status' => $status]);
            } else {
                $verification = Verification::create([
                    'student_id' => $request->student_id,
                    'ijazah_path' => $request->ijazah_path,
                    'status' => $status,
                    'reason' => $request->reason,
                ]);
                \Log::info('Verification created via n8n callback', ['id' => $verification->id, 'status' => $status]);
            }
        }

        // Dispatch n8n notification job (fire-and-forget via queue)
        $eventName = $request->valid ? 'verification_verified' : 'verification_rejected';
        SendN8nWebhook::dispatch($verification, $eventName);

        return response()->json([
            'message' => 'Verification saved successfully',
            'data' => $verification,
        ]);
    }

    /**
     * Update an existing verification status.
     */
    public function update(Request $request, Verification $verification): JsonResponse
    {
        $request->validate([
            'valid' => 'required|boolean',
            'reason' => 'nullable|array',
        ]);

        $status = $request->valid ? 'VERIFIED' : 'REJECTED';

        $verification->update([
            'status' => $status,
            'reason' => $request->reason,
        ]);

        $eventName = $request->valid ? 'verification_verified' : 'verification_rejected';
        SendN8nWebhook::dispatch($verification, $eventName);

        return response()->json([
            'message' => 'Verification updated',
            'data' => $verification->fresh(),
        ]);
    }
}
