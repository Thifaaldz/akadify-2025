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

    protected $listeners = ['closeDropdown'];

    public string $search = '';
    public ?int $student_id = null;
    public bool $showDropdown = false;
    public int $activeIndex = 0;
    public $ijazah;

    protected $rules = [
        'student_id' => 'required|exists:students,id',
        'ijazah'     => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
    ];

    // =================================================
    // Ambil siswa sesuai search
    // =================================================
    public function getStudents()
    {
        if (strlen($this->search) < 1) {
            return collect();
        }

        return Student::query()
            ->where('nama', 'like', "%{$this->search}%")
            ->orderBy('nama')
            ->limit(10)
            ->get();
    }

    public function updatedSearch()
    {
        $this->showDropdown = true;
        $this->activeIndex = 0;
        $this->student_id = null;
    }

    public function selectStudent(int $id)
    {
        $student = Student::find($id);
        if (! $student) return;

        $this->student_id = $student->id;
        $this->search = "{$student->nama} — {$student->nisn}";
        $this->showDropdown = false;
    }

    public function selectFirst()
    {
        $students = $this->getStudents();
        if ($students->isNotEmpty()) {
            $this->selectStudent($students[$this->activeIndex]->id);
        }
    }

    public function moveDown()
    {
        $count = $this->getStudents()->count();
        if ($this->activeIndex < $count - 1) $this->activeIndex++;
    }

    public function moveUp()
    {
        if ($this->activeIndex > 0) $this->activeIndex--;
    }

    public function closeDropdown()
    {
        $this->showDropdown = false;
    }

    // =================================================
    // Submit ijazah dan trigger N8N
    // =================================================
    public function submit()
    {
        $this->validate();

        $student = Student::findOrFail($this->student_id);

        // Simpan file ke disk 'ijazah'
        $filename = $this->ijazah->getClientOriginalName();
        try {
            $path = $this->ijazah->storeAs('', $filename, 'ijazah');
        } catch (\Throwable $e) {
            Log::error('Failed to store ijazah file', ['message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menyimpan file ijazah.');
            return;
        }

        // Buat record verifikasi
        $verification = Verification::create([
            'student_id' => $student->id,
            'ijazah_path' => $path,
            'status' => 'PENDING_OCR',
        ]);

        $webhookUrl = env('N8N_WEBHOOK_URL');

        if (!$webhookUrl) {
            Log::error('N8N webhook URL not configured', ['verification_id' => $verification->id]);
            session()->flash('error', 'Webhook N8N belum dikonfigurasi.');
            return;
        }

        try {
            $response = Http::withBasicAuth('test','test')
                ->acceptJson()
                ->retry(3, 1000)
                ->timeout(10)
                ->post($webhookUrl, [
                    'verification_id' => $verification->id,
                    'student_id'      => $student->id,
                    'phone'           => $student->phone,
                    'file_path'       => '/home/node/.n8n-files/ijazah/' . basename($path),
                ]);

            if ($response->successful()) {
                Log::info('N8N webhook delivered', [
                    'url' => $webhookUrl,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                session()->flash('success', 'Ijazah berhasil diupload. N8N workflow dijalankan.');
            } else {
                Log::error('N8N webhook failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                session()->flash('error', 'Upload berhasil tapi N8N gagal.');
            }
        } catch (\Throwable $e) {
            Log::error('N8N webhook exception', ['message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghubungi N8N: ' . $e->getMessage());
        }

        $this->reset(['ijazah', 'search', 'student_id']);
    }

    public function render()
    {
        return view('livewire.upload-ijazah', [
            'students' => $this->getStudents(),
        ])->layout('layouts.app');
    }
}
