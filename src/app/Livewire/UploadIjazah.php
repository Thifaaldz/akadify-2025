<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Student;
use App\Models\Verification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UploadIjazah extends Component
{
    use WithFileUploads;

    public $student_id;
    public $ijazah;

    protected $rules = [
        'student_id' => 'required|exists:students,id',
        'ijazah' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
    ];

    public function submit()
    {
        $this->validate();

        // Ambil data student
        $student = Student::findOrFail($this->student_id);

        // Simpan file ijazah ke disk 'ijazah'
        $filename = $this->ijazah->getClientOriginalName();
        try {
            $path = $this->ijazah->storeAs('', $filename, 'ijazah');
        } catch (\Throwable $e) {
            Log::error('Failed to store ijazah file', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Gagal menyimpan file ijazah — periksa konfigurasi disk dan permission.');
            return;
        }

        // Buat record verifikasi
        $verification = Verification::create([
            'student_id' => $student->id,
            'ijazah_path' => $path,
            'status' => 'PENDING_OCR',
        ]);

        // Ambil URL webhook N8N
        $webhookUrl = env('N8N_WEBHOOK_URL');

        if (! $webhookUrl) {
            Log::error('N8N webhook URL not configured', ['verification_id' => $verification->id]);
            session()->flash('error', 'Webhook URL belum dikonfigurasi. Hubungi administrator.');
            return;
        }

        // Trigger webhook N8N dengan Basic Auth
        try {
            $response = Http::withBasicAuth('test', 'test') // Basic Auth: user=test, password=test
                ->acceptJson()
                ->retry(3, 1000) // retry 3 kali, delay 1 detik
                ->timeout(10)
                ->post($webhookUrl, [
                    'verification_id' => $verification->id,
                    'student_id' => $student->id,
                    'phone' => $student->phone,
                    'file_path' => '/home/node/.n8n-files/ijazah/' . basename($path),
                ]);

            if ($response->successful()) {
                Log::info('n8n webhook delivered', [
                    'url' => $webhookUrl,
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'verification_id' => $verification->id
                ]);
                session()->flash('success', 'Ijazah berhasil diupload. N8N workflow otomatis dijalankan.');
            } else {
                Log::error('n8n webhook failed', [
                    'url' => $webhookUrl,
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'verification_id' => $verification->id
                ]);
                session()->flash('error', 'Upload berhasil tapi N8N gagal (status: ' . $response->status() . ').');
            }
        } catch (\Throwable $e) {
            Log::error('n8n webhook exception', [
                'message' => $e->getMessage(),
                'verification_id' => $verification->id
            ]);
            session()->flash('error', 'Gagal menghubungi N8N: ' . $e->getMessage());
        }

        // Reset hanya properti file
        $this->reset('ijazah');
    }

    public function render()
    {
        return view('livewire.upload-ijazah', [
            'students' => Student::all(),
        ])->layout('layouts.app');
    }
}
