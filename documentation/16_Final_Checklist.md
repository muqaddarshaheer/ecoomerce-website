# Final Checklist

Use this before a demonstration. Tick a line only after you have looked at it.

## Setup

- [ ] Apache and MySQL are running in XAMPP
- [ ] `database.sql` has been imported
- [ ] `database/sample_data.sql` has been imported if you want the sample products
- [ ] `http://localhost/e_projject_ecomerce_website/setup.php` loads without a database error
- [ ] `uploads/products` can receive new images

## Shop

- [ ] Home page shows the banner and category photos
- [ ] A product page shows a 7-digit product ID and a photo that loads
- [ ] Search and a price filter return the expected products
- [ ] A visitor can open the shop without logging in
- [ ] Checkout sends a visitor to the customer login

## Customer

- [ ] Registration and login work
- [ ] Cart quantity and total change together
- [ ] A successful test card creates a 16-digit order number
- [ ] Decline card `4000000000000002` does not create an order
- [ ] The customer can open only that customer's orders
- [ ] Cancel is available before dispatch and gone after dispatch
- [ ] A delivered order offers a return for seven days

## Staff

- [ ] Admin can add a category and a product with a picture
- [ ] Admin can see the order, reports, feedback and FAQs
- [ ] Employee can dispatch a cleared or VPP order and save a delivery note
- [ ] Employee cannot open the admin product page
- [ ] Customer cannot open the admin dashboard

## Rules you should be able to say aloud

- [ ] Product ID is 2 digits plus 5 digits
- [ ] Order number is 1 delivery digit plus the lowest product ID plus 8 sequence digits
- [ ] Card and cheque wait for clearance. VPP can leave first
- [ ] The card page is a simulation. The full card number is not stored
- [ ] A refund record is not a bank transfer

## Documents

- [ ] README accounts match the database you imported
- [ ] The study guide matches the screens you will show
- [ ] Certificate and review forms still have blank student and institute lines
