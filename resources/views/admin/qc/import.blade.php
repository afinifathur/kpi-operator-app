<!doctype html>
<!-- bagian ini yang ditambah -->
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Impor QC (Paste)</title>
  <link rel="stylesheet" href="{{ asset('css/filament-overrides.css') }}">
  <style>
    body { font-family: Arial, sans-serif; padding: 16px; }
    textarea { width: 100%; height: 220px; }
    .box { border: 1px solid #ddd; padding: 10px; margin-bottom: 12px; background: #fafafa; }
    .err { color: #b00020; }
  </style>
</head>
<body>
  <h1>Impor QC (Paste)</h1>

  @if(session('status'))
    <div class="box">{{ session('status') }}</div>
  @endif

  @if(session('import_errors'))
    <div class="box err">
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
      <label>Payload (6 kolom per baris: customer, heat_number, item, hasil, operator, department)</label>
      <textarea name="payload" placeholder="PT Sukses Makmur, HN-240901-001, Flange 2&quot; 150#, OK, Budi, QC&#10;CV Baja Prima, HN-240901-002, Elbow 3&quot; SCH40, NG, Sari, QC"></textarea>
      @error('payload')<div class="err">{{ $message }}</div>@enderror
    </div>

    <div style="margin-top:8px;">
      <button type="submit">Impor</button>
      <a href="{{ route('admin.qc.index') }}" style="margin-left:8px;">Kembali</a>
    </div>
  </form>
</body>
</html>
