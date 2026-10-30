# Unit Testing Checklist

Date of the checks below: 29 October 2026. Environment: XAMPP on Windows, PHP 8.3, MariaDB 10.4, project URL `http://localhost/e_projject_ecomerce_website/`.

A result is listed only when that check was actually run. "Not run in the browser" means the screen was not clicked, even if the same rule exists in code or in the command-line script.

After these checks, `database.sql` and `database/sample_data.sql` were imported again so the database matches the installation manual. The orders and the extra category created during testing are not left in the database.

## Command-line business rules

`php tests/cli_workflow.php` was run on a freshly imported database.

| Check | Result |
| --- | --- |
| Admin password verifies with `password_verify` | Pass |
| Sample customer and product files are present | Pass |
| Decline card throws and stock stays the same | Pass |
| Quantity above stock is rejected and stock stays the same | Pass |
| VPP order number is `3010000100000001` for product `0100001` | Pass |
| Stock falls by the ordered quantity once | Pass |
| Server total for 2 cards at Rs 280 plus Rs 99 delivery is Rs 659 | Pass |
| Cancel before dispatch restores stock and blocks dispatch | Pass |
| Cheque order cannot dispatch while pending or only verified | Pass |
| Cheque can dispatch after it is cleared | Pass |
| Delivery closes cancellation and opens the 7-day return | Pass |
| Approved return restocks and creates a pending refund | Pass |
| Issuing the refund sets payment status to refunded | Pass |
| A mixed card order uses the lower product code `0100001` | Pass |
| Stored card data is last four `4242` plus a `SIM` reference, not the full number | Pass |

## Browser and HTTP checks

| Check | What happened | Result |
| --- | --- | --- |
| Home page | Title `Home · Paperlane`, banner, four categories, featured products, images loaded | Pass |
| Product page | `product.php?code=0100001` showed ID `0100001`, Rs 280.00, stock and warranty | Pass |
| Add to cart | Cart count changed from 0 to 1 without a full reload | Pass |
| Registration | New customer Sara Ahmed was created and the name appeared in the header. The guest cart stayed at 1 | Pass |
| Checkout totals | 1 card: goods Rs 280.00, delivery Rs 99.00, to pay Rs 379.00 | Pass |
| Card order | Success page showed `1010000100000001`, payment Cleared, order Confirmed, cart 0. Database last four digits were `4242` and stock of `0100001` fell from 40 to 39 | Pass |
| Default payment choice | The first checkout post missed the delivery type because PHP stored the keys `1`, `2` and `3` as integers, so `=== '1'` never marked a radio. The comparison was changed to a string. A later post with delivery type 1 succeeded | Fixed, then pass |
| Admin login | `admin@paperlane.local` opened the dashboard and the new order was listed | Pass |
| Customer blocked from admin | Opening `admin/index.php` while a customer was logged in showed the admin login | Pass |
| New category | Admin form created Desk Accessories with prefix `05` | Pass |
| New product | Admin form created `0500001` Brass Paper Clips, price 180, stock 25, with an opening stock movement of +25. The public page showed the photo at a real size of 900×1350 | Pass |
| Employee login | Ayesha Khan, `EMP0001`, saw the card order as ready | Pass |
| Employee blocked from admin | `admin/products.php` while the employee was logged in returned to the admin login | Pass |
| Dispatch and delivery | Employee saved a dispatch note and a delivery note. Status became Delivered. The cash sentence said the payment was already settled | Pass |
| Customer tracking | Sara's order page showed the number split into delivery 1, product `0100001`, sequence `00000001`, both staff notes, and a return window closing 5 November 2026 | Pass |
| Return | Sara submitted a return. Admin approved it. Stock returned to 40. A pending refund of Rs 379.00 was created, then marked issued. Payment status became refunded | Pass |
| Search | `shop.php?q=clips` showed 1 product, Brass Paper Clips | Pass |
| Price and category filter | `min=100&max=200&category=desk-accessories` kept `0500001` and did not show `0100001` | Pass |
| FAQ | Seven questions rendered. Opening "How much is delivery?" showed the Rs 99 / Rs 1,500 answer | Pass |
| Feedback | A note titled Parcel packing, rating 4, was stored and listed on `admin/feedback.php` | Pass |
| Guest checkout | `checkout.php` without a login returned HTTP 302 to `auth/login.php` | Pass |
| Declined card over HTTP | Card `4000000000000002` stayed on checkout with the decline message. Stock of `0500001` did not change | Pass |
| VPP order and cancel | Order `3050000100000002` was created (delivery 3, product `0500001`, sequence 2). Total Rs 279.00. Cancel before dispatch restored stock and set payment status to failed | Pass |
| Reports | The payment report included order `1010000100000001` | Pass |

## Not claimed as a browser test

| Item | Note |
| --- | --- |
| Cheque verify and clear buttons | Covered by `tests/cli_workflow.php`, not clicked in the browser |
| Employee password change | The page exists. The password was not changed, so the documented sample password still works |
| Deactivate employee | The form exists. It was not submitted |
| Phone-width layout | Desktop width was used. A separate mobile pass was not done |
| Two browsers buying the last item at the same moment | The transaction and row lock are in the code. A live two-browser race was not run |
