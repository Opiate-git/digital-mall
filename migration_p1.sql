-- P1: fiyatı FLOAT yerine kuruş hassasiyetli DECIMAL olarak tut. migration_p0.sql'den SONRA, BİR KEZ çalıştır.
ALTER TABLE `products` MODIFY `price` DECIMAL(10,2) NOT NULL;
