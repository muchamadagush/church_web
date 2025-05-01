<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        <h1>{{ isset($jemaat) ? 'Edit' : 'Tambah' }} Jemaat</h1>

        @if(session('error'))
            <div style="background-color: #fee2e2; border: 1px solid #ef4444; color: #dc2626; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                {{ session('error') }}
            </div>
        @endif
        
        <div class="card" style="padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
          <form 
              action="{{ isset($jemaat) ? route('jemaat.update', $jemaat->id) : route('jemaat.store') }}" 
              method="POST" 
              style="width: 100%;"
            >
              @csrf
              @if(isset($jemaat))
                  @method('PUT')
              @endif

              <div style="margin-bottom: 15px;">
                  <label style="display: block; margin-bottom: 5px;">
                      Username
                      <span style="color: #dc2626;">*</span>
                  </label>
                  <input type="text" 
                        name="username" 
                        value="{{ old('username', $jemaat->username ?? '') }}" 
                        required 
                        style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                  @error('username')
                      <span style="color: #dc2626; font-size: 0.875em;">{{ $message }}</span>
                  @enderror
              </div>

              <div style="margin-bottom: 15px;">
                  <label style="display: block; margin-bottom: 5px;">
                      Nama Lengkap
                      <span style="color: #dc2626;">*</span>
                  </label>
                  <input type="text" 
                        name="fullname" 
                        value="{{ old('fullname', $jemaat->fullname ?? '') }}" 
                        required 
                        style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                  @error('fullname')
                      <span style="color: #dc2626; font-size: 0.875em;">{{ $message }}</span>
                  @enderror
              </div>

              @if(!isset($jemaat))
              <div style="margin-bottom: 15px;">
                  <label style="display: block; margin-bottom: 5px;">
                      Password
                      <span style="color: #dc2626;">*</span>
                  </label>
                  <input type="password" 
                        name="password" 
                        required 
                        style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                  @error('password')
                      <span style="color: #dc2626; font-size: 0.875em;">{{ $message }}</span>
                  @enderror
              </div>
              @endif

              <div style="margin-bottom: 15px;">
                  <label style="display: block; margin-bottom: 5px;">
                      Tanggal Lahir
                      <span style="color: #dc2626;">*</span>
                  </label>
                  <input type="date" 
                        name="dateofbirth" 
                        value="{{ old('dateofbirth', isset($jemaat) ? $jemaat->dateofbirth : '') }}" 
                        required 
                        style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                  @error('dateofbirth')
                      <span style="color: #dc2626; font-size: 0.875em;">{{ $message }}</span>
                  @enderror
              </div>

              <div style="margin-bottom: 15px;">
                  <label style="display: block; margin-bottom: 5px;">
                      Alamat
                      <span style="color: #dc2626;">*</span>
                  </label>
                  <textarea name="address" 
                            required 
                            style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">{{ old('address', $jemaat->address ?? '') }}</textarea>
                  @error('address')
                      <span style="color: #dc2626; font-size: 0.875em;">{{ $message }}</span>
                  @enderror
              </div>

              <div style="margin-bottom: 15px;">
                  <label style="display: block; margin-bottom: 5px;">
                      Gereja
                      <span style="color: #dc2626;">*</span>
                  </label>
                  <select name="church_id" 
                          required 
                          style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                      <option value="">Pilih Gereja</option>
                      @foreach($churches as $church)
                          <option value="{{ $church->id }}" 
                              {{ old('church_id', $jemaat->church_id ?? '') == $church->id ? 'selected' : '' }}>
                              {{ $church->name }}
                          </option>
                      @endforeach
                  </select>
                  @error('church_id')
                      <span style="color: #dc2626; font-size: 0.875em;">{{ $message }}</span>
                  @enderror
              </div>

              <div style="margin-top: 20px; display: flex; gap: 10px;">
                  <button type="submit" style="background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Simpan</button>
                  <a href="{{ route('jemaat.index') }}" style="background: #dc2626; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none;">Batal</a>
              </div>
          </form>
        </div>
</div>