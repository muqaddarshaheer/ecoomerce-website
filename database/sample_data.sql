-- Optional demonstration products and staff.
-- Import database.sql first. Import this file once on that fresh database.
USE paperlane_shop;

INSERT INTO users (role, full_name, email, password_hash, phone, status) VALUES
('employee', 'Ayesha Khan', 'employee@paperlane.local', '$2y$10$RCiFlPSMFdNBdlY9seM/MeLIzYfCeBRNZfqobTQ0Mu29u1lBC1hzG', '03001234567', 'active'),
('customer', 'Neha Shah', 'customer@paperlane.local', '$2y$10$yJQMzRg6C1MMZXkm4OshRO9DPWhu6HevTOXa5v46zQYensh/gtf7W', '03009876543', 'active');

INSERT INTO employees (user_id, employee_code, designation)
SELECT id, 'EMP0001', 'Dispatch executive' FROM users WHERE email = 'employee@paperlane.local';

INSERT INTO customers (user_id)
SELECT id FROM users WHERE email = 'customer@paperlane.local';

INSERT INTO customer_addresses (customer_id, full_name, phone, address_line, city, state, postal_code, is_default)
SELECT c.id, 'Neha Shah', '03009876543', '18 Lakeview Road', 'Karachi', 'Sindh', '75500', 1
FROM customers c JOIN users u ON u.id = c.user_id WHERE u.email = 'customer@paperlane.local';

INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0100001', id, 'Gold Ribbon Gift Box', 'A pink gift box finished with a gold ribbon and a few gold hearts. The box is sold on its own, ready to fill.', 280.00, 'No warranty', 40, 0, 'active' FROM categories WHERE code_prefix = '01';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0100002', id, 'Kraft and Berry Gifts', 'A group of wrapped boxes in kraft, red and white, tied with cotton string and a small berry sprig.', 950.00, 'No warranty', 18, 0, 'active' FROM categories WHERE code_prefix = '01';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0100003', id, 'Blue Ribbon Gift Stack', 'Three stacked gift boxes in white and blue, tied with a satin ribbon. Suitable for a desk gift or a hamper.', 1250.00, 'No warranty', 15, 0, 'active' FROM categories WHERE code_prefix = '01';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0200001', id, 'Honey Plush Bear', 'A soft honey-coloured bear sold with two small gift boxes. It sits on a desk or goes straight into a hamper.', 890.00, 'Stitching checked for 30 days', 16, 0, 'active' FROM categories WHERE code_prefix = '02';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0200002', id, 'Campus File Box', 'A pale blue file box with labelled paper folders inside. It holds a normal stack of A4 sheets.', 420.00, 'No warranty', 50, 0, 'active' FROM categories WHERE code_prefix = '02';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0200003', id, 'Linen Notebook', '192 ruled pages in a stitched linen cover. Plain enough for class notes or a journal.', 640.00, 'No warranty', 28, 0, 'active' FROM categories WHERE code_prefix = '02';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0200004', id, 'Ink Pen Duo', 'Two smooth ballpoint pens packed in a small gift sleeve.', 560.00, '3 months', 4, 0, 'active' FROM categories WHERE code_prefix = '02';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0300001', id, 'Tan Shoulder Bag', 'A structured shoulder bag with an inner zip pocket and an adjustable strap.', 3450.00, '6 months against manufacturing defects', 8, 0, 'active' FROM categories WHERE code_prefix = '03';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0300002', id, 'Slim Everyday Wallet', 'Eight card slots, one note pocket and a snap closure. It fits a small bag.', 1480.00, '6 months against manufacturing defects', 14, 0, 'active' FROM categories WHERE code_prefix = '03';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400001', id, 'Lip and Blush Set', 'A pink lip colour and a matching blush compact for daily use. Patch-test first if your skin is sensitive.', 760.00, 'No warranty. Sealed for hygiene.', 22, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400002', id, 'Amber Dropper Bottle', 'A small amber glass bottle with a dropper top. Use it for a light fragrance or a facial oil.', 2150.00, 'No warranty', 12, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400003', id, 'Women Check Shawl', 'A lightweight check shawl in brown, cream and rust. Wear it, or fold it around a gift.', 990.00, 'No warranty', 16, 0, 'active' FROM categories WHERE code_prefix = '04';

