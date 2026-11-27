-- P0 düzeltmeleri için şema değişikliği. phpMyAdmin'de products_db üzerinde BİR KEZ çalıştır.
-- Mevcut manager'lar onaylı sayılır; yeni kayıt olan manager'lar admin onayı bekler.
ALTER TABLE `users` ADD COLUMN `approved` TINYINT(1) NOT NULL DEFAULT 1;
