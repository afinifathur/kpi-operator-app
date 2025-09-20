@extends('layouts.app')

@section('content')
<h3>Scorecard HR</h3>
<form method="get" class="grid">
    <label>Dari <input type="date" name="from" value="{{ $from }}"></label>
    <label>Sampai <input type="date" name="to" value="{{ $to }}"></label>
    <label>Operator
        <select name="operator_id">
            <option value="">--Semua--</option>
            @foreach($operators as $o)
            <option value="{{ $o->id }}" @selected($op==$o->id)>{{ $o->no_induk }} - {{ $o->nama }}</option>
            @endforeach
        </select>
    </label>
    <button type="submit">Terapkan</button>
</form>

<p>Rata-rata %: <strong>{{ number_format($avg ?? 0,2) }}%</strong></p>
<table>
    <thead><tr><th>Tanggal</th><th>Operator ID</th><th>Target</th><th>Qty</th><th>%</th><th>ON</th><th>Mendekati</th><th>Jauh</th></tr></thead>
    <tbody>
        @foreach($data as $row)
        <tr>
            <td>{{ $row->tanggal }}</td>
            <td>{{ $row->operator_id }}</td>
            <td>{{ $row->target_qty }}</td>
            <td>{{ $row->total_qty }}</td>
            <td>{{ number_format($row->pencapaian_pct,2) }}</td>
            <td>{{ $row->hit_on }}</td>
            <td>{{ $row->hit_mendekati }}</td>
            <td>{{ $row->hit_jauh }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<form method="get" action="{{ route('hr.scorecard.export') }}">
    <input type="hidden" name="from" value="{{ $from }}">
    <input type="hidden" name="to" value="{{ $to }}">
    <input type="hidden" name="operator_id" value="{{ $op }}">
    <button type="submit">Export XLSX</button>
</form>
@endsection
