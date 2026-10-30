# Problem Definition

## Existing way of working

A small gift and stationery shop often works like this:

- Products and prices are written in a register or remembered by the owner.
- A customer has to visit or phone the shop to ask what is in stock.
- An order is a handwritten slip. It is easy to lose, and two people can sell the last item.
- Payment by card, cheque or cash on delivery is noted in different places.
- The shop may dispatch a cheque order before the cheque has cleared.
- After delivery, a return is agreed in conversation and the stock book is updated late, or not at all.
- There is no single page where the customer can see whether the parcel has left the shop.

## Problems in that way of working

- Stock figures go wrong.
- Order numbers are not unique or cannot be explained.
- Payment status and delivery status are mixed up.
- Customers cannot see their own history.
- Staff who only deliver parcels can still change prices if they share one login.

## Proposed system

Paperlane keeps the catalogue, cart, orders, payments, stock movements, dispatch notes, returns and refunds in MySQL. The public site shows only active products. Placing an order locks the product rows, checks the quantity, writes the order and the payment, and reduces stock in one transaction.

Card and cheque orders cannot be dispatched until the payment status is cleared. Cash-on-delivery orders can leave earlier, and the cash is recorded at delivery. Cancellation is offered only before dispatch. The return form is offered only for seven days after the delivery date. A refund record is created only after a cleared payment is cancelled or a return is approved. Issuing that refund is a shop record, not a bank transfer.

The administrator, the employee and the customer each have a role. The wrong role cannot open the other panel.
