<div class="akadify-wrapper">
    <div class="akadify-card">

        <div class="akadify-logo">
            <img src="{{ asset('front/assets/img/logo-akadify.png') }}">
        </div>

        <div class="akadify-title">Verifikasi Ijazah Digital</div>
        <div class="akadify-subtitle">Sistem verifikasi dokumen akademik</div>

        @if (session()->has('success'))
            <div class="akadify-success">{{ session('success') }}</div>
        @endif

        <form wire:submit.prevent="submit" class="akadify-form">

            {{-- SEARCH SISWA --}}
            <div style="position: relative;">
                <label>Siswa</label>

                <input
                    type="text"
                    wire:model.live="search"
                    wire:keydown.enter.prevent="selectFirst"
                    wire:keydown.arrow-down.prevent="moveDown"
                    wire:keydown.arrow-up.prevent="moveUp"
                    placeholder="Ketik nama siswa..."
                    autocomplete="off"
                >

                {{-- DROPDOWN --}}
                @if ($showDropdown && $students->isNotEmpty())
                    <div class="akadify-search-box">
                        @foreach ($students as $index => $student)
                            <div
                                class="akadify-search-item {{ $activeIndex === $index ? 'active' : '' }}"
                                wire:click="selectStudent({{ $student->id }})"
                            >
                                <strong>{{ $student->nama }}</strong><br>
                                <small>{{ $student->nisn }}</small>
                            </div>
                        @endforeach
                    </div>
                @endif

                @error('student_id')
                    <div class="akadify-error">Silakan pilih siswa dari daftar</div>
                @enderror
            </div>

            <br>

            {{-- FILE IJAZAH --}}
            <div>
                <label>File Ijazah</label>
                <input type="file" wire:model="ijazah">
                @error('ijazah')
                    <div class="akadify-error">{{ $message }}</div>
                @enderror
            </div>

            <button class="akadify-btn">Upload & Verifikasi</button>
        </form>
    </div>
</div>
