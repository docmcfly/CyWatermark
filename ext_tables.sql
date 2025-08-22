CREATE TABLE `sys_file_metadata` (
  tx_cywatermark_watermark_source TINYINT(1) UNSIGNED DEFAULT 1 NOT NULL,
  tx_cywatermark_watermark_file INT(11) UNSIGNED DEFAULT NULL,
  tx_cywatermark_watermark_relative_size INT(3) UNSIGNED DEFAULT 25,
  tx_cywatermark_watermark_position TINYINT(1) UNSIGNED NOT NULL DEFAULT 3,
);

CREATE TABLE `sys_category` (
  tx_cywatermark_watermark_file INT(11) UNSIGNED DEFAULT NULL,
  tx_cywatermark_watermark_relative_size INT(3) UNSIGNED DEFAULT 25,
  tx_cywatermark_watermark_position TINYINT(1) UNSIGNED NOT NULL DEFAULT 3,
);