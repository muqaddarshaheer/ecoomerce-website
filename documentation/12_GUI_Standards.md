# GUI Standards Document

Paperlane uses one visual system on the shop, the admin panel and the employee desk. The aim is a small shop site that a student can recognise and explain, not a copied theme.

## Colour

| Name | Hex | Use |
| --- | --- | --- |
| Forest | `#1e3a32` | Header, primary buttons, footer |
| Clay | `#c4653a` | Second buttons, prices and small highlights |
| Sand | `#f3eee6` | Page background |
| Ink | `#1c1917` | Body text |
| White | `#ffffff` | Cards, forms and tables |

Status colours come from Bootstrap badges: green for success and cleared, amber for pending, red for danger and cancelled, and grey for neutral states. Do not invent extra badge colours in new pages.

## Type

- Headings use Fraunces, then Georgia.
- Body text and forms use Outfit, then Segoe UI.
- Prices and order numbers use the same body font so they stay easy to read aloud.
- Do not set body text below 16px.

## Layout

- The shop content sits in a Bootstrap container.
- Product cards are equal-height columns: photo, name, price, stock.
- Forms use one column on a phone and two columns from the medium breakpoint upward.
- Admin and employee pages share a left navigation list. The shop uses a top bar and a footer.
- Buttons that delete or dispatch ask for confirmation in the browser before the form is sent.
- Every page has a skip link on the shop, and form errors appear in a Bootstrap alert above the form.

## Images

- Category and product photos are real image files under `assets/images` or `uploads`.
- If a file is missing, the page shows a letter tile. It does not show a broken image icon.
- Product photos are cropped in a fixed frame with `object-fit: cover`.
- Uploaded files are renamed to a random name. The original file name is not used as the URL.

## Writing on the screen

- The shop speaks in short sentences. Example: "The simulated bank declined this card."
- Currency is written `Rs 280.00`.
- Order numbers are shown in full, then explained in words under the number.
- The card form states that no bank is contacted.

## Motion

- Cards lift slightly on hover.
- Menus and the FAQ accordion use the Bootstrap collapse. There is no extra animation library.

## What not to add

- Stock photographs that do not belong to a product, except the existing sample set.
- Placeholder buttons that do not submit or link.
- A second colour scheme for the admin panel.
