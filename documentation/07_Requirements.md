# Requirements

## Functional requirements

| ID | Requirement | Where it lives |
| --- | --- | --- |
| F1 | Home page shows a banner, categories and featured products | `index.php` |
| F2 | Product list supports search, category, minimum price, maximum price, sort, in-stock and pages | `shop.php` |
| F3 | Product page shows the product ID, gallery, price, description, stock and warranty | `product.php` |
| F4 | Visitors browse without an account | public pages |
| F5 | Customer registration, login and logout | `auth/` |
| F6 | Profile name, phone and password can be updated | `customer/profile.php` |
| F7 | Cart add, quantity update, remove and totals | `cart.php` |
| F8 | Guest cart is kept in the session and merged after customer login | `includes/cart.php` |
| F9 | Checkout collects an address and a delivery type | `checkout.php` |
| F10 | Server calculates goods, Rs 99 shipping, and free shipping from Rs 1,500 | `includes/functions.php` |
| F11 | Order number is 16 digits and unique | `includes/orders.php`, `order_counters` |
| F12 | Simulated card success clears payment; decline creates no order | `prepare_payment()` |
| F13 | Cheque moves pending, verified, then cleared, and only cleared can dispatch | admin order page |
| F14 | VPP can dispatch while payment is still pending | employee order page |
| F15 | Customer sees own orders and a timeline | `customer/orders.php`, `customer/order.php` |
| F16 | Cancel only before dispatch; stock returns; cleared payment creates a refund | `cancel_order()` |
| F17 | Return or replacement within 7 days of delivery | `create_return_request()` |
| F18 | Admin approves or rejects; approved return restocks; cleared payment gets a refund | `admin/returns.php` |
| F19 | Issuing a refund marks it issued and does not call a bank | `issue_refund()` |
| F20 | Admin product, category, image, stock, employee, customer, FAQ and feedback screens | `admin/` |
| F21 | Employee dispatch, delivery report and own password | `employee/` |
| F22 | Reports for payments, dispatch, delivery and stock, with CSV | `admin/reports.php` |
| F23 | Feedback form and public FAQ | `feedback.php`, `faq.php` |

## Non-functional requirements

| ID | Requirement | How it is met |
| --- | --- | --- |
| N1 | Beginner-readable PHP | Page scripts plus a few include files, no framework |
| N2 | Prepared statements | PDO with native prepares |
| N3 | Password storage | `password_hash()` and `password_verify()` |
| N4 | Sessions and roles | `paperlane_session`, checks before admin and employee HTML |
| N5 | CSRF | Token on every POST |
| N6 | XSS | Output through `e()` which encodes HTML |
| N7 | Upload safety | Type, size and `getimagesize()` checks; uploads cannot execute PHP |
| N8 | Money integrity | Totals recomputed from database prices inside a transaction |
| N9 | No oversell | `SELECT ... FOR UPDATE` and a stock check |
| N10 | Errors | Customers see a short message. Details go to `logs/app.log`. Database password is not printed |
| N11 | Responsive layout | Bootstrap 5 grid |
| N12 | Identifiers keep leading zeros | `CHAR` columns, not integers |

## Business rules that are easy to get wrong

- A category prefix is locked after the first product exists. Old product IDs stay valid.
- The primary product in an order number is the lowest `product_code` string, not the first item the customer clicked.
- Stock is deducted at placement, including cheque orders that are still unpaid. Rejection or cancellation puts it back. Dispatch does not deduct again.
- A replacement request restocks and then issues the goods again, so the net stock change is zero.
- Employees do not see the cheque number or the card's last four digits.
