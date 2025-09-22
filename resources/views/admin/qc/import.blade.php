<x-app-layout>
  <div style="padding:16px;">
    <h1>Impor QC (Paste)</h1>

    @if(session('status'))
      <div style="border:1px solid #ddd;padding:10px;background:#fafafa;margin-bottom:12px;">
        {{ session('status') }}
      </div>
    @endif

    @if(session('import_errors'))
      <div style="border:1px solid #f5c2c7;background:#f8d7da;color:#842029;padding:10px;margin-bottom:12px;">
        <strong>Kesalahan:</strong>
        <ul>
          @foreach(session('import_errors') as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form method="post" action="{{ route('admin.qc.import.store') }}">
      @csrf
      <div style="margin-bottom:8px;">
        <label>Delimiter:
          <select name="delimiter">
            <option value="comma">Comma (,)</option>
            <option value="tab">Tab (\t)</option>
            <option value="semicolon">Semicolon (;)</option>
            <option value="space">Space</option>
          </select>
        </label>
      </div>

      <div>
        <label>Payload (6 kolom: customer, heat_number, item, hasil, operator, department)</label>
        <textarea name="payload" style="width:100%;height:220px;"
          placeholder="PT Sukses Makmur, HN-240901-001, Flange 2&quot; 150#, OK, Budi, QC&#10;CV Baja Prima, HN-240901-002, Elbow 3&quot; SCH40, NG, Sari, QC"></textarea>
        @error('payload')<div style="color:#b00020">{{ $message }}</div>@enderror
      </div>

      <div style="margin-top:8px;">
        <button type="submit">Impor</button>
        <a href="{{ route('admin.qc.index') }}" style="margin-left:8px;">Kembali</a>
      </div>
    </form>
  </div>
</x-app-layout>
