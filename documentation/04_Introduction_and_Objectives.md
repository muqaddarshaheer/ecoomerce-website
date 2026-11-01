# Introduction and Objectives

This is the Aptech eProject **Online Shopping Cart**, built as the shop Paperlane.

## Introduction

The thirst for learning, upgrading technical skills and applying the concepts in real life environment at a fast pace is what the industry demands from IT professionals today. However busy work schedules, far-flung locations, and unavailability of convenient time-slots pose as major barriers when it comes to applying the concepts into realism. Hence the need to look out for alternative means of implementation in the form of a laddered approach.

The same constraints apply to students. With busy schedules, it is difficult to keep up with a genuine need for integrated application which can be seen live, especially in IT education where technology can change quickly. Technology can be used as the training model itself.

eProject is a step-by-step learning environment that simulates classroom and lab learning as an actual implementation. It is a project implementation at the student's machine: an electronic, live juncture that allows the student to

- Practice step by step, in a laddered approach.
- Build a larger, more robust application.
- Use utilities inside an application the student designed.
- Move from a single program to unified code and a complete application.
- Learn implementation of concepts in a phased manner.
- Enhance skills and add value.
- Work on a real-life project.
- Start from a real-life scenario and create a more complicated and useful application.
- Use mentoring through the eProjects process.

The student is expected to complete this eProject and keep the documentation with the source code.

Paperlane is that complete application. It is a PHP and MySQL shopping cart for greeting cards, gifts, stationery, handbags, wallets and beauty accessories. A visitor can browse without an account. A customer orders only after login. An administrator maintains the catalogue, payments, staff and reports. An employee records dispatch and delivery and cannot change prices or create staff.

## Objectives

The objective of this program is to give a sample project to work on a real-life scenario, and to build a larger application with the tools, not to reteach the language. The student should already understand PHP, MySQL, HTML, sessions and SQL before starting. Doubts about the application or its objectives go back to the eProjects team.

Applied to this cart, the objectives are:

- Show products with photographs, prices, stock and warranty text.
- Let a visitor search and filter without registering.
- Let a customer register, log in, keep a cart and place an order.
- Give every product a 7-character product ID and every order a unique 16-character number.
- Support credit card, cheque and cash on delivery (VPP). Card payment is simulated and labelled as such.
- Stop card and cheque orders from leaving the shop before payment is cleared.
- Let staff record dispatch and delivery, and let a customer track those steps.
- Allow cancellation only before dispatch, and a return or replacement only within seven days after delivery.
- Reduce stock once, inside the order transaction, and restore it when a cancellation or an approved return requires it.
- Keep the administrator, the employee and the customer on separate doors.
- Store passwords with `password_hash` and use prepared statements, escaped output and CSRF checks.

## Standards plan

- The logic of each module is explained in the comment at the top of `includes/orders.php`, `includes/cart.php`, `includes/auth.php` and `includes/functions.php`, and again as algorithms in [Algorithms and flowcharts](11_Algorithms_and_Flowcharts.md).
- This folder is the project report: synopsis, certificate, contents, problem definition, customer requirements, plan, task sheet, ER diagram, algorithms, GUI standards, interface design, review report, unit-test checklist and final checklist.
- The documents describe the code that is in the project. They do not describe a feature that was not built.
