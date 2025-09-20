-- Agregat harian per operator (weighted %: total_qty / target_qty * 100)
DROP VIEW IF EXISTS v_scorecard_daily;
CREATE VIEW v_scorecard_daily AS
SELECT
  j.tanggal,
  j.operator_id,
  o.no_induk,
  o.nama AS operator_nama,

  SUM(je.target_qty)               AS target_qty,
  SUM(j.qty_hasil)                 AS total_qty,
  CASE WHEN COALESCE(SUM(je.target_qty),0)=0
       THEN 0
       ELSE ROUND(SUM(j.qty_hasil)/SUM(je.target_qty)*100, 2)
  END                              AS pencapaian_pct,

  SUM(CASE WHEN je.kategori='ON_TARGET'  THEN 1 ELSE 0 END) AS hit_on_target,
  SUM(CASE WHEN je.kategori='MENDEKATI'  THEN 1 ELSE 0 END) AS hit_mendekati,
  SUM(CASE WHEN je.kategori='JAUH'       THEN 1 ELSE 0 END) AS hit_jauh,
  SUM(CASE WHEN je.kategori='LEBIH'      THEN 1 ELSE 0 END) AS hit_lebih
FROM jobs j
JOIN operators o        ON o.id = j.operator_id
LEFT JOIN job_evaluations je ON je.job_id = j.id
GROUP BY j.tanggal, j.operator_id, o.no_induk, o.nama;
