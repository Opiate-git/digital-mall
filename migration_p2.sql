-- P2: migration_p0.sql ve migration_p1.sql'den SONRA, BİR KEZ çalıştır.

-- 1) Resimler ürün ADINA değil ürün ID'sine bağlansın (aynı isimli ürünler karışmasın)
ALTER TABLE `pictures` ADD COLUMN `product_id` INT(11) NULL AFTER `id`;
UPDATE `pictures` pi JOIN `products` p ON p.name = pi.product_name SET pi.product_id = p.id;
DELETE FROM `pictures` WHERE `product_id` IS NULL;            -- sahipsiz resim satırları (ör. "145")
ALTER TABLE `pictures`
  MODIFY `product_id` INT(11) NOT NULL,
  DROP COLUMN `product_name`,
  ADD KEY `idx_pictures_product` (`product_id`),
  ADD CONSTRAINT `fk_pictures_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

-- 2) Eski 'Accepted' durumları manager panelinde ve gelirde görünsün
UPDATE `orders` SET `status` = 'Approved' WHERE `status` = 'Accepted';

-- 3) Ürün adlarındaki baştaki/sondaki boşluklar
UPDATE `products` SET `name` = TRIM(`name`);
