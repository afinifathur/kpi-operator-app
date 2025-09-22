<x-app-layout>
  <div style="padding:16px;">
    <h1>QC Records</h1>

    <form method="get" style="margin-bottom:8px;">
      <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari: heat/customer/item/operator" />
      <select name="hasil">
        <option value="">Hasil (semua)</option>
        @foreach(['OK','NG'] as $opt)
          <option value="{{ $opt }}" @selected(($filters['hasil'] ?? '')===$opt)>{{ $opt }}</option>
        @endforeach
      </select>
      <input name="department" value="{{ $filters['department'] ?? '' }}" placeholder="Department" />
      <button type="submit">Filter</button>
      <a href="{{ route('admin.qc.import') }}" style="margin-left:8px;">Impor (paste)</a>
    </form>

    <div style="overflow-x:auto">
      <table style="border-collapse:collapse;width:100%;">
        <thead>
          <tr>
            <th>Tanggal</th><th>Customer</th><th>Heat #</th><th>Item</th>
            <th>Hasil</th><th>Operator</th><th>Dept</th><th>Catatan</th>
          </tr>
        </thead>
        <tbody>
          @forelse($records as $r)
            <tr>
              <td>{{ $r->created_at->format('Y-m-d H:i') }}</td>
              <td>{{ $r->customer }}</td>
              <td><code>{{ $r->heat_number }}</code></td>
              <td>{{ $r->item }}</td>
              <td style="font-weight:bold;{{ $r->hasil==='NG' ? 'color:#b00020' : 'color:#0b7a2a' }}">{{ $r->hasil }}</td>
              <td>{{ $r->operator }}</td>
              <td>{{ $r->department }}</td>
              <td>{{ $r->notes }}</td>
            </tr>
          @empty
            <tr><td colspan="8" style="text-align:center">Belum ada data.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="margin-top:10px;">
      {{ $records->links() }}
    </div>
  </div>
</x-app-layout>