INSERT INTO product_images (product_id, file_name, is_primary)
SELECT id, 'birthday-card.jpg', 1 FROM products WHERE product_code = '0100001';
INSERT INTO product_images (product_id, file_name, is_primary)
SELECT id, 'wedding-card.jpg', 1 FROM products WHERE product_code = '0100002';
INSERT INTO product_images (product_id, file_name, is_primary)
SELECT id, 'gift-box.jpg', 1 FROM products WHERE product_code = '0100003';
INSERT INTO product_images (product_id, file_name, is_primary)
SELECT id, 'doll.jpg', 1 FROM products WHERE product_code = '0200001';
INSERT INTO product_images (product_id, file_name, is_primary)
SELECT id, 'files.jpg', 1 FROM products WHERE product_code = '0200002';
INSERT INTO product_images (product_id, file_name, is_primary)
SELECT id, 'notebook.jpg', 1 FROM products WHERE product_code = '0200003';
INSERT INTO product_images (product_id, file_name, is_primary)
SELECT id, 'pens.jpg', 1 FROM products WHERE product_code = '0200004';
INSERT INTO product_images (product_id, file_name, is_primary)
SELECT id, 'handbag.jpg', 1 FROM products WHERE product_code = '0300001';
INSERT INTO product_images (product_id, file_name, is_primary)
SELECT id, 'wallet.jpg', 1 FROM products WHERE product_code = '0300002';
INSERT INTO product_images (product_id, file_name, is_primary)
SELECT id, 'lipstick.jpg', 1 FROM products WHERE product_code = '0400001';
INSERT INTO product_images (product_id, file_name, is_primary)
SELECT id, 'perfume.jpg', 1 FROM products WHERE product_code = '0400002';
INSERT INTO product_images (product_id, file_name, is_primary)
SELECT id, 'scarf.jpg', 1 FROM products WHERE product_code = '0400003';

INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0100004', id, 'Red Bow Gift Set', 'Several small boxes wrapped in red, kraft and white, each finished with a bow. Sold as one gift set.', 640.00, 'No warranty', 24, 1, 'active' FROM categories WHERE code_prefix = '01';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0100005', id, 'Candy Cane Gift Box', 'A kraft box printed with candy canes and tied with baker’s twine. The box is empty and ready to fill.', 520.00, 'No warranty', 20, 0, 'active' FROM categories WHERE code_prefix = '01';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0100006', id, 'Black and Gold Gift Boxes', 'Matte black boxes with gold satin bows, in a few sizes. Useful for jewellery or a small accessory.', 880.00, 'No warranty', 16, 0, 'active' FROM categories WHERE code_prefix = '01';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0100007', id, 'Glass Gift Candle', 'A cream candle in a clear glass, packed as a gift. Burn it on a stable surface and never leave it unattended.', 740.00, 'No warranty', 18, 1, 'active' FROM categories WHERE code_prefix = '01';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0100008', id, 'Gallery Frame Set', 'A mixed set of small and medium frames for prints, notes and photographs. Frames only; prints are not included.', 1650.00, 'No warranty', 10, 0, 'active' FROM categories WHERE code_prefix = '01';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0100009', id, 'Kraft Box with Pink Ribbon', 'A plain kraft box with a long pink satin ribbon. Simple enough for a birthday or a thank-you.', 360.00, 'No warranty', 30, 0, 'active' FROM categories WHERE code_prefix = '01';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0100010', id, 'Mixed Gift Pile', 'A small pile of wrapped boxes in red, white and kraft. Sold together as one decorative gift bundle.', 1100.00, 'No warranty', 12, 0, 'active' FROM categories WHERE code_prefix = '01';

INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0200005', id, 'Colour Pencil Row', 'A row of sharpened colour pencils for school drawing and card making. Keep the points covered in a pencil case.', 480.00, 'No warranty', 36, 1, 'active' FROM categories WHERE code_prefix = '02';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0200006', id, 'Spiral Desk Notebook', 'An open spiral notebook with blank pages and a slim black pen beside it. The pen is included.', 390.00, 'No warranty', 32, 0, 'active' FROM categories WHERE code_prefix = '02';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0200007', id, 'Fountain Pen Pad', 'A lined pad with a silver fountain pen. The pen writes with a smooth nib and is refillable.', 720.00, '3 months on the pen', 14, 0, 'active' FROM categories WHERE code_prefix = '02';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0200008', id, 'Desk Writing Set', 'A blue pen with loose sheets and a small notebook, photographed as a working desk set. Sheets and pen are included.', 450.00, 'No warranty', 22, 0, 'active' FROM categories WHERE code_prefix = '02';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0200009', id, 'Wooden Alphabet Blocks', 'Painted wooden blocks with letters, for a desk or a child’s shelf. Wipe them with a dry cloth.', 980.00, 'No warranty', 11, 1, 'active' FROM categories WHERE code_prefix = '02';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0200010', id, 'Ruled Notes Book', 'A ruled notebook open to a page marked Notes, with a fountain pen resting on it.', 540.00, 'No warranty', 26, 0, 'active' FROM categories WHERE code_prefix = '02';

INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0300003', id, 'Teal Top Handle', 'A teal leather top-handle bag with a gold clasp. The strap is short, so it is carried in the hand or on the forearm.', 4200.00, '6 months against manufacturing defects', 7, 1, 'active' FROM categories WHERE code_prefix = '03';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0300004', id, 'Floral Chain Bag', 'A white chain bag printed with blue flowers and finished with small studs. It hangs from a long chain strap.', 2890.00, '6 months against manufacturing defects', 9, 1, 'active' FROM categories WHERE code_prefix = '03';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0300005', id, 'Yellow Mini Backpack', 'A small yellow backpack with playful patches. Light enough for a day out or a college bag.', 1980.00, '6 months against manufacturing defects', 10, 0, 'active' FROM categories WHERE code_prefix = '03';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0300006', id, 'Suede Top Handle', 'A warm brown suede bag with two short handles and silver hardware. Keep it dry and brush the nap gently.', 3650.00, '6 months against manufacturing defects', 6, 0, 'active' FROM categories WHERE code_prefix = '03';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0300007', id, 'Stripe Chain Bag', 'A pale pink chain bag with yellow and white stripes across the flap. The chain is detachable.', 2450.00, '6 months against manufacturing defects', 8, 0, 'active' FROM categories WHERE code_prefix = '03';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0300008', id, 'Black Satchel', 'A compact black satchel with two buckle straps and a long shoulder strap. It holds a phone, a wallet and a small notebook.', 3100.00, '6 months against manufacturing defects', 9, 0, 'active' FROM categories WHERE code_prefix = '03';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0300009', id, 'Grey Daypack', 'A plain grey backpack with one front pocket. Suitable for files, a bottle and a light jacket.', 2200.00, '6 months against manufacturing defects', 13, 0, 'active' FROM categories WHERE code_prefix = '03';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0300010', id, 'Tan Messenger', 'A tan leather messenger with dark straps and brass rings. The flap closes over an A4 folder.', 3980.00, '6 months against manufacturing defects', 5, 0, 'active' FROM categories WHERE code_prefix = '03';

INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400004', id, 'Blue Mascara', 'A single blue mascara tube for everyday use. Replace it within three months of opening.', 540.00, 'No warranty. Sealed for hygiene.', 28, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400005', id, 'Amber Bottle Trio', 'Three amber glass bottles: a pump, a spray and a dropper. Bottles are empty and ready to fill.', 860.00, 'No warranty', 15, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400006', id, 'Pearl Necklace Box', 'A strand of pearls presented in a red gift box. Store it flat so the strand does not kink.', 4500.00, '6 months against clasp defects', 6, 1, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400007', id, 'Blue Stone Earrings', 'A pair of drop earrings with a blue centre stone, shown on a green leaf. The posts are for pierced ears.', 1890.00, '6 months against stone loss', 12, 1, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400008', id, 'Gloss Nail Set', 'A glossy black and tortoiseshell nail look. The set is a pair of polish shades for home use.', 620.00, 'No warranty. Sealed for hygiene.', 20, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400009', id, 'Moon Necklace', 'Two fine chains, one with a blue stone and one with a moon pendant. They can be worn together or apart.', 1560.00, '6 months against clasp defects', 14, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400010', id, 'Women Pearl Bracelet', 'A pearl bracelet worn with a matching necklace. The bracelet closes with a small clasp.', 2100.00, '6 months against clasp defects', 9, 0, 'active' FROM categories WHERE code_prefix = '04';

INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'gifts-red.jpg', 1 FROM products WHERE product_code = '0100004';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'gift-candy.jpg', 1 FROM products WHERE product_code = '0100005';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'gifts-black.jpg', 1 FROM products WHERE product_code = '0100006';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'gift-candle.jpg', 1 FROM products WHERE product_code = '0100007';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'gift-frames.jpg', 1 FROM products WHERE product_code = '0100008';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'gift-kraft.jpg', 1 FROM products WHERE product_code = '0100009';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'gifts-mixed.jpg', 1 FROM products WHERE product_code = '0100010';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'pencils.jpg', 1 FROM products WHERE product_code = '0200005';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'notebook-spiral.jpg', 1 FROM products WHERE product_code = '0200006';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'pen-pad.jpg', 1 FROM products WHERE product_code = '0200007';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'writing-set.jpg', 1 FROM products WHERE product_code = '0200008';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'alphabet-blocks.jpg', 1 FROM products WHERE product_code = '0200009';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'notes-book.jpg', 1 FROM products WHERE product_code = '0200010';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'bag-teal.jpg', 1 FROM products WHERE product_code = '0300003';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'bag-floral.jpg', 1 FROM products WHERE product_code = '0300004';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'bag-yellow.jpg', 1 FROM products WHERE product_code = '0300005';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'bag-suede.jpg', 1 FROM products WHERE product_code = '0300006';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'bag-stripe.jpg', 1 FROM products WHERE product_code = '0300007';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'bag-satchel.jpg', 1 FROM products WHERE product_code = '0300008';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'bag-daypack.jpg', 1 FROM products WHERE product_code = '0300009';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'bag-messenger.jpg', 1 FROM products WHERE product_code = '0300010';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'mascara.jpg', 1 FROM products WHERE product_code = '0400004';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'bottles.jpg', 1 FROM products WHERE product_code = '0400005';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'pearls.jpg', 1 FROM products WHERE product_code = '0400006';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'earrings.jpg', 1 FROM products WHERE product_code = '0400007';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'nails.jpg', 1 FROM products WHERE product_code = '0400008';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'necklace-moon.jpg', 1 FROM products WHERE product_code = '0400009';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'bracelet.jpg', 1 FROM products WHERE product_code = '0400010';

INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400011', id, 'Women Layered Necklace', 'A gold layered necklace with a blue stone and a moon pendant. The two chains can be worn together.', 2680.00, '6 months against clasp defects', 12, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400012', id, 'Women Necklace and Earring Set', 'A beaded necklace with a flower centre, sold with matching drop earrings. A bracelet is shown with the set.', 5200.00, '6 months against clasp defects', 8, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400013', id, 'Women Gold Hoop Earrings', 'A pair of twisted gold hoop earrings. The hoops close with a hinged latch.', 1450.00, '6 months against stone loss', 16, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400014', id, 'Women Drop Earrings', 'A pair of rose-gold drop earrings with a round stone centre. The hooks are for pierced ears.', 1890.00, '6 months against stone loss', 14, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400015', id, 'Women Gold Jhumkas', 'Gold bell-shaped jhumkas. The bells hang from a hook for pierced ears.', 2200.00, '6 months against stone loss', 10, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400016', id, 'Women Stone Bracelet', 'A gold bracelet set with small stones in a repeating curve. It closes with a clasp.', 2400.00, '6 months against clasp defects', 11, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400017', id, 'Women Charm Bracelet', 'Slim bangles and charm bracelets worn together. The charms sit on the band.', 1650.00, '6 months against clasp defects', 13, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400018', id, 'Men Chain Bracelet', 'A gold chain-link bracelet for a man. It closes with a clasp and sits close on the wrist.', 1850.00, '6 months against clasp defects', 12, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400019', id, 'Women Rose Watch', 'A slim rose-gold watch with a pale face and a grey strap. Suitable for everyday wear, not for swimming.', 4900.00, '1 year against movement defects', 7, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400020', id, 'Men Steel Watch', 'A steel chronograph watch with a dark face and a metal bracelet. Keep it away from strong magnets.', 6400.00, '1 year against movement defects', 6, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400021', id, 'Women Halo Ring', 'A ring with a round centre stone and a halo of smaller stones.', 3200.00, '6 months against stone loss', 9, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400022', id, 'Women Pink Stone Ring', 'A gold ring with a pink centre stone and a border of small stones.', 2800.00, '6 months against stone loss', 9, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400023', id, 'Men Gold Band', 'A plain gold band for a man. The inside of the band is smooth.', 2100.00, '6 months against manufacturing defects', 10, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400024', id, 'Women Nude Nail Set', 'A pale nude nail colour for home use. Two shades are included. Sealed for hygiene.', 890.00, 'No warranty. Sealed for hygiene.', 18, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400025', id, 'Women Makeup Kit', 'Brushes, a lipstick, a powder compact and a mascara wand for everyday makeup. Patch-test before use.', 2450.00, 'No warranty. Sealed for hygiene.', 10, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400026', id, 'Women Face Serum', 'An amber dropper bottle for a face serum. Patch-test on the wrist before use on the face.', 1680.00, 'No warranty. Sealed for hygiene.', 14, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400027', id, 'Men Cologne', 'A glass cologne bottle with a silver cap, for external use. Keep it away from heat.', 3100.00, 'No warranty', 11, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400028', id, 'Men Stripe Tie', 'A navy tie with pale stripes. It is a standard length and ties in a simple knot.', 1100.00, 'No warranty', 15, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400029', id, 'Men Knit Tie', 'A dark knit tie with a small check. It is narrower than a silk tie.', 980.00, 'No warranty', 15, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400030', id, 'Men Bifold Wallet', 'A brown leather bifold wallet with card slots. It folds once.', 1950.00, '6 months against stitching defects', 12, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400031', id, 'Women Chain Handbag', 'A pale pink handbag with a chain strap and a coloured stripe across the flap.', 4200.00, '6 months against manufacturing defects', 7, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400032', id, 'Women Tan Handbag', 'A tan leather handbag with dark straps and a long shoulder strap.', 3800.00, '6 months against manufacturing defects', 8, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400033', id, 'Fruit Gift Basket', 'A wicker basket filled with fruit, packed as a gift.', 2500.00, 'No warranty. Perishable once opened.', 8, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400034', id, 'Ribbon Gift Boxes', 'A set of small gift boxes tied with ribbon, for giving.', 1800.00, 'No warranty', 10, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400035', id, 'Flower Gift Bouquet', 'A bouquet of pale pink flowers, wrapped to give.', 2200.00, 'No warranty. Flowers are perishable.', 8, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400036', id, 'Blue Gift Stack', 'A stack of blue and white gift boxes with ribbon, packed beside flowers.', 1600.00, 'No warranty', 9, 0, 'active' FROM categories WHERE code_prefix = '04';

INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'neck-layered.jpg', 1 FROM products WHERE product_code = '0400011';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'set-necklace.jpg', 1 FROM products WHERE product_code = '0400012';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'set-necklace-side.jpg', 0 FROM products WHERE product_code = '0400012';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'ear-hoop.jpg', 1 FROM products WHERE product_code = '0400013';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'ear-drop.jpg', 1 FROM products WHERE product_code = '0400014';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'jhumka-gold.jpg', 1 FROM products WHERE product_code = '0400015';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'brace-women.jpg', 1 FROM products WHERE product_code = '0400016';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'brace-charm.jpg', 1 FROM products WHERE product_code = '0400017';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'brace-men.jpg', 1 FROM products WHERE product_code = '0400018';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'watch-women.jpg', 1 FROM products WHERE product_code = '0400019';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'watch-men.jpg', 1 FROM products WHERE product_code = '0400020';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'ring-halo.jpg', 1 FROM products WHERE product_code = '0400021';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'ring-pink.jpg', 1 FROM products WHERE product_code = '0400022';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'ring-pink-side.jpg', 0 FROM products WHERE product_code = '0400022';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'ring-band.jpg', 1 FROM products WHERE product_code = '0400023';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'nails-nude.jpg', 1 FROM products WHERE product_code = '0400024';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'makeup-kit.jpg', 1 FROM products WHERE product_code = '0400025';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'skin-serum.jpg', 1 FROM products WHERE product_code = '0400026';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'perfume-men.jpg', 1 FROM products WHERE product_code = '0400027';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'tie-stripe.jpg', 1 FROM products WHERE product_code = '0400028';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'tie-knit.jpg', 1 FROM products WHERE product_code = '0400029';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'wallet-men.jpg', 1 FROM products WHERE product_code = '0400030';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'bag-chain.jpg', 1 FROM products WHERE product_code = '0400031';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'bag-tan.jpg', 1 FROM products WHERE product_code = '0400032';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'basket-fruit.jpg', 1 FROM products WHERE product_code = '0400033';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'basket-ribbon.jpg', 1 FROM products WHERE product_code = '0400034';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'basket-flowers.jpg', 1 FROM products WHERE product_code = '0400035';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'basket-blue.jpg', 1 FROM products WHERE product_code = '0400036';

INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400037', id, 'Women Soft Pink Nails', 'A real hand with a soft pink manicure and a pale tip. Sealed for hygiene.', 950.00, 'No warranty. Sealed for hygiene.', 16, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400038', id, 'Women Blue Glitter Nails', 'A real hand with royal-blue almond nails and a silver glitter tip.', 1100.00, 'No warranty. Sealed for hygiene.', 14, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400039', id, 'Women Blue Tip Nails', 'A real hand with a white nail and a blue glitter tip.', 1200.00, 'No warranty. Sealed for hygiene.', 14, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400040', id, 'Women Red Almond Nails', 'Real hands with glossy red almond nails.', 980.00, 'No warranty. Sealed for hygiene.', 16, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400041', id, 'Women Stone Bar Necklace', 'A gold chain with a rectangular pale stone pendant.', 3400.00, '6 months against clasp defects', 8, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400042', id, 'Women Choker Necklace', 'Layered gold necklaces, a short choker and a small pendant, worn with rings.', 2900.00, '6 months against clasp defects', 9, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400043', id, 'Women Temple Gold Necklace', 'A long gold necklace of pearls and coloured stones, worn with a matching jhumka.', 8900.00, '6 months against clasp defects', 4, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400044', id, 'Women Ruby Coin Necklace', 'A gold coin necklace set with ruby stones, worn with a red and green beaded choker.', 7600.00, '6 months against clasp defects', 5, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400045', id, 'Women Layered Gold Necklace', 'Layered gold necklaces worn with small jhumkas.', 4600.00, '6 months against clasp defects', 7, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400046', id, 'Women Red Stone Set', 'A gold necklace with red stones and matching gold earrings.', 6400.00, '6 months against clasp defects', 6, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400047', id, 'Women Heart Necklace Set', 'A gold heart necklace with matching heart drop earrings.', 2800.00, '6 months against clasp defects', 8, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400048', id, 'Women Filigree Necklace Set', 'A gold filigree necklace with matching jhumka earrings, shown in a display case.', 7200.00, '6 months against clasp defects', 5, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400049', id, 'Women Leaf Necklace Set', 'A fine gold necklace with a flower pendant and leaf earrings.', 3100.00, '6 months against clasp defects', 7, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400050', id, 'Women Bridal Pearl Set', 'A pearl and gold necklace worn with a jhumka, rings and bangles.', 12500.00, '6 months against clasp defects', 3, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400051', id, 'Women Beaded Necklace Set', 'A long pearl and ruby necklace with matching jhumkas, rings and a slim bracelet.', 9800.00, '6 months against clasp defects', 4, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400052', id, 'Women Gold Filigree Jhumkas', 'A pair of gold jhumkas with red enamel and small bell drops. The hooks are for pierced ears.', 2400.00, '6 months against stone loss', 10, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400053', id, 'Women Silver Jhumkas', 'Silver bell jhumkas with a fine chain beside them. The hooks are for pierced ears.', 2100.00, '6 months against stone loss', 9, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400054', id, 'Women Pearl Jhumkas', 'Gold jhumkas edged with pearls, shown with a matching pendant.', 3200.00, '6 months against stone loss', 8, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400055', id, 'Women Gold Link Bracelet', 'A gold bracelet of small linked stones. It closes with a clasp.', 2600.00, '6 months against clasp defects', 10, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400056', id, 'Women Silver Stone Bracelet', 'A silver bracelet set with round clear stones. It closes with a clasp.', 2300.00, '6 months against clasp defects', 9, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400057', id, 'Women Red Bangle Stack', 'A stack of gold bangles with red stones, for a woman.', 2800.00, '6 months against manufacturing defects', 8, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400058', id, 'Women Green Stone Ring', 'A gold ring with a green centre stone and a border of red, green and black stones.', 3600.00, '6 months against stone loss', 8, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400059', id, 'Women Eye Colour Palette', 'A small palette of warm eye colours. Patch-test before use.', 1850.00, 'No warranty. Sealed for hygiene.', 12, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400060', id, 'Women Lip and Eye Look', 'A glossy pink lip with black eyeliner and mascara, sold as a lip and eye look. Patch-test before use.', 2200.00, 'No warranty. Sealed for hygiene.', 10, 0, 'active' FROM categories WHERE code_prefix = '04';
INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status)
SELECT '0400061', id, 'Women Embroidered Shawl', 'A red and blush bridal dupatta with gold embroidery, worn as a shawl.', 4500.00, 'No warranty', 6, 0, 'active' FROM categories WHERE code_prefix = '04';

INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'nails-pink.jpg', 1 FROM products WHERE product_code = '0400037';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'nails-blue.jpg', 1 FROM products WHERE product_code = '0400038';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'nails-ombre.jpg', 1 FROM products WHERE product_code = '0400039';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'nails-red.jpg', 1 FROM products WHERE product_code = '0400040';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'neck-bar.jpg', 1 FROM products WHERE product_code = '0400041';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'neck-choker.jpg', 1 FROM products WHERE product_code = '0400042';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'neck-temple.jpg', 1 FROM products WHERE product_code = '0400043';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'neck-coin.jpg', 1 FROM products WHERE product_code = '0400044';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'neck-layers.jpg', 1 FROM products WHERE product_code = '0400045';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'set-red.jpg', 1 FROM products WHERE product_code = '0400046';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'set-heart.jpg', 1 FROM products WHERE product_code = '0400047';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'set-filigree.jpg', 1 FROM products WHERE product_code = '0400048';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'set-leaf.jpg', 1 FROM products WHERE product_code = '0400049';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'set-bridal.jpg', 1 FROM products WHERE product_code = '0400050';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'set-beaded.jpg', 1 FROM products WHERE product_code = '0400051';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'jhumka-filigree.jpg', 1 FROM products WHERE product_code = '0400052';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'jhumka-silver.jpg', 1 FROM products WHERE product_code = '0400053';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'jhumka-pearl.jpg', 1 FROM products WHERE product_code = '0400054';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'brace-gold.jpg', 1 FROM products WHERE product_code = '0400055';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'brace-silver.jpg', 1 FROM products WHERE product_code = '0400056';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'brace-bangles.jpg', 1 FROM products WHERE product_code = '0400057';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'ring-green.jpg', 1 FROM products WHERE product_code = '0400058';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'makeup-eyes.jpg', 1 FROM products WHERE product_code = '0400059';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'makeup-look.jpg', 1 FROM products WHERE product_code = '0400060';
INSERT INTO product_images (product_id, file_name, is_primary) SELECT id, 'shawl-embroidered.jpg', 1 FROM products WHERE product_code = '0400061';

INSERT INTO stock_movements (product_id, change_qty, balance_after, reason, note)
SELECT id, stock, stock, 'opening', 'Sample opening stock' FROM products;
