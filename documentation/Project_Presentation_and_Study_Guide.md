# Project Presentation and Study Guide

Read this in simple English before a viva. It matches the Paperlane code and database. If a sentence says "simulated", that feature is finished in the website but it does not talk to a real bank.

## Ready-to-present introduction

Good morning. My project is an online shopping cart for Paperlane, a shop that sells greeting cards, gifts, dolls, files, stationery, handbags, wallets and small beauty accessories.

A visitor can look at products without an account. A customer registers, fills a cart and places an order. The administrator looks after products, stock, staff and payments. An employee only dispatches and delivers parcels.

The site is written in core PHP and MySQL. I did not use Laravel or React, so I can open a page and explain it. Card payment is a labelled simulation. Cheque clearance and cash on delivery are recorded by staff.

## Problem statement

Small shops often keep orders on paper. Stock becomes wrong, two people can sell the last item, and a cheque parcel can leave before the cheque clears. The customer cannot see the status later.

Paperlane keeps the catalogue, cart, order, payment, stock movement and delivery note in one database. The server calculates the total. The customer, the employee and the administrator do not share one login.

## Existing system and proposed system

**Existing:** a register, phone calls, and handwritten slips. Payment and delivery are easy to mix up.

**Proposed:** a website with three roles. Product IDs and order numbers follow a fixed rule. Card and cheque orders wait for clearance. Cash-on-delivery orders may leave first. Cancellation is only before dispatch. Returns last seven days after delivery. Stock changes are written one by one so they are not applied twice.

## Objectives

- Show products with photos, prices, stock and warranty.
- Allow browsing without registration, and ordering only after login.
- Generate a 7-digit product ID and a 16-digit order number.
- Support card, cheque and VPP, with the card path simulated.
- Let staff update dispatch and delivery.
- Let the administrator manage the shop without giving that power to the employee.

## Technologies and why

| Technology | Why it is here |
| --- | --- |
| PHP 8 | The pages and the rules. Core PHP is enough for a student project and is easy to trace. |
| MySQL / MariaDB | The data, with foreign keys and transactions. |
| PDO | Prepared statements, so user input is not pasted into SQL. |
| HTML and CSS | The pages. |
| Bootstrap 5 | A responsive grid without writing every layout by hand. |
| JavaScript and jQuery | The cart count, quantity buttons, payment panels, FAQ accordion and confirm dialogs. |
| Apache / XAMPP | Runs the site on the same computer. |

Bootstrap and jQuery are saved inside `assets/vendor`, so the shop still loads if the classroom has no internet. Fonts try Google Fonts first and fall back to Georgia and Segoe UI.

## Features and roles

**Visitor:** home, shop, search, filters, product page, FAQ, about, feedback.

**Customer:** everything a visitor can do, plus cart, checkout, profile, own orders, cancel before dispatch, and a return or replacement for seven days after delivery.

**Administrator:** dashboard, categories, products, images, stock, employees, customers, orders, cheque clearance, returns, refunds, feedback, FAQs and reports.

**Employee:** own password, orders that are allowed for fulfilment, dispatch, delivery report, and payment clearance status. No prices, no new employees, no admin menu.

## Database tables

`users` holds every login. `customers` and `employees` point at a user. `customer_addresses` stores addresses. `categories` and `products` are the catalogue. `product_images` and `stock_movements` belong to a product. `carts` and `cart_items` are the saved cart. `order_counters` holds the next sequence. `orders` and `order_items` are the purchase. `payments` is one row per order. `dispatch_records` are the notes. `return_requests` and `refunds` come after delivery or after a cleared cancellation. `feedback` and `faqs` are the extra pages.

A product ID is not the same as the numeric primary key. The primary key is an internal number. The product ID is `products.product_code`, a 7-character string.

## ER diagram, in words

One user can be one customer or one employee. A customer places many orders and can save many addresses. Each order copies the address it was sent to, so editing the address book later does not change old orders.

A category contains many products. A product has many pictures and many stock movements. An order has many order lines, one payment, and zero or more dispatch notes, returns and refunds. The cart is separate from the order. When checkout succeeds, the lines are copied onto the order and the cart is emptied.

`faqs` and `order_counters` do not belong to a customer. The counter is a single row so two orders cannot take the same sequence.

The picture is in `10_Database_Schema.md`.

## Flow from registration to delivery

1. The visitor opens the home page and a product. Add to cart stores the product id and quantity. Guests use the session. Customers use `cart_items`.
2. Registration inserts `users` with role `customer` and a `customers` row. The password is hashed.
3. Login checks the hash, checks the role, and merges the guest cart.
4. Checkout reads the cart again from the database prices. Shipping is Rs 99, or Rs 0 when goods are at least Rs 1,500.
5. The chosen payment is checked. A declined card stops here.
6. A transaction locks the products, takes the next order number, inserts the order, items and payment, and reduces stock.
7. The customer sees the order page. An employee sees it when it is confirmed and either VPP or already cleared.
8. Dispatch writes a record and sets the dispatch date. Delivery writes another record. For VPP, cash can be marked collected in that same save.
9. The customer sees those notes on the tracking page.

