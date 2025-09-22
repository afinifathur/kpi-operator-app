<x-app-layout>
  <div class="container">
    <div class="card">
      <div class="header">
        <h1>QC Records</h1>
        <a href="{{ route('admin.qc.import') }}" class="btn btn-primary">Impor (paste)</a>
      </div>

      <form method="get" class="toolbar">
        <div class="field">
          <label for="q">Pencarian</label>
          <input id="q" name="q" class="input" value="{{ $filters['q'] ?? '' }}" placeholder="Cari heat/customer/item/operator">
        </div>

        <div class="field">
          <label for="hasil">Hasil</label>
          <select id="hasil" name="hasil" class="select">
            <option value="">Semua</option>
            @foreach(['OK','NG'] as $opt)
              <option value="{{ $opt }}" @selected(($filters['hasil'] ?? '')===$opt)>{{ $opt }}</option>
            @endforeach
          </select>
        </div>

        <div class="field">
          <label for="department">Department</label>
          <input id="department" name="department" class="input" value="{{ $filters['department'] ?? '' }}" placeholder="Department">
        </div>

        <div class="field" style="align-self:end">
          <button class="btn btn-ghost" type="submit">Terapkan</button>
        </div>
      </form>

      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Customer</th>
              <th>Heat #</th>
              <th>Item</th>
              <th>Hasil</th>
              <th>Operator</th>
              <th>Dept</th>
              <th>Catatan</th>
            </tr>
          </thead>
          <tbody>
            @forelse($records as $r)
              <tr>
                <td>{{ $r->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $r->customer }}</td>
                <td><code>{{ $r->heat_number }}</code></td>
                <td>{{ $r->item }}</td>
                <td>
                  <span class="badge {{ $r->hasil==='NG' ? 'ng' : 'ok' }}">{{ $r->hasil }}</span>
                </td>
                <td>{{ $r->operator }}</td>
                <td>{{ $r->department }}</td>
                <td>{{ $r->notes }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="8">Belum ada data.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-2">
        {{ $records->links() }}
      </div>
    </div>
  </div>
</x-app-layout>
