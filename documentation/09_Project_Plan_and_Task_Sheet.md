# Project Plan and Task Sheet

Student name: ________________________    Enrollment: ________________________

The task list matches the modules that are in the project. The status column records that the module exists in the code. The date and remark columns are for the student and the guide.

| No. | Task | Output | Status | Date | Remark |
| --- | --- | --- | --- | --- | --- |
| 1 | Agree the three roles and the product range | Requirement notes | Done in code | | |
| 2 | Design tables, keys and the order-number rule | `database.sql` | Done | | |
| 3 | Configure PDO, sessions, CSRF and password hashing | `config/`, `includes/` | Done | | |
| 4 | Build the public home, shop, product, FAQ and about pages | Public PHP files | Done | | |
| 5 | Build registration, login, profile and cart | `auth/`, `cart.php`, `customer/profile.php` | Done | | |
| 6 | Build checkout, payments and stock transactions | `checkout.php`, `includes/orders.php` | Done | | |
| 7 | Build order tracking, cancel and returns | `customer/order.php` | Done | | |
| 8 | Build the admin catalogue, staff, orders and reports | `admin/` | Done | | |
| 9 | Build the employee desk | `employee/` | Done | | |
| 10 | Add photographs, styles and optional sample products | `assets/`, `uploads/`, `database/sample_data.sql` | Done | | |
| 11 | Test the main paths and record the results | `documentation/15_Unit_Testing_Checklist.md` | Done | | |
| 12 | Write the report and the viva guide | `documentation/` | Done | | |

## Suggested order of work for a student explaining the project

1. Show the home page and a product ID.
2. Register or log in as the sample customer and place one small order.
3. Read the 16-digit number aloud and split it into delivery type, product and sequence.
4. Log in as the employee and dispatch only if the payment rule allows it.
5. Log in as the administrator and show stock, the payment row and a report.
6. Show that the customer login cannot open `admin/index.php`.

## Risks that were handled in the code

| Risk | What the code does |
| --- | --- |
| Two customers buy the last item | Product rows are locked in id order inside the order transaction |
| The browser sends a fake total | The server ignores it and prices the cart from the database |
| A card number is saved by mistake | Only the last four digits and the brand are written |
| A cheque leaves before it clears | Dispatch checks payment status |
| Refreshing a page deducts stock again | Stock changes only inside the status change, and the status is updated conditionally |
