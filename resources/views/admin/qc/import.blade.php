<x-app-layout>
  <div class="container">
    <div class="card">
      <div class="header">
        <h1>Impor QC (Paste)</h1>
        <a href="{{ route('admin.qc.index') }}" class="btn btn-ghost">Kembali</a>
      </div>

      @if(session('status'))
        <div class="alert info">{{ session('status') }}</div>
      @endif

      @if(session('import_errors'))
        <div class="alert error">
          <strong>Kesalahan:</strong>
          <ul>
            @foreach(session('import_errors') as $e) <li>{{ $e }}</li> @endforeach
          </ul>
        </div>
      @endif

      <form method="post" action="{{ route('admin.qc.import.store') }}">
        @csrf

        <div class="field">
          <label for="delimiter">Delimiter</label>
          <select id="delimiter" name="delimiter" class="select" style="max-width:220px">
            <option value="comma">Comma (,)</option>
            <option value="tab">Tab (\t)</option>
            <option value="semicolon">Semicolon (;)</option>
            <option value="space">Space</option>
          </select>
        </div>

        <div class="field">
          <label for="payload">Payload (6 kolom per baris: customer, heat_number, item, hasil, operator, department)</label>
          <textarea id="payload" name="payload" class="textarea"
            placeholder="PT Sukses Makmur, HN-240901-001, Flange 2&quot; 150#, OK, Budi, QC&#10;CV Baja Prima, HN-240901-002, Elbow 3&quot; SCH40, NG, Sari, QC"></textarea>
          @error('payload') <div class="alert error mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="mt-2">
          <button type="submit" class="btn btn-primary">Impor sekarang</button>
          <a class="btn btn-ghost ml-1" href="{{ route('admin.qc.index') }}">Lihat data</a>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
