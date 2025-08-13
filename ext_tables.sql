
ALTER TABLE sys_file_metadata
  ADD COLUMN tx_cywatermark_watermark_source TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  ADD COLUMN tx_cywatermark_watermark_file INT(11) UNSIGNED DEFAULT NULL,
  ADD COLUMN tx_cywatermark_watermark_relative_size INT(3) UNSIGNED  DEFAULT '25',
  ADD COLUMN tx_cywatermark_watermark_position TINYINT(1) UNSIGNED NOT NULL DEFAULT '3';

ALTER TABLE sys_category
  ADD COLUMN tx_cywatermark_watermark_file INT(11) UNSIGNED DEFAULT NULL,
  ADD COLUMN tx_cywatermark_watermark_relative_size INT(3) UNSIGNED  DEFAULT '25',
  ADD COLUMN tx_cywatermark_watermark_position TINYINT(1) UNSIGNED NOT NULL DEFAULT '3';