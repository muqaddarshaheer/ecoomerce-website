# Customer Requirement Specification

The customer for this project is the owner of a shop selling greeting cards, gifts, dolls, files, stationery, handbags, wallets, beauty products and accessories.

## Users

- **Visitor:** may browse, search, filter and read the FAQ and about page.
- **Customer:** a registered visitor who may order and see only that person's orders.
- **Administrator:** one shop manager who maintains the catalogue, staff, money records and content.
- **Employee:** a dispatch worker who updates delivery progress and a personal password.

## What the shop owner asked for

1. A home page with a banner, categories and featured products, using real product photographs.
2. A product list and a product page with a gallery, price, description, stock and warranty.
3. Search, category filter and price filter.
4. Registration, login, logout and a profile.
5. A cart with quantity changes, removal and totals.
6. Checkout with an address and a choice of credit card, cheque, or VPP / cash on delivery.
7. Order history, order details and tracking.
8. Cancellation only before the parcel is dispatched.
9. A return or replacement request within seven days after delivery.
10. A feedback form and an FAQ page.
11. A 7-digit product ID: 2-digit category code plus 5-digit number, kept as text.
12. A unique 16-digit order number: 1 delivery digit, 7-digit primary product ID, 8-digit sequence.
13. Card and cheque orders must not be dispatched until payment is cleared. VPP may be dispatched first.
14. Stock must fall when an order is confirmed and must not be reduced twice. Overselling is not allowed.
15. Refunds only after an authorised return, or when a cleared payment is cancelled before dispatch.
16. An admin panel for products, images, categories, stock, employees, customers, orders, cheque clearance, returns, feedback, FAQs and reports.
17. An employee panel that cannot create staff, change prices or act as the administrator.
18. A single `database.sql` file. The live shop must work with products created in the admin panel. Sample rows are optional.
19. Passwords hashed, database access through prepared statements, and protection against forged forms and script injection.

## Out of scope

- A real card network or a real refund to a bank. The card form is a labelled simulation.
- A mobile app.
- Per-item returns. A request covers the whole order.
- Multiple warehouses.
