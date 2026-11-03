# Paperlane — Online Shopping Cart

Paperlane is a small stationery, gifts and accessories shop built as an Aptech eProject. It uses core PHP, MySQL, HTML, CSS, Bootstrap 5 and a little jQuery. There is no Laravel, React or Node.js.

Visitors can browse without an account. Only a logged-in customer can place an order, edit a profile or see that customer's own orders. Admin and employee accounts use separate login pages.

## What you need

- XAMPP with Apache, MySQL/MariaDB and PHP 8.0 or newer (tested with PHP 8.3 and MariaDB 10.4)
- A browser
- The project folder inside the XAMPP web root, for example `C:\xampp\htdocs\e_projject_ecomerce_website`

## Start XAMPP

1. Open the XAMPP Control Panel.
2. Start **Apache** and **MySQL**.
3. Leave the MySQL user as `root` with an empty password, which is the usual XAMPP default. If your MySQL password is not empty, change `config/database.php` before you continue.

## Create the database

1. Open `http://localhost/phpmyadmin`.
2. Import `database.sql`. This creates the database `paperlane_shop`, the tables, the administrator, four categories and seven FAQ answers.
3. Import `database/sample_data.sql` once, after `database.sql`, if you want the sample employee, sample customer and forty products (ten in each category). The shop also works with no sample products: create them in the admin panel.

Importing `database.sql` again **drops** `paperlane_shop` and starts over. Orders, customers and products added in the browser are removed. Import `database/sample_data.sql` again after that if you still want the sample rows. Do not import the sample file twice on the same database, or the product IDs will collide.

`config/database.php` already points at `127.0.0.1`, database `paperlane_shop`, user `root`, and an empty password. The site address is detected from the folder name. For this folder it is:

`http://localhost/e_projject_ecomerce_website/`

A short check page is at `setup.php`. It only reports whether PHP, the database and the upload folder look ready. It does not change data.

## Accounts

These passwords are the project defaults. They are written here so there is no hidden login. Change the administrator password after the first real use by editing the user in the database, or add your own admin before a demonstration if your teacher requires that.

| Who | Page | Email | Password |
| --- | --- | --- | --- |
| Administrator | `admin/login.php` | `admin@paperlane.local` | `Admin@123` |
| Employee (sample file) | `employee/login.php` | `employee@paperlane.local` | `Employee@123` |
| Customer (sample file) | `auth/login.php` | `customer@paperlane.local` | `Customer@123` |

The employee and sample customer exist only after `database/sample_data.sql` is imported. Anyone can also register a new customer at `auth/register.php`. A new employee created in the admin panel starts as pending and cannot log in until an administrator clicks Approve. The sample employee is already approved.

The shop **Log in** button opens one welcome screen. Customer, Employee and Admin are three choices on that screen, and each choice posts to its own login script. A **Use demo account** button fills the email and password for that role. Remember me keeps the browser signed in for 14 days. Forgot password does not send email: an administrator resets the account. The public FAQ page opens the corner AI Agent. The agent answers the questions stored in the shop (the same ones an administrator edits under Agent answers).

Use the matching login page. A correct password on the wrong page is rejected and the account is not logged in.

## Product ID and order number

Product IDs are 7-character text values so leading zeros stay: a 2-digit category code plus a 5-digit number. Example: category `01` produces `0100001`. The code is created inside a database transaction and does not change if the category is renamed later.

An order number is 16 characters:

`delivery digit` + `primary product ID` + `8-digit sequence`

| First digit | Meaning |
| --- | --- |
| 1 | Credit card |
| 2 | Cheque |
| 3 | VPP / cash on delivery |

The primary product is the lowest product ID in that order when the codes are compared as text. Example: a card order whose lowest product is `0100001` and whose sequence is 1 is `1010000100000001`.

The sequence comes from `order_counters`. The row is updated with `LAST_INSERT_ID(last_seq + 1)` inside the same transaction as the order. If the order fails, the transaction rolls back and that sequence is not kept. Two checkouts cannot take the same number.

## Payments

Card payment is a **simulation**. No bank is called. The full card number and CVV are not stored. The database keeps the brand, the last four digits, a reference that starts with `SIM`, and a note that the card data was not stored.

- Only two cards are accepted. Visa numbers start with 4. Mastercard numbers start with 51 to 55. Any other starting digits are rejected and no order is created.
- Success examples: Visa `4242 4242 4242 4242`, Mastercard `5555 5555 5555 4444`, a future expiry, and any 3 or 4 digit CVV. The checkout asks you to choose Visa or Mastercard, and the number must start like the card you chose.
- Decline cards: `4000 0000 0000 0002` and `4000 0000 0000 9995`. No order is created and stock does not change.

Cheque orders stay pending until an administrator verifies the cheque and then marks it cleared. They cannot be dispatched before clearance. Rejecting a cheque cancels the order and puts the stock back.

VPP orders can be dispatched before cash is collected. The employee records the cash when the parcel is delivered. An administrator can also mark cash collected after dispatch.

A refund row is created only when a cleared payment is cancelled before dispatch, or when an approved return had a cleared payment. Marking a refund "issued" updates the shop records. It does not send money to a bank.

## Shopping rules

- Delivery inside Karachi is Rs 180. It is free when the goods total is Rs 2,500 or more.
- Prices, quantities and totals are calculated on the server.
- Stock is reduced once, when the order is placed. Cancelling before dispatch puts it back. A product row is locked so two customers cannot buy the last piece.
- A customer can cancel only while the order is pending or confirmed and has no dispatch date.
- A return or replacement can be requested for 7 days after the delivery date. One open request is allowed per order, for the whole order.
- Employees see orders that are confirmed, dispatched or delivered, and only when the order is VPP or the payment is cleared. They cannot change prices, create staff or open the admin pages.

## Folders

- `assets/` styles, scripts, vendor files and shop images
- `config/` database and application settings
- `includes/` shared PHP
- `auth/` customer login, registration and logout
- `customer/` profile, orders and returns
- `admin/` administrator panel
- `employee/` dispatch desk
- `uploads/products/` and `uploads/categories/` uploaded pictures
- `database/sample_data.sql` optional sample rows
- `documentation/` project report and study guide
- `tests/` command-line checks. They are blocked from the browser.

Uploaded pictures must be JPG, PNG, WEBP or GIF, at most 2 MB, and must really be images. PHP files cannot run inside `uploads/`.

## Academic write-up

The report, diagrams, test results and a presentation script are in `documentation/`. Start with `documentation/03_Table_of_Contents.md` or `documentation/Project_Presentation_and_Study_Guide.md`.
