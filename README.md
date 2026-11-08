# Paperlane

A small Karachi counter for greeting cards, stationery, bags and gifts, opened as a full online shop.

Anyone may walk the aisles. A customer signs in only when it is time to pay, follow a parcel, or ask for something back. Behind the counter sit two more desks: an administrator who keeps the catalogue honest, and an employee who packs what is already paid for.

Paperlane is an Aptech eProject. It is written in core PHP and MySQL, with HTML, CSS, Bootstrap 5 and a little jQuery. There is no Laravel, React or Node.js, and there is no hidden login.

**Open the shop:** [http://localhost/e_projject_ecomerce_website/](http://localhost/e_projject_ecomerce_website/)

---

## The counter

| Page | What it is for |
| --- | --- |
| Home | The day’s shelves, occasions, and a note the visitor can leave |
| Shop | Search, categories, and a single product with its real price and stock |
| Brands | Stationery names on a sliding shelf. Opening a name filters the shop |
| Customise | A finish, a colour, a name and a message on a gift. Ordering still needs a sign-in |
| About | The departments, and pieces that really appear on orders |
| Cart and checkout | Address, delivery, and one of three ways to pay |
| Your account | Profile, your own orders, cancel, return or replacement |
| Admin desk | Catalogue, stock, staff, payments, returns, feedback, FAQs and reports |
| Employee desk | Confirmed work that is ready to pack, dispatch and deliver |

A guest can browse, search and read the FAQ. The cart remembers a guest, but the order is placed only after login. Each person sees only their own orders.

## Three doors

The **Log in** button opens one welcome screen. Customer, Employee and Admin are three choices, and each choice posts to its own script. A correct password on the wrong door is refused. The account is not signed in.

A **Use demo account** button fills the email and password for the role you picked. Remember me keeps the browser signed in for 14 days. Forgot password does not send email: an administrator resets the account.

A new employee created in the admin panel starts as pending and cannot log in until an administrator clicks Approve.

## How a parcel moves

1. The customer checks out. The server prices the cart again, locks the product rows, and takes the stock once.
2. The order receives a 16-character number. Card and cheque parcels wait in the shop until the payment is cleared. A VPP parcel may leave before the cash is collected.
3. An employee records dispatch, then delivery. The customer follows those steps on their own order page.
4. The customer may cancel only while the order is pending or confirmed and has no dispatch date. Stock returns to the shelf.
5. For seven days after delivery, the customer may ask for a return or a replacement. One open request is allowed per order. An approved replacement puts the goods back and takes them again, so the stock ends where it started. It does not create a second order.
6. A refund row appears only when a cleared payment is cancelled before dispatch, or when an approved return had already been paid. Marking that refund “issued” updates the shop’s books. It does not send money to a bank.

Delivery inside Karachi is **Rs 180**. It is free when the goods total is **Rs 2,500** or more. Prices, quantities and totals are calculated on the server.

Employees see orders that are confirmed, dispatched or delivered, and only when the order is VPP or the payment is cleared. They do not see cheque numbers or card digits. They cannot change prices, add catalogue items, create staff, or open the admin pages.

## Product codes and order numbers

A product code is a 7-character string, so the leading zeros stay: two digits for the category, then five for the piece. Category `01` produces `0100001`. The code is created inside a database transaction and does not change if the category is renamed later.

An order number is 16 characters:

`delivery digit` + `lowest product code in the order` + `8-digit sequence`

| First digit | How the customer pays |
| --- | --- |
| 1 | Credit card |
| 2 | Cheque |
| 3 | VPP, cash on delivery |

The product code in the middle is the lowest one in that order when the codes are compared as text. A card order whose lowest product is `0100001`, and whose sequence is 1, is `1010000100000001`.

The sequence lives in `order_counters`. Inside the same transaction as the order, the row is updated with `LAST_INSERT_ID(last_seq + 1)`. If the order fails, the transaction rolls back and that number is not kept. Two checkouts cannot take the same number.

## Payments

Card payment is a **simulation**. No bank is called. The full card number and the CVV are never stored. The database keeps the brand, the last four digits, a reference that starts with `SIM`, and a note that the card data was not stored.

Only two brands are accepted. Visa numbers start with 4. Mastercard numbers start with 51 to 55. The checkout asks you to choose the brand, and the number must start like the card you chose. Any other start is rejected and no order is created.

| Try this | What happens |
| --- | --- |
| Visa `4242 4242 4242 4242` | Accepted, with a future expiry and any 3 or 4 digit CVV |
| Mastercard `5555 5555 5555 4444` | Accepted, on the same terms |
| `4000 0000 0000 0002` or `4000 0000 0000 9995` | Declined. No order, and the stock does not move |

A cheque stays pending until an administrator verifies it and then marks it cleared. Clearing is allowed only after verification. The parcel cannot be dispatched before that. Rejecting the cheque cancels the order and puts the stock back.

VPP can leave the shop before the cash arrives. The employee records the cash when the parcel is delivered. An administrator can also mark the cash collected after dispatch.

## Run it on XAMPP

You need Apache, MySQL or MariaDB, and PHP 8.0 or newer. This copy was tested with PHP 8.3 and MariaDB 10.4. Place the folder inside the XAMPP web root, for example `C:\xampp\htdocs\e_projject_ecomerce_website`.

1. Open the XAMPP Control Panel and start **Apache** and **MySQL**.
2. Leave the MySQL user as `root` with an empty password, the usual XAMPP default. If your password is not empty, change `config/database.php` first.
3. Open the shop. The first visit creates `paperlane_shop` from `database/paperlane_live.sql` when that database is not there yet. That one file is the whole counter: nine categories, the products and their pictures, the customise ranges, staff, customers and the orders already on the desk.
4. Open `setup.php` if you want a readiness check. Once the database exists, that page does not change data.

`config/database.php` already points at `127.0.0.1`, database `paperlane_shop`, user `root`, and an empty password. The site address is detected from the folder name. Product photos are already in `uploads/products/` and `uploads/categories/`, so a clone or a ZIP of this folder has the pictures as well as the pages.

Do not import `database.sql` or `database/sample_data.sql` on top of the live shop. `database.sql` drops `paperlane_shop` and leaves only an empty starter. The sample file is an older short catalogue and its product codes collide with the live ones. To put this exact snapshot back, drop `paperlane_shop` in phpMyAdmin and open the shop again, or import `database/paperlane_live.sql` once. That import drops and recreates the shop.

## Accounts

These are the project defaults, written here so there is no hidden door. Change the administrator password after the first real use.

| Who | Page | Email | Password |
| --- | --- | --- | --- |
| Administrator | `admin/login.php` | `admin@paperlane.local` | `Admin@123` |
| Employee | `employee/login.php` | `employee@paperlane.local` | `Employee@123` |
| Customer | `auth/login.php` | `customer@paperlane.local` | `Customer@123` |

Anyone can also register a new customer at `auth/register.php`. The sample employee is already approved.

The public FAQ page opens the corner agent. It answers the questions stored in the shop, the same ones an administrator edits under Agent answers.

## Where things live

| Folder | Keeps |
| --- | --- |
| `assets/` | Styles, scripts, vendor files and shop images |
| `config/` | Database and application settings, including the Karachi delivery fee |
| `includes/` | Shared PHP: auth, cart, orders, pages and brand marks |
| `auth/` | Customer login, registration and logout |
| `customer/` | Profile, orders and returns |
| `admin/` | The administrator’s desk |
| `employee/` | The dispatch desk |
| `uploads/products/` and `uploads/categories/` | The pictures the shop and customise pages show |
| `database/paperlane_live.sql` | The full shop database used on a fresh open |
| `database/sample_data.sql` | An older short catalogue. Do not import it over the live shop |
| `documentation/` | The Aptech report and the study guide |
| `tests/` | Command-line checks. The browser cannot run them |

Uploaded pictures must be JPG, PNG, WEBP or GIF, at most 2 MB, and must really be images. PHP files cannot run inside `uploads/`.

Passwords are stored with `password_hash`. Queries use PDO prepared statements. Forms carry a CSRF token, pages escape output, and the order transaction is the only place stock is allowed to move.

## The write-up

The report, diagrams and presentation notes are in `documentation/`. Begin with [the table of contents](documentation/03_Table_of_Contents.md) or the [presentation and study guide](documentation/Project_Presentation_and_Study_Guide.md). The pages describe the shop that is in this folder.
