-- ============================================================
--  Run once in phpMyAdmin on play2review_db
--  Adds category-level progress columns to the `users` table
-- ============================================================

ALTER TABLE `users`
  -- ENGLISH
  ADD COLUMN IF NOT EXISTS `english_grammar_level`       INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `english_vocabulary_level`    INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `english_reading_level`       INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `english_literature_level`    INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `english_writing_level`       INT DEFAULT 0,

  -- MATH
  ADD COLUMN IF NOT EXISTS `math_algebra_level`          INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `math_geometry_level`         INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `math_statistics_level`       INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `math_probability_level`      INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `math_functions_level`        INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `math_wordproblems_level`     INT DEFAULT 0,

  -- FILIPINO
  ADD COLUMN IF NOT EXISTS `filipino_gramatika_level`    INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `filipino_panitikan_level`    INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `filipino_paguunawa_level`    INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `filipino_talasalitaan_level` INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `filipino_wika_level`         INT DEFAULT 0,

  -- AP
  ADD COLUMN IF NOT EXISTS `ap_ekonomiks_level`          INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `ap_kasaysayan_level`         INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `ap_kontemporaryo_level`      INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `ap_heograpiya_level`         INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `ap_pamahalaan_level`         INT DEFAULT 0,

  -- SCIENCE
  ADD COLUMN IF NOT EXISTS `science_biology_level`       INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `science_chemistry_level`     INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `science_physics_level`       INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `science_earthscience_lev