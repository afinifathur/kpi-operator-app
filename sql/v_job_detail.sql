-- Detail job + evaluasi + master terkait (durasi dukung lintas tengah malam)
DROP VIEW IF EXISTS v_job_detail;
CREATE VIEW v_job_detail AS
SELECT
  j.id AS job_id,
  j.tanggal,
  j.operator_id,
  o.no_induk,
  o.nama            AS operator_nama,
  o.departemen      AS operator_departemen,

  j.item_id,
  i.kode_barang,
  i.nama_barang,
  i.size,
  i.aisi,
  i.cust,

  j.machine_id,
  m.no_mesin,
  m.departemen      AS mesin_departemen,

  j.shift_id,
  s.nama            AS shift_nama,
  s.work_minutes,

  j.jam_mulai,
  j.jam_selesai,
  TIMESTAMPDIFF(
    MINUTE,
    j.jam_mulai,
    IF(j.jam_selesai <= j.jam_mulai, DATE_ADD(j.jam_selesai, INTERVAL 1 DAY), j.jam_selesai)
  )                 AS durasi_menit,

  j.qty_hasil,
  j.timer_sec_per_pcs,
  j.sumber_timer,
  j.catatan,

  je.target_qty,
  je.pencapaian_pct,
  je.kategori,
  je.auto_flag
FROM jobs j
JOIN operators o      ON o.id = j.operator_id
JOIN items i          ON i.id = j.item_id
LEFT JOIN machines m  ON m.id = j.machine_id
LEFT JOIN shifts s    ON s.id = j.shift_id
LEFT JOIN job_evaluations je ON je.job_id = j.id;
