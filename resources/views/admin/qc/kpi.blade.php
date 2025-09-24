<x-app-layout>
  <div class="container">
    <div class="card">
      <div class="header">
        <h1>KPI QC</h1>
        <a href="{{ route('admin.qc.index') }}" class="btn btn-ghost">Database QC</a>
      </div>

      <form method="get" class="toolbar">
        <div class="field">
          <label for="from">Dari tanggal</label>
          <input id="from" type="date" name="from" class="input" value="{{ $from }}">
        </div>
        <div class="field">
          <label for="to">Sampai tanggal</label>
          <input id="to" type="date" name="to" class="input" value="{{ $to }}">
        </div>
        <div class="field">
          <label for="department">Department</label>
          <input id="department" name="department" class="input" value="{{ $department }}" placeholder="mis. Netto">
        </div>
        <div class="field" style="align-self:end">
          <button class="btn btn-ghost" type="submit">Terapkan</button>
        </div>
      </form>

      {{-- Tabel rekap --}}
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Operator QC</th>
              <th>Total Qty</th>
              <th>Total Defects</th>
              <th>Defect Rate</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $r)
              <tr>
                <td>{{ $r->operator_name }}</td>
                <td>{{ number_format($r->total_qty) }}</td>
                <td>{{ number_format($r->total_defects) }}</td>
                <td>{{ $r->defect_rate }}%</td>
              </tr>
            @empty
              <tr><td colspan="4">Tidak ada data untuk rentang/per filter ini.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Bar chart ringan (inline SVG, tanpa JS) --}}
      @php
        $max = max(1, $chart->max('value') ?? 1);
        $barW = 28; $gap = 14;
        $width = ($barW + $gap) * max(1, $chart->count()) + 20;
        $height = 180;
      @endphp

      <div class="card mt-2">
        <h2 class="section-title">Top 10 Defects ({{ $from }} — {{ $to }})</h2>
        <svg width="{{ $width }}" height="{{ $height }}" role="img" aria-label="Top defects">
          @foreach($chart as $i => $c)
            @php
              $x = 10 + $i * ($barW + $gap);
              $h = (int) round(($c['value'] / $max) * ($height - 40));
              $y = $height - 20 - $h;
            @endphp
            <rect x="{{ $x }}" y="{{ $y }}" width="{{ $barW }}" height="{{ $h }}" fill="#6366f1" />
            <text x="{{ $x + $barW/2 }}" y="{{ $height - 6 }}" font-size="10" text-anchor="middle">{{ Str::limit($c['label'], 6, '') }}</text>
            <text x="{{ $x + $barW/2 }}" y="{{ $y - 4 }}" font-size="10" text-anchor="middle">{{ $c['value'] }}</text>
          @endforeach
        </svg>
      </div>
    </div>
  </div>
</x-app-layout>
