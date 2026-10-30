# Interface Design Document

Paths are relative to the project folder. Example base: `http://localhost/e_projject_ecomerce_website/`.

## Public shop

| Screen | File | What the user sees and does |
| --- | --- | --- |
| Home | `index.php` | Banner, four category pictures, featured products, short promises |
| Shop | `shop.php` | Search box, category, min and max price, sort, in-stock checkbox, product cards, page links |
| Product | `product.php?code=0100001` | Gallery, product ID, price, stock, warranty, quantity, Add to cart |
| Cart | `cart.php` | Lines, quantity, remove, goods, delivery and total |
| Checkout | `checkout.php` | Saved or new address, card, cheque or VPP, server total, Place order |
| Order received | `order-success.php?n=` | 16-digit number, payment status, link to tracking |
| FAQ | `faq.php` | Accordion of active questions |
| About | `about.php` | Short description of the shop |
| Feedback | `feedback.php` | Name, email, subject, rating, message |
| Setup check | `setup.php` | Read-only server and database check |

Header links: Shop, Categories, FAQ, About, Feedback, search, cart count, and either Log in or the customer name. The footer repeats shop links and the two staff logins.

## Customer account

| Screen | File | Notes |
| --- | --- | --- |
| Login | `auth/login.php` | Customer role only |
| Register | `auth/register.php` | Name, email, phone, password, hidden honeypot |
| Logout | `auth/logout.php` | Ends the session |
| Profile | `customer/profile.php` | Name, phone, password |
| Orders | `customer/orders.php` | That customer's orders only |
| Order | `customer/order.php?id=` | Items, address, payment, timeline, cancel or return when the rules allow |
| Returns | `customer/returns.php` | That customer's requests |

## Admin panel

Login: `admin/login.php`. After login the menu is Dashboard, Categories, Products, Orders, Customers, Employees, Returns, Feedback, FAQs, Reports, View shop, Log out.

| Screen | File | Main actions |
| --- | --- | --- |
| Dashboard | `admin/index.php` | Counts, recent orders, low stock |
| Categories | `admin/categories.php` | Add, edit, delete when empty |
| Products | `admin/products.php` | List and search |
| Product form | `admin/product-form.php` | Create or edit, upload images, set stock |
| Orders | `admin/orders.php` | Filter by date, delivery type, customer, status |
| Order | `admin/order.php` | Cheque verify, clear or reject; dispatch and delivery; cash collected |
| Customers | `admin/customers.php` | View and activate or deactivate |
| Employees | `admin/employees.php` | Create, activate, deactivate |
| Returns | `admin/returns.php` | Approve, reject, issue refund |
| Feedback | `admin/feedback.php` | Read, mark reviewed, delete |
| FAQs | `admin/faqs.php` | Add, edit, hide |
| Reports | `admin/reports.php` | Payment, dispatch, delivery and stock tables, date filter, CSV, print |

## Employee desk

Login: `employee/login.php`.

| Screen | File | Main actions |
| --- | --- | --- |
| Dashboard | `employee/index.php` | Counts and the next parcels |
| Orders | `employee/orders.php` | Ready, dispatched and delivered tabs |
| Order | `employee/order.php` | Address, contents, payment clearance, dispatch, delivery report |
| Reports | `employee/reports.php` | That desk's delivery notes |
| Password | `employee/password.php` | Current password and a new password |

The employee order page does not show a price editor, a cheque number or card digits.

## Messages

- Success and errors use alerts.
- Destructive buttons use `data-confirm`.
- Add to cart can answer with JSON when `ajax=1`, and the header cart count updates without a full reload. Other saves use a normal form post and a redirect, so a refresh does not repeat the action.
