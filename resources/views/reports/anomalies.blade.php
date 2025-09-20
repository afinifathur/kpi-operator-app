@extends('layouts.app')

@section('content')
<h3>Laporan Anomali</h3>
<form method="get" class="grid">
    <label>Dari <input type="date" name="from" value="{{ $from }}"></label>
    <label>Sampai <input type="date" name="to" value="{{ $to }}"></label>
    <button type="submit">Filter</button>
</form>
<table>
    <thead><tr><th>Tanggal</th><th>Job</th><th>Operator</th><th>Item</th><th>Mesin</th><th>Qty</th><th>Target</th><th>%</th><th>Kategori</th></tr></thead>
    <tbody>
        @foreach($rows as $r)
        <tr>
            <td>{{ $r->tanggal }}</td>
            <td>{{ $r->job_id }}</td>
            <td>{{ $r->operator_id }}</td>
            <td>{{ $r->item_id }}</td>
            <td>{{ $r->machine_id }}</td>
            <td>{{ $r->qty_hasil }}</td>
            <td>{{ $r->target_qty }}</td>
            <td>{{ number_format($r->pencapaian_pct,2) }}</td>
            <td>{{ $r->kategori }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
