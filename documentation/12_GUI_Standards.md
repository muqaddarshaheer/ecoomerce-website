# GUI Standards Document

The public shop, the admin desk and the employee desk are three screens. They share type, buttons and status colours. They do not share one background colour.

## Colour

The shop has two modes. A Light / Dark control in the navbar stores the choice in the browser.

| Mode | Page | Accent | Text |
| --- | --- | --- | --- |
| Dark | `#07110d` | `#22c55e` | `#f3f7f4` |
| Light | `#f3f7f4` | `#16a34a` | `#122117` |

| Desk | Colour | Where |
| --- | --- | --- |
| Admin | Forest `#1e3a32` | Left sidebar and admin buttons |
| Employee | Blue `#2f5f8a` | Employee buttons and lane |

Product photos sit on a light pad in both modes so a white or dark photograph stays readable. Deal, Offer and Sale badges are green, except Sale, which is gold `#f4d48a` with dark text.

Status badges stay semantic: amber for pending, green for cleared or success, and a separate colour for cancelled. They do not follow the theme accent.

## Type

- Headings use Fraunces, then Georgia.
- Body text, prices and forms use Manrope, then Segoe UI.
- Order numbers are shown in full.
- Do not set body text below 16px.

## Layout

- Shop content sits in a Bootstrap container.
- The home page is a shop front: hero, four timed offer boxes (stationery, gifts, bags, beauty), aisle circles, a daily counter, new arrivals, categories, a lookbook, a brand row, and products that have actually been ordered.
- Only the brand row slides. Product rows are still grids.
- Shop and product pages do not use a full-width banner.
- Product cards show the photo, name, price and a View button.
- Admin uses a sticky left sidebar. The employee desk keeps its own sidebar.
- Buttons that delete or dispatch ask for confirmation before the form is sent.
- The shop has a skip link. Form errors appear in a Bootstrap alert.

## Images

- A product photo must show the thing named on the card.
- Files live under `uploads/products`, `uploads/categories`, or `assets/images`.
- If a file is missing, the page shows a letter tile.
- Uploaded files are renamed. The original file name is not used as the URL.
- Brand marks on the home row are original wordmarks drawn for this shop. They are not official trademark files.

## Writing on the screen

- Currency is written `Rs 1,560.00`.
- The card form states that no bank is contacted.
- Offer boxes show the shelf price. They do not show a crossed-out price or a percent off, because the database has no discount column.
- Free delivery is stated as the real rule: Rs 99, free from Rs 1,500.

## Motion

- Hover on links, cards, category pills and View buttons turns them green.
- The brand row is the only continuous slide.
- A countdown on each offer box and on the daily counter runs until midnight in Asia/Karachi.