## Product ID and order number

**Product ID:** category prefix plus a 5-digit number. Category Greeting Cards is `01`, so the first product is `0100001`. The prefix is locked after products exist. The value is stored as text so `0100001` does not become `100001`.

**Order number, 16 digits:**

| Part | Length | Meaning |
| --- | --- | --- |
| Delivery | 1 | 1 card, 2 cheque, 3 VPP |
| Primary product | 7 | Lowest product code in the order |
| Sequence | 8 | Next number from `order_counters` |

If one order contains `0200004` and `0100001`, the primary product is `0100001` because `"0100001" < "0200004"` as text. The sequence is increased inside the order transaction. A failed order rolls the number back with the rest of the transaction.

Worked example from testing: one birthday card paid by the test card became `1010000100000001`. Read it as delivery `1`, product `0100001`, sequence `00000001`.

## Payment, cancellation, return and replacement

**Credit card — simulated.** A future Visa or Mastercard number that passes the Luhn check is accepted, including `4242424242424242`. `4000000000000002` and `4000000000009995` are declined. Nothing is ordered on a decline. The row stores brand, last four digits and a reference starting with `SIM`. The full number and CVV are thrown away. No bank is contacted. This is a completed screen with a simulated gateway, not a live payment integration.

**Cheque.** Status goes pending, then verified, then cleared. Only an administrator does this. Dispatch is refused until cleared. Rejecting the cheque cancels the order and restores stock.

**VPP.** The order is confirmed immediately and payment stays pending. It may be dispatched. Cash is recorded at delivery.

**Cancel.** Allowed only when the status is pending or confirmed and `dispatch_date` is empty. Stock goes back. If the payment was cleared, a refund row is created. If it was not cleared, the payment becomes failed.

**Return.** Allowed for seven days after `delivery_date`, one non-rejected request per order, for the whole order. Approval restocks. If the payment had been cleared, a refund row is created.

**Replacement.** Approval restocks and then issues the same quantity again, so the shelf count ends where it started.

**Refund.** An administrator marks it issued. The payment status becomes refunded. This does not transfer money. Say that clearly in the viva.

## Security

- PDO prepared statements.
- `password_hash` and `password_verify`.
- A new session id on login. Inactive users are logged out.
- Role checks run before the admin or employee page is drawn.
- CSRF token on every POST.
- HTML output is encoded.
- Uploads are checked with `getimagesize`, limited to 2 MB, and saved under a random name. `uploads` cannot run PHP.
- Prices and totals are recalculated on the server.
- Order placement, stock and payment updates use a transaction.
- Errors shown to the user do not include the database password. The detail goes to `logs/app.log`.
- Five bad passwords lock the browser for two minutes.
- There is no hidden account. The three default logins are in the README.

## Testing summary

The command-line script `tests/cli_workflow.php` passed on a clean database: decline, oversell, VPP number, single stock deduction, cancel, cheque clearance before dispatch, return, refund, and card data limited to the last four digits.

In the browser and over HTTP, a customer registered, paid with the test card, and received order `1010000100000001` for Rs 379.00. An administrator created category `05` and product `0500001`. An employee dispatched and delivered the card order. The customer tracked it and requested a return. The administrator approved it, stock returned to 40, and the Rs 379.00 refund was marked issued. A second order, `3050000100000002`, was cash on delivery and was cancelled before dispatch. Search, the price filter, the FAQ accordion, feedback, guest checkout, a declined card and the blocked admin page were also checked.

The exact table is `15_Unit_Testing_Checklist.md`. Those test orders were removed by importing the SQL files again.

## Advantages

- Three roles with separate logins.
- Order numbers that a student can decode.
- Stock and money changes in a transaction.
- The shop still works when the administrator adds products later. It does not depend on the sample file.

## Limitations

- Card payment and refunds are simulated.
- A return covers the whole order, not one line.
- There is one counter for all orders, which is enough for this shop.
- Sample photos are real files. A few are only loosely related to the product name, for example the file set and the birthday card. Replacing them in the admin form does not require a code change.
- The layout was checked on a desktop browser. A separate phone-sized pass was not recorded.

## Future improvements

- A real payment gateway, still without storing the card.
- Returns for a single line.
- Email when an order is dispatched.
- A barcode on the packing slip.

## Conclusion

Paperlane is a working shopping cart with a public shop, a customer account, an admin panel and an employee desk. Product IDs, order numbers, stock and the three payment rules are implemented in PHP and MySQL. The card page is intentionally a simulation and is labelled on the screen and in this guide.

## A 3 to 5 minute speech

