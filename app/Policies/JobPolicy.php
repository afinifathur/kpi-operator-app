<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    // Semua user ber-role bisa melihat list
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'admin_produksi',
            'admin_produksi_fitting',
            'admin_produksi_flange',
            'supervisor_qa',
            'hr',
            'viewer',
        ]);
    }

    // Lihat 1 record (tetap hormati batas departemen untuk admin_* khusus)
    public function view(User $user, Job $job): bool
    {
        return $this->viewAny($user) && $this->canAccessDept($user, $job);
    }

    // Buat job: hanya admin produksi (umum atau khusus departemen)
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'admin_produksi',
            'admin_produksi_fitting',
            'admin_produksi_flange',
        ]);
    }

    // Edit job: admin produksi (umum/khusus) + supervisor_qa (koreksi entri)
    public function update(User $user, Job $job): bool
    {
        return $user->hasAnyRole([
                'admin_produksi',
                'admin_produksi_fitting',
                'admin_produksi_flange',
                'supervisor_qa',
            ]) && $this->canAccessDept($user, $job);
    }

    // Hapus job: hanya admin produksi (umum/khusus)
    public function delete(User $user, Job $job): bool
    {
        return $user->hasAnyRole([
                'admin_produksi',
                'admin_produksi_fitting',
                'admin_produksi_flange',
            ]) && $this->canAccessDept($user, $job);
    }

    // Helper: batasi akses sesuai departemen operator pada job
    private function canAccessDept(User $user, Job $job): bool
    {
        // muat operator jika belum (hindari null)
        $opDept = optional($job->operator)->departemen;

        if ($user->hasRole('admin_produksi_fitting')) {
            return $opDept === 'FITTING';
        }
        if ($user->hasRole('admin_produksi_flange')) {
            return $opDept === 'FLANGE';
        }
        // admin_produksi umum, supervisor_qa, hr, viewer: tidak dibatasi departemen
        return true;
    }
}
