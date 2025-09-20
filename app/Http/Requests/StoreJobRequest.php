<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'tanggal' => ['required','date'],
            'operator_id' => ['required','exists:operators,id'],
            'item_id' => ['required','exists:items,id'],
            'machine_id' => ['required','exists:machines,id'],
            'jam_mulai' => ['required','date'],
            'jam_selesai' => ['required','date'],
            'qty_hasil' => ['required','integer','min:0'],
            'timer_sec_per_pcs' => ['nullable','integer','min:1'],
            'sumber_timer' => ['nullable','in:std,manual'],
            'catatan' => ['nullable','string','max:500'],
            'use_shift_minutes' => ['nullable','boolean'],
            'shift_id' => ['nullable','exists:shifts,id'],
        ];
    }
}