Good morning. I am presenting Paperlane, an online shop for stationery, gifts and accessories.

The problem is that a paper register cannot reliably show stock, payment and delivery together. My proposed system is a PHP and MySQL website. Visitors may browse. Only a registered customer can order.

I will show the home page, then a product. Its ID is seven digits. The first two digits are the category. `0100001` is the first product in category 01.

I add it to the cart. Delivery is 99 rupees, and it is free after 1,500 rupees of goods. At checkout I choose a simulated card, `4242 4242 4242 4242`. The site does not store the full number and it does not call a bank. The order number has 16 digits: 1 for the card, then the product ID, then the sequence.

If I had chosen a cheque, the employee could not dispatch it until the administrator marked it cleared. Cash on delivery can leave first, and the rider records the cash.

The customer can cancel only before dispatch. After delivery there are seven days to ask for a return. A refund row is created only when a cleared payment is cancelled or a return is approved. Marking it issued updates our database only.

The administrator adds products and staff. The employee sees the parcel and a password page, and cannot change prices. A customer who opens the admin address is sent back to the admin login.

I used prepared statements, hashed passwords, sessions, CSRF tokens and transactions. That is the project. Thank you. I can take questions.

## Viva questions

1. **What is the project?**  
   A shopping site for Paperlane with a customer shop, an admin panel and an employee desk.

2. **Why PHP and MySQL?**  
   The course asks for core PHP and MySQL. The pages are easy to open and explain, and MySQL transactions protect the order and the stock.

3. **Can a guest buy?**  
   No. A guest can browse and hold a session cart. Checkout asks for a customer login.

4. **How is the password stored?**  
   `password_hash()` creates a bcrypt hash. Login uses `password_verify()`. The plain password is not stored.

5. **What is a product ID?**  
   Seven characters: two for the category and five for the number, stored as text. Example `0100001`.

6. **What is an order number?**  
   Sixteen characters: delivery digit, primary product ID, eight-digit sequence.

7. **Which product is the primary one?**  
   The lowest product code in that order, compared as text.

8. **Why a counter table?**  
   Updating one row inside the transaction gives a unique sequence. A failed order rolls it back.

9. **What are the three payments?**  
   Card, cheque and VPP. Card is simulated. Cheque must be cleared by an administrator. VPP is cash at the door.

10. **What is stored from a card?**  
    Brand, last four digits, a `SIM` reference and a note. Not the full number and not the CVV.

11. **Which test card fails?**  
    `4000000000000002`. No order is written and stock does not change.

12. **When does stock fall?**  
    Once, when the order is placed. Dispatch does not deduct again.

13. **What if two people buy the last item?**  
    The product rows are locked with `FOR UPDATE` in id order. The second transaction waits and then sees the new stock.

14. **When can a customer cancel?**  
    Only before dispatch, while the status is pending or confirmed.

15. **What is the return window?**  
    Seven days after the delivery date, for the whole order.

16. **When is a refund created?**  
    After a cleared payment is cancelled before dispatch, or after an approved return of a cleared payment. Issuing it does not call a bank.

17. **What can the employee not do?**  
    Create employees, change prices, verify cheques, or open the admin pages.

18. **How do you stop SQL injection?**  
    PDO prepared statements. Values are bound, not concatenated into the query.

19. **How do you stop a fake form post?**  
    A CSRF token in the session must match the hidden field.

20. **How do you stop a script in a product name?**  
    The name is encoded with `htmlspecialchars` before it is printed.

21. **Who calculates the total?**  
    The server. The browser total is only for display.

22. **What is free delivery?**  
    Goods of Rs 1,500 or more. Otherwise delivery is Rs 99.

23. **What happens on the fifth bad password?**  
    That session must wait two minutes.

24. **Where are images checked?**  
    The upload must be a real JPG, PNG, WEBP or GIF, at most 2 MB. It is stored under a random file name.

25. **Does the sample data have to be there?**  
    No. `database.sql` is enough to log in as admin and create products. The sample file is optional.

## Concepts used in this project

**PHP.** Pages run on the server. `include` shares the header and the database code. `$_POST` and `$_GET` bring in the form. Sessions remember who is logged in. Exceptions turn a rule failure into a message.

**MySQL.** Tables, primary keys, foreign keys and unique keys. `CHAR` keeps leading zeros. `ENUM` limits status values. A transaction is `begin`, then the writes, then `commit`, or `rollBack` if something fails. `SELECT ... FOR UPDATE` locks a row.

**HTML.** Forms, labels, required fields, tables and buttons. The product page is a normal document, not a single-page app.

**CSS.** One stylesheet sets the forest, clay and sand colours, the cards and the spacing. Bootstrap supplies the grid and the responsive menu.

**JavaScript.** jQuery updates the cart count after an Ajax add, shows the card or cheque fields for the selected payment, steps the quantity, and asks `confirm` before a destructive button. The order itself is still a normal form post.
