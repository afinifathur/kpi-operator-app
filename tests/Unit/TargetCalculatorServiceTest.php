<?php

namespace Tests\Unit;

use App\Models\Item;
use App\Models\ItemStandard;
use App\Models\Setting;
use App\Models\Shift;
use App\Services\TargetCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TargetCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Pastikan timezone konsisten
        config(['app.timezone' => 'Asia/Jakarta']);
    }

    private function makeItemWithStd(int $stdSec, string $aktifDari = '2025-01-01', ?string $aktifSampai = null): Item
    {
        $item = Item::create([
            'kode_barang' => 'ITM-001',
            'nama_barang' => 'Produk Uji',
            'size'        => '1/2"',
            'aisi'        => '304',
            'cust'        => 'TEST',
            'catatan'     => null,
        ]);

        ItemStandard::create([
            'item_id'               => $item->id,
            'std_time_sec_per_pcs'  => $stdSec,
            'aktif_dari'            => $aktifDari,
            'aktif_sampai'          => $aktifSampai,
        ]);

        return $item;
    }

    /** @test */
    public function hitung_on_target_100_persen()
    {
        $item = $this->makeItemWithStd(60); // 60 detik/pcs
        $svc  = new TargetCalculatorService();

        $calc = $svc->calculate([
            'item_id'           => $item->id,
            'jam_mulai'         => '2025-09-10 08:00:00',
            'jam_selesai'       => '2025-09-10 10:00:00', // 120 menit
            'qty_hasil'         => 120,                   // target = 120
            'timer_sec_per_pcs' => null,
            'shift_id'          => null,
        ]);

        $this->assertSame(120, $calc['target_qty']);
        $this->assertSame(100.00, $calc['pencapaian_pct']);
        $this->assertSame('ON_TARGET', $calc['kategori']);
        $this->assertSame('std', $calc['sumber_timer']);
        $this->assertSame('2025-09-10', $calc['tanggal']);
    }

    /** @test */
    public function hitung_lebih_dari_100_persen()
    {
        $item = $this->makeItemWithStd(60);
        $svc  = new TargetCalculatorService();

        $calc = $svc->calculate([
            'item_id'     => $item->id,
            'jam_mulai'   => '2025-09-10 08:00:00',
            'jam_selesai' => '2025-09-10 10:00:00', // 120 menit
            'qty_hasil'   => 130,                   // > target
        ]);

        $this->assertSame('LEBIH', $calc['kategori']);
        $this->assertGreaterThan(100.0, $calc['pencapaian_pct']);
        $this->assertSame('PERTAHANKAN', $calc['auto_flag']);
    }

    /** @test */
    public function hitung_mendekati_dengan_threshold_setting()
    {
        Setting::updateOrCreate(['key'=>'near_threshold_pct'], ['value'=>85]);

        $item = $this->makeItemWithStd(60);
        $svc  = new TargetCalculatorService();

        $calc = $svc->calculate([
            'item_id'     => $item->id,
            'jam_mulai'   => '2025-09-10 08:00:00',
            'jam_selesai' => '2025-09-10 10:00:00', // target 120
            'qty_hasil'   => 102,                   // 85%
        ]);

        $this->assertSame(120, $calc['target_qty']);
        $this->assertSame('MENDEKATI', $calc['kategori']);
        $this->assertSame('PERTAHANKAN', $calc['auto_flag']);
    }

    /** @test */
    public function hitung_jauh_kurang_dari_threshold()
    {
        // default near_threshold_pct = 80 (service fallback)
        $item = $this->makeItemWithStd(60);
        $svc  = new TargetCalculatorService();

        $calc = $svc->calculate([
            'item_id'     => $item->id,
            'jam_mulai'   => '2025-09-10 08:00:00',
            'jam_selesai' => '2025-09-10 10:00:00', // target 120
            'qty_hasil'   => 60,                    // 50%
        ]);

        $this->assertSame('JAUH', $calc['kategori']);
        $this->assertSame('BUTUH_PELATIHAN', $calc['auto_flag']);
    }

    /** @test */
    public function manual_timer_override_digunakan()
    {
        $item = $this->makeItemWithStd(60); // tapi kita override manual 30 detik/pcs
        $svc  = new TargetCalculatorService();

        $calc = $svc->calculate([
            'item_id'           => $item->id,
            'jam_mulai'         => '2025-09-10 08:00:00',
            'jam_selesai'       => '2025-09-10 09:00:00', // 60 menit = 3600 detik
            'qty_hasil'         => 100,
            'timer_sec_per_pcs' => 30,                    // override -> target = 120
        ]);

        $this->assertSame(120, $calc['target_qty']);
        $this->assertSame('manual', $calc['sumber_timer']);
    }

    /** @test */
    public function lintas_tengah_malam_durasi_benar()
    {
        $item = $this->makeItemWithStd(120); // 120 detik/pcs
        $svc  = new TargetCalculatorService();

        $calc = $svc->calculate([
            'item_id'     => $item->id,
            'jam_mulai'   => '2025-09-10 22:00:00',
            'jam_selesai' => '2025-09-10 02:00:00', // +1 hari -> 240 menit
            'qty_hasil'   => 110,
        ]);

        $this->assertSame(240, $calc['durasi_menit']);
        // target = floor(240*60/120) = floor(120) = 120
        $this->assertSame(120, $calc['target_qty']);
        $this->assertSame('2025-09-10', $calc['tanggal']); // mengikuti tanggal jam_mulai
    }

    /** @test */
    public function jika_shift_dipilih_maka_durasi_dipaksa_work_minutes()
    {
        $item  = $this->makeItemWithStd(60);
        $shift = Shift::create([
            'nama'          => 'Shift Pagi',
            'work_minutes'  => 420,
            'mulai_default' => '07:00:00',
            'selesai_default'=> '16:00:00',
        ]);

        $svc = new TargetCalculatorService();

        $calc = $svc->calculate([
            'item_id'     => $item->id,
            'shift_id'    => $shift->id,
            'jam_mulai'   => '2025-09-10 08:00:00',
            'jam_selesai' => '2025-09-10 11:20:00', // 200 menit sebenarnya, tapi dipaksa 420
            'qty_hasil'   => 350,
        ]);

        $this->assertSame(420, $calc['durasi_menit']);
        $this->assertSame(420, $calc['target_qty']); // std 60 detik -> 420 pcs
        $this->assertSame('MENDEKATI', $calc['kategori']); // 350/420 ≈ 83.33%
    }

    /** @test */
    public function error_jika_standar_tidak_ditemukan()
    {
        $item = Item::create([
            'kode_barang' => 'ITM-404',
            'nama_barang' => 'Produk Kosong',
            'size'        => null,
            'aisi'        => null,
            'cust'        => null,
            'catatan'     => null,
        ]);

        $svc = new TargetCalculatorService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Standar waktu item tidak ditemukan');

        $svc->calculate([
            'item_id'     => $item->id,
            'jam_mulai'   => '2025-09-10 08:00:00',
            'jam_selesai' => '2025-09-10 09:00:00',
            'qty_hasil'   => 10,
        ]);
    }

    /** @test */
    public function error_jika_std_time_tidak_valid()
    {
        $item = $this->makeItemWithStd(0); // tidak valid
        $svc  = new TargetCalculatorService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Waktu per pcs harus > 0 detik.');

        $svc->calculate([
            'item_id'     => $item->id,
            'jam_mulai'   => '2025-09-10 08:00:00',
            'jam_selesai' => '2025-09-10 09:00:00',
            'qty_hasil'   => 10,
        ]);
    }
}
