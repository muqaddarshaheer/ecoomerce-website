-- Paperlane gift customisation. Run this on an existing paperlane_shop database.
-- Do not run database.sql again. That file drops the database.
-- The live shop already has these columns if the site was updated in place.

ALTER TABLE cart_items
  ADD COLUMN design VARCHAR(20) NOT NULL DEFAULT 'classic',
  ADD COLUMN color_name VARCHAR(20) NOT NULL DEFAULT 'Ivory',
  ADD COLUMN custom_name VARCHAR(60) NOT NULL DEFAULT '',
  ADD COLUMN gift_message VARCHAR(180) NOT NULL DEFAULT '',
  ADD COLUMN custom_note VARCHAR(180) NOT NULL DEFAULT '',
  ADD COLUMN line_token CHAR(16) NOT NULL DEFAULT '';

ALTER TABLE order_items
  ADD COLUMN design VARCHAR(20) NOT NULL DEFAULT '',
  ADD COLUMN color_name VARCHAR(20) NOT NULL DEFAULT '',
  ADD COLUMN custom_name VARCHAR(60) NOT NULL DEFAULT '',
  ADD COLUMN gift_message VARCHAR(180) NOT NULL DEFAULT '',
  ADD COLUMN custom_note VARCHAR(180) NOT NULL DEFAULT '';
