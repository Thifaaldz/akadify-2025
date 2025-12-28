<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Student;
use App\Models\Verification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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

        // Simpan file ijazah langsung ke disk 'ijazah' (storage/app/ijazah)
        $filename = $this->ijazah->getClientOriginalName();
        $path = $this->ijazah->storeAs('', $filename, 'ijazah');

        // Buat record verifikasi
        $verification = Verification::create([
            'student_id' => $student->id,
            'ijazah_path' => $path,
            'status' => 'PENDING_OCR',
        ]);

        // Trigger workflow n8n dengan path yang sudah di-mount di docker
        Http::post(config('services.n8n.webhook'), [
            'verification_id' => $verification->id,
            'student_id' => $student->id,
            'phone' => $student->phone,
            'file_path' => '/home/node/.n8n-files/ijazah/' . basename($path),
        ]);

        session()->flash(
            'success',
            'Ijazah berhasil diupload. Proses verifikasi sedang berlangsung.'
        );

        $this->reset();
    }

    public function render()
    {
        return view('livewire.upload-ijazah', [
            'students' => Student::all(),
        ])->layout('layouts.app');
    }
}
