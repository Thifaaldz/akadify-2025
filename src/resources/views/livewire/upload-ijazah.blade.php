<div class="akadify-wrapper">
    <div class="akadify-card">

        <div class="akadify-logo">
            <img src="{{ asset('front/assets/img/logo-akadify.png') }}" alt="AKADIFY">
        </div>

        <div class="akadify-title">
            Verifikasi Ijazah Digital
        </div>

        <div class="akadify-subtitle">
            Sistem verifikasi dokumen akademik berbasis web
        </div>

        @if (session()->has('success'))
            <div class="akadify-success">
                {{ session('success') }}
            </div>
        @endif

        <form wire:submit.prevent="submit" class="akadify-form">

            <div>
                <label>Siswa</label>
                <select wire:model="student_id">
                    <option value="">Pilih Siswa</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">
                            {{ $student->nama }} — {{ $student->nisn }}
                        </option>
                    @endforeach
                </select>
                @error('student_id')
                    <div class="akadify-error">{{ $message }}</div>
                @enderror
            </div>

            <br>

            <div>
                <label>File Ijazah</label>
                <input type="file" wire:model="ijazah">
                @error('ijazah')
                    <div class="akadify-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="akadify-btn">
                Upload & Verifikasi
            </button>
        </form>
    </div>
</div>
