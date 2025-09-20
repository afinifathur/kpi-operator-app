<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditableObserver
{
    protected function log(string $aksi, Model $model): void
    {
        AuditLog::create([
            'user_id' => optional(Auth::user())->id,
            'aksi' => $aksi,
            'tabel' => $model->getTable(),
            'pk' => (string)$model->getKey(),
            'before' => $aksi==='created' ? null : json_encode($model->getOriginal()),
            'after' => json_encode($model->getAttributes()),
            'timestamp' => now(),
        ]);
    }
    public function created(Model $model){ $this->log('created',$model); }
    public function updated(Model $model){ $this->log('updated',$model); }
    public function deleted(Model $model){ $this->log('deleted',$model); }
}
