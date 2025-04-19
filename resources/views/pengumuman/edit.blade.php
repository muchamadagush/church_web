@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">
    <h2>Ubah Data Pengumuman</h2>

    <form action="{{ route('pengumuman.update', $announcement->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px;">Judul :</label>
            <input type="text" name="title" value="{{ old('title', $announcement->title) }}" 
                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            @error('title')
                <span style="color: red; font-size: 0.8em;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px;">Gereja :</label>
            <select name="church_id" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Pilih Gereja</option>
                @foreach($churches as $church)
                    <option value="{{ $church->id }}" 
                        {{ old('church_id', $announcement->church_id) == $church->id ? 'selected' : '' }}>
                        {{ $church->name }}
                    </option>
                @endforeach
            </select>
            @error('church_id')
                <span style="color: red; font-size: 0.8em;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px;">Tanggal :</label>
            <input type="date" name="announcement_date" 
                value="{{ old('announcement_date', $announcement->announcement_date) }}"
                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            @error('announcement_date')
                <span style="color: red; font-size: 0.8em;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px;">Banner :</label>
            <img id="banner-preview" 
                src="{{ $announcement->banner ? asset('storage/' . $announcement->banner) : '' }}" 
                style="{{ $announcement->banner ? '' : 'display: none;' }} max-width: 200px; margin: 10px 0;">
            <input type="file" name="banner" accept="image/*" onchange="previewImage(this)"
                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            @error('banner')
                <span style="color: red; font-size: 0.8em;">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" style="background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
                Simpan
            </button>
            <a href="{{ route('pengumuman.index') }}" style="background: #dc2626; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none;">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('banner-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
