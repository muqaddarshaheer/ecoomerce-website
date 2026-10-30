# Introduction and Objectives

## Introduction

A stationery and gift shop takes orders for cards, files, bags and small accessories. If those orders live only in a notebook, the shop can lose track of stock, payments and deliveries. A website lets a customer see what is actually in stock and lets the shop record each step from the cart to the doorstep.

Paperlane is that website. It is a student project written in plain PHP so the pages, the database and the rules can be explained without a framework. The public shop, the customer account, the admin panel and the employee desk are separate. Money totals are calculated on the server from the prices stored in MySQL.

## Objectives

- Show products with photographs, prices, stock and warranty text.
- Let a visitor search and filter without registering.
- Let a customer register, log in, keep a cart and place an order.
- Give every product a 7-digit ID and every order a unique 16-digit number.
- Support credit card, cheque and cash on delivery, with the card path clearly simulated.
- Stop card and cheque orders from leaving the shop before payment is cleared.
- Let staff record dispatch and delivery, and let a customer track those steps.
- Allow cancellation only before dispatch, and a return or replacement only for seven days after delivery.
- Reduce stock once per order and put it back when a cancellation or an approved return requires it.
- Give the administrator products, staff, payments, returns, feedback, FAQs and reports.
- Keep employees away from prices, staff accounts and the rest of the admin panel.
- Store passwords as hashes and stop common injection, cross-site scripting and forged form posts.
