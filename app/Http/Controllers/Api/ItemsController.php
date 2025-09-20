<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;

class ItemsController extends Controller
{
    public function show(string $kode)
    {
        $item = Item::where('kode_barang',$kode)->firstOrFail();
        $std = optional($item->activeStandardAt(now()->toDateString()))->std_time_sec_per_pcs;
        return response()->json([
            'kode_barang' => $item->kode_barang,
            'nama_barang' => $item->nama_barang,
            'size' => $item->size,
            'aisi' => $item->aisi,
            'cust' => $item->cust,
            'catatan' => $item->catatan,
            'std_time_sec_per_pcs' => $std,
        ]);
    }
}
