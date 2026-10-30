# Algorithms and Flowcharts

The diagrams match the functions in `includes/auth.php`, `includes/cart.php` and `includes/orders.php`.

## Login

The same checks run for the customer, admin and employee forms. Only the expected role changes.

```mermaid
flowchart TD
    start[Open the login page] --> post{Form posted?}
    post -->|No| show[Show the form]
    post -->|Yes| csrf{CSRF token matches?}
    csrf -->|No| expired[Ask the user to try again]
    csrf -->|Yes| lock{Locked after 5 failures?}
    lock -->|Yes| wait[Ask them to wait two minutes]
    lock -->|No| find{Email and password match an active user?}
    find -->|No| fail[Count the failure]
    find -->|Yes| role{Role matches this page?}
    role -->|No| wrong[Tell them which door to use]
    role -->|Yes| session[Regenerate the session and store the user id]
    session --> merge{Customer?}
    merge -->|Yes| cart[Merge the guest cart into the database cart]
    merge -->|No| desk[Open that role dashboard]
    cart --> shop[Open the shop or the page they wanted]
```

A correct password on the wrong door does not create a session.

## Cart

```mermaid
flowchart TD
    add[Add, update or remove] --> who{Logged-in customer?}
    who -->|No| session[Change the session cart]
    who -->|Yes| db[Change cart_items for that user]
    session --> show[Reload the cart from current prices]
    db --> show
    show --> stock{Line quantity above stock or product inactive?}
    stock -->|Yes| block[Show the cart but block checkout]
    stock -->|No| ready[Allow checkout]
```

The browser never sends the line price. `cart_details()` reads `products.price`.

## Checkout and payment

```mermaid
flowchart TD
    start[Customer posts checkout] --> auth{Logged in as customer?}
    auth -->|No| login[Send them to the customer login]
    auth -->|Yes| lines{Cart ready and every line in stock?}
    lines -->|No| cart[Return to the cart]
    lines -->|Yes| pay{Delivery type}
    pay -->|Card| sim{Simulated card accepted?}
    sim -->|No| stop[Show the decline. Write nothing]
    sim -->|Yes| txn[Start a transaction]
    pay -->|Cheque or VPP| txn
    txn --> lock[Lock product rows in id order]
    lock --> enough{Still enough stock?}
    enough -->|No| rollback[Roll back]
    enough -->|Yes| number[Take the next sequence]
    number --> write[Insert order, items and payment]
    write --> deduct[Insert stock movements and reduce stock]
    deduct --> commit[Commit]
    commit --> clear[Empty the cart]
```

Card success stores status cleared and order status confirmed. Cheque starts as pending and pending. VPP starts as confirmed and payment pending. A decline throws before `place_order()`, so stock is unchanged.

## Dispatch

```mermaid
flowchart TD
    open[Employee or admin opens the order] --> see{Employee allowed to see it?}
    see -->|No| hide[Hide the order]
    see -->|Yes| can{Status confirmed, not dispatched, and payment cleared or VPP?}
    can -->|No| wait[Do not show dispatch]
    can -->|Yes| save[Insert a dispatch record, set dispatch_date and status dispatched]
```

Delivery is a second step. For unpaid VPP, the delivery report can mark the cash collected in the same transaction. A failed delivery note does not mark the order delivered.

## Cancel, return and refund

```mermaid
flowchart TD
    cancel[Customer asks to cancel] --> before{Pending or confirmed, and no dispatch date?}
    before -->|No| deny[Refuse]
    before -->|Yes| restore[Set cancelled, restore stock]
    restore --> money{Payment was cleared?}
    money -->|Yes| refundRow[Create a pending refund]
    money -->|No| failed[Mark the payment failed]

    ret[Customer asks for a return] --> window{Delivered and within 7 days and no open request?}
    window -->|No| deny2[Refuse]
    window -->|Yes| pending[Save a pending request]
    pending --> admin{Admin decision}
    admin -->|Reject| rejected[Store the note]
    admin -->|Approve return| restock[Restock and, if payment was cleared, create a refund]
    admin -->|Approve replacement| swap[Restock and then issue the same quantity again]
    refundRow --> issue[Admin marks the refund issued]
    restock --> issue
    issue --> book[Payment becomes refunded. No bank is called]
```
