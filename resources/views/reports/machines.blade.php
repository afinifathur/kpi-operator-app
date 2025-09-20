@extends('layouts.app')

@section('content')
<h3>Laporan Produktivitas Mesin</h3>
<form method="get" class="grid">
    <label>Dari <input type="date" name="from" value="{{ $from }}"></label>
    <label>Sampai <input type="date" name="to" value="{{ $to }}"></label>
    <button type="submit">Filter</button>
</form>
<table>
    <thead><tr><th>No Mesin</th><th>Target Total</th><th>Qty Total</th><th>Avg %</th></tr></thead>
    <tbody>
        @foreach($rows as $r)
        <tr>
            <td>{{ $r->no_mesin }}</td>
            <td>{{ $r->target_total }}</td>
            <td>{{ $r->qty_total }}</td>
            <td>{{ number_format($r->avg_pct,2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
