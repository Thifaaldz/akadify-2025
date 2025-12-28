<div>
    <h2>Upload Ijazah</h2>

    @if (session()->has('success'))
        <p style="color: green;">
            {{ session('success') }}
        </p>
    @endif

    <form wire:submit.prevent="submit">
        <div>
            <label>Mahasiswa</label><br>
            <select wire:model="student_id">
                <option value="">-- Pilih Mahasiswa --</option>
                @foreach($students as $student)
                    <option value="{{ $student->id }}">
                        {{ $student->nama }} ({{ $student->nisn }})
                    </option>
                @endforeach
            </select>
            @error('student_id') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <br>

        <div>
            <label>File Ijazah</label><br>
            <input type="file" wire:model="ijazah">
            @error('ijazah') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <br>

        <button type="submit">Upload</button>
    </form>
</div>
