# Problem Definition

**Online Shopping Cart**

## Table of contents

1. Introduction
2. Existing scenario
3. Proposed scenario
4. Non-financial requirements
5. Financial requirements
6. Functional requirements

## Introduction

Arts is a stationery shop that sells arts materials, gift articles, greeting cards, dolls, files, handbags, wallets, and some beauty products. There is strong demand for greeting cards, gift articles and handbags. In a busy schedule, a customer may not have time to visit the shop. With current technology, the same customer can place an order from home or the office and pay by credit card, VPP, or cheque.

The opening brief also names demand draft beside those three. The financial rules below specify credit card, cheque and VPP. Checkout in Paperlane offers those three. There is no separate demand-draft form.

## Existing scenario

The client is a retail dealer of gift articles, dolls, files and similar goods. Customers usually visit the shop to buy. The shop holds a large variety. Demand is rising, combinations of products are many, and the number of retail dealers is growing, so it is harder to hold a place in the market and to keep customers.

The shop also does not keep a reliable track of products, so it is hard to know how many pieces are still on the shelf.

## Proposed solution

To stay competitive, keep customers, and automate the work, the client wants an application in which a customer places an order online from home or the office, and the dealer sends the goods to the customer's door.

The application must store each product in detail: price, description, and a unique 7-digit product ID made of a 2-digit product code and a 5-digit product number. A customer may look up products without registering. Placing an order requires a registered account.

After the order is placed, the dealer dispatches it according to the delivery type the customer chose. The application generates a unique 16-digit order number as soon as the order is placed. That number is 1 digit for the delivery type, 7 digits for the product ID, and 8 digits for the order sequence.

An order paid by credit card or by cheque is dispatched only after the payment has cleared. If the customer is not satisfied, the customer may ask for a replacement or a return within seven days of delivery, and a return gives the money back. A replacement does not create a second order. Products that carry a warranty show that warranty with the product.

Only the dealer updates employees, products and their details, and checks orders by date and by delivery type. Only the dealer creates an employee profile, with a login ID and a password. An employee may change only that employee's own password, and may read the orders so the goods can be dispatched and delivered on time.

The website has a place for the customer to send feedback about the service.

## Non-financial

- Only the dealer manages products, employees, orders, stock, feedback and FAQs.
- An employee may only change that employee's password, and may check and update orders and delivery reports.
- Products, employees, orders, dispatch and delivery, and customers are stored in the database.
- A customer can open the details of an order that customer placed.
- Search can narrow products by words, category, price, sort and stock.
- A registered customer manages that account and that customer's orders, and can still read the catalogue. A visitor who is not registered can read the catalogue and can register.
- Stock on hand, the dispatch date, the payment, and the delivery are recorded so they can be read later.
- At checkout the payment is stored according to the type chosen: card brand and last four digits for a card, and cheque number, bank and date for a cheque. The full card number and CVV are not stored.
- A customer can cancel only while the order has not been dispatched.

## Financial

- If the customer pays by cheque or by credit card, the goods are dispatched only after that payment is cleared.
- If the order is VPP, the customer pays at delivery.
- If the customer is not satisfied, the customer may request a replacement or a return within seven days after delivery, so that a return can refund what was paid.

## Functional requirements

- Employee details, product details, stock, orders, delivery details and customer details are kept in the database.
- Admin, employee and customer each have a separate login.

## Where this already lives

Nothing new was added to the shop for this statement. The rules above are the ones already in the program.

| Requirement | Where |
| --- | --- |
| Catalogue without an account | Public shop pages |
| Order only after registration | `checkout.php`, `includes/auth.php` |
| 7-digit product ID | `products.product_code` |
| 16-digit order number | `includes/orders.php` |
| Card, cheque, VPP | `includes/functions.php` `delivery_types()` |
| Dispatch after card or cheque clearance | `can_dispatch()` |
| VPP paid at delivery | `record_delivery()` |
| Cancel before dispatch | `can_cancel()` |
| Return or replacement within 7 days | `can_request_return()` |
| Warranty text | Product page and admin product form |
| Dealer manages staff, products, orders, stock, feedback, FAQs | `admin/` |
| Orders filtered by date and delivery type | `admin/orders.php` |
| Employee password, orders and delivery report | `employee/` |
| Customer sees own orders | `customer/order.php` |
| Feedback | `feedback.php` |
| Separate logins | `auth/login.php` |
