# Installation and User Manual

## Install

1. Copy the project to `C:\xampp\htdocs\e_projject_ecomerce_website`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. In phpMyAdmin, import `database.sql`.
4. Import `database/sample_data.sql` if you want twelve products, one employee and one sample customer.
5. Open `http://localhost/e_projject_ecomerce_website/`.
6. If the database password is not empty, set it in `config/database.php`.

Importing `database.sql` deletes the database and creates it again. Run the sample file only once after that.

## Logins

| Role | Address | Email | Password |
| --- | --- | --- | --- |
| Administrator | `/admin/login.php` | `admin@paperlane.local` | `Admin@123` |
| Employee, after the sample file | `/employee/login.php` | `employee@paperlane.local` | `Employee@123` |
| Sample customer | `/auth/login.php` | `customer@paperlane.local` | `Customer@123` |

Customers can also use **Create an account**. Staff must use the footer links. The shop login will not accept the admin password as a customer session.

Five wrong passwords lock that browser for two minutes.

## Customer guide

1. Open **Shop** or a category. You do not need an account to look.
2. Open a product, choose a quantity and press **Add to cart**.
3. In the cart, change a quantity or remove a line. The total is recalculated from the database price. Delivery is Rs 99, or free from Rs 1,500 of goods.
4. Press through to checkout. If you are not logged in, log in or register. The cart is kept.
5. Choose a saved address or type a new one.
6. Choose a payment:
   - **Credit card:** this is a practice payment. Use `4242424242424242`, a future month and year, and `123` as the CVV. The shop stores only the last four digits. `4000000000000002` is rejected and no order is saved.
   - **Cheque:** enter the cheque number, bank and date. The shop holds the parcel until an administrator clears the cheque.
   - **VPP:** pay the rider in cash. The parcel may leave before the cash is marked collected.
7. The next page shows the 16-digit order number. Open **Track this order** to see the status.
8. **Cancel order** appears only before dispatch.
9. After delivery, **Return or replacement** appears for seven days.
10. **Feedback** sends a note to the administrator. **Questions** opens the FAQ.

## Administrator guide

1. Log in at the admin page.
2. Add a category with a 2-digit code that is not already used, such as `05`.
3. Add a product. The product ID is assigned for you. Upload at least one JPG, PNG, WEBP or GIF under 2 MB. Set the price, stock and warranty. Featured products appear on the home page.
4. Open **Orders**. Filter by date, delivery type, customer or status.
5. For a cheque, use **Verify** and then **Clear**. Reject cancels the order and restores stock. Do not clear a cheque that was not verified.
6. Open **Returns** to approve or reject a request. **Issue** on a refund only updates the shop. It does not pay a bank.
7. Read **Feedback**, edit **FAQs**, and open **Reports** for payments, dispatch, delivery and stock. CSV downloads the current filter. Print uses the browser print dialog.
8. **Employees** creates a staff login. Deactivate stops that person logging in. Employees cannot be given the admin role from that form.

## Employee guide

1. Log in at the employee page.
2. **Orders** lists parcels you are allowed to see: confirmed, dispatched or delivered, and only VPP or already cleared payments.
3. Open a parcel, check the address, and mark it dispatched when the button is there. A cheque that is not cleared has no dispatch button.
4. After dispatch, save a delivery report. For unpaid cash on delivery, leave **Cash collected** ticked only if you received the money.
5. Change your password under **Password**. You cannot change product prices or create another employee.

## If something fails

- A blank database error on every page: MySQL is stopped, or `database.sql` was not imported.
- Images missing after a new product: the file must be a real image under 2 MB. The names are stored in `uploads/products/`.
- "The form expired": go back and submit once. Do not reuse an old tab from before login.
- A product will not delete a category: remove or move the products first. The prefix stays once products exist.
