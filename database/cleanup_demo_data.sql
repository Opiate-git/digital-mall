-- Clean demo data for the public portfolio version. Safe to run more than once.
SET NAMES utf8mb4;

-- Test / unused categories
DELETE FROM categories WHERE name IN ('123', 'hj', 'defter') OR shop_name = 'test mağza';

-- Merge album categories, English names
UPDATE products   SET category = 'album' WHERE category IN ('albüm', 'albüm2');
DELETE FROM categories WHERE name = 'albüm2';
UPDATE categories SET name = 'album' WHERE name = 'albüm';
UPDATE products   SET category = 'guitar' WHERE category = 'gitar';
UPDATE categories SET name = 'guitar' WHERE name = 'gitar';
UPDATE products   SET category = 'pick' WHERE category = 'pena';
UPDATE categories SET name = 'pick' WHERE name = 'pena';
UPDATE products   SET category = 'case' WHERE id IN (43, 44);
INSERT INTO categories (name, shop_name)
    SELECT 'case', 'donmadanmüzik.net' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'case' AND shop_name = 'donmadanmüzik.net');

-- Product names and descriptions
UPDATE products SET name = 'Pencil',            description = 'Smooth HB pencil for everyday writing.' WHERE id = 31;
UPDATE products SET name = 'Notebook',          description = 'A4 squared notebook, 3 sections, 120 pages.' WHERE id = 32;
UPDATE products SET name = 'Lateralus (Vinyl)', description = 'Second-hand vinyl in mint condition.' WHERE id = 33;
UPDATE products SET name = 'Tool Album (Vinyl)', description = 'Second-hand vinyl, no scratches.' WHERE id = 34;
UPDATE products SET name = 'High-End Guitar',   description = 'Premium electric guitar with a rich, warm tone.' WHERE id = 35;
UPDATE products SET name = 'Guitar Pick',       description = 'Durable medium-gauge pick.' WHERE id = 36;
UPDATE products SET name = 'Jazz Bass',         description = '4-string bass with a classic jazz sound.' WHERE id = 38;
UPDATE products SET name = 'Guitar Hero Controller', description = 'Guitar controller for rhythm games.' WHERE id = 39;
UPDATE products SET name = 'Electric Guitar',   description = 'Solid-body electric guitar for beginners and pros.' WHERE id = 40;
UPDATE products SET name = 'Bass Guitar',       description = '4-string electric bass.' WHERE id = 41;
UPDATE products SET name = 'Effects Processor', description = 'Multi-effects pedal for guitar and bass.' WHERE id = 42;
UPDATE products SET name = '6-Guitar Case',     description = 'Padded case that holds six guitars.' WHERE id = 43;
UPDATE products SET name = 'Guitar Case',       description = 'Padded gig bag with shoulder straps.' WHERE id = 44;
UPDATE products SET name = 'Pencil Case',       description = 'Zippered pencil case with two pockets.' WHERE id = 45;
UPDATE products SET name = 'Mono Eraser',       description = 'Clean-erasing eraser that leaves little dust.' WHERE id = 46;
UPDATE products SET name = 'Posca Marker',      description = 'Water-based paint marker for any surface.' WHERE id = 47;
UPDATE products SET name = 'Minions Bag',       description = 'Kids school bag with a Minions print.' WHERE id = 48;
UPDATE products SET name = 'Sketchbook',        description = 'A5 sketchbook with thick drawing paper.' WHERE id = 49;
UPDATE products SET name = 'Album (Vinyl)',     description = 'Second-hand vinyl in good condition.' WHERE id = 50;
UPDATE products SET name = 'T-Shirt',           description = 'Cotton t-shirt with a printed design.' WHERE id = 51;
UPDATE products SET name = 'Cool Hat',          description = 'Adjustable cap, one size.' WHERE id = 52;
UPDATE products SET name = 'Jacket',            description = 'Light jacket for spring and autumn.' WHERE id = 53;
UPDATE products SET name = 'Hoodie',            description = 'Warm cotton hoodie with front pocket.' WHERE id = 54;
UPDATE products SET name = 'Tee',               description = 'Basic tee, regular fit.' WHERE id = 55;

-- Order created by an old price-tampering test (1,000,000 total)
DELETE FROM orders WHERE id = 18;
