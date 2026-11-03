<?php
require_once __DIR__ . '/../includes/init.php';
$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        if (post('action') === 'refund') {
            issue_refund((int) post('refund_id', 10), (int) $admin['id'], post('notes', 255));
            set_flash('success', 'Refund marked as issued.');
        } else {
            review_return((int) post('return_id', 10), post('decision', 20), (int) $admin['id'], post('notes', 500));
            set_flash('success', 'Request updated.');
        }
    } catch (AppException $e) {
        set_flash('danger', $e->getMessage());
    }
    redirect('admin/returns.php');
}

$requests = db()->query("SELECT r.*, o.order_number, o.payment_status, u.full_name FROM return_requests r JOIN orders o ON o.id = r.order_id JOIN customers c ON c.id = r.customer_id JOIN users u ON u.id = c.user_id ORDER BY FIELD(r.status,'pending','approved','completed','rejected'), r.id DESC")->fetchAll();
$refunds = db()->query("SELECT f.*, o.order_number FROM refunds f JOIN orders o ON o.id = f.order_id ORDER BY FIELD(f.status,'pending','issued'), f.id DESC LIMIT 30")->fetchAll();
$pageTitle = 'Returns';
$active = 'returns';
require __DIR__ . '/../includes/admin_header.php';
?>
<h1>Returns and refunds</h1>
<div class="soft-card mb-4">
    <table class="table align-middle mb-0">
        <thead><tr><th>Order</th><th>Customer</th><th>Type</th><th>Reason</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($requests as $row): ?>
            <tr>
                <td><a class="btn btn-line btn-sm" href="order.php?id=<?= (int) $row['order_id'] ?>"><?= e($row['order_number']) ?></a></td>
                <td><?= e($row['full_name']) ?></td>
                <td><?= e(ucfirst($row['request_type'])) ?></td>
                <td><?= e($row['reason']) ?></td>
                <td><?= status_badge($row['status']) ?></td>
                <td style="min-width:220px">
                    <?php if ($row['status'] === 'pending'): ?>
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="return_id" value="<?= (int) $row['id'] ?>">
                            <textarea class="form-control mb-2" name="notes" rows="2" placeholder="Note to the customer"></textarea>
                            <button class="btn btn-sm btn-forest" name="decision" value="approved" type="submit">Approve</button>
                            <button class="btn btn-sm btn-outline-danger" name="decision" value="rejected" type="submit">Reject</button>
                        </form>
                    <?php elseif ($row['admin_notes']): ?>
                        <span class="small"><?= e($row['admin_notes']) ?></span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$requests): ?><tr><td colspan="6">No requests.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<h2 class="h4">Refunds</h2>
<p>A refund is created only after a cleared payment is cancelled before dispatch, or after a return is approved. Marking it issued does not call a bank.</p>
<div class="table-responsive soft-card">
    <table class="table mb-0">
        <thead><tr><th>Order</th><th>Amount</th><th>Reason</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($refunds as $refund): ?>
            <tr>
                <td><a class="btn btn-line btn-sm" href="order.php?id=<?= (int) $refund['order_id'] ?>"><?= e($refund['order_number']) ?></a></td>
                <td><?= e(money($refund['amount'])) ?></td>
                <td><?= e($refund['reason']) ?></td>
                <td><?= status_badge($refund['status']) ?></td>
                <td>
                    <?php if ($refund['status'] === 'pending'): ?>
                        <form method="post" class="d-flex gap-2">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="refund">
                            <input type="hidden" name="refund_id" value="<?= (int) $refund['id'] ?>">
                            <input class="form-control form-control-sm" name="notes" placeholder="Note">
                            <button class="btn btn-sm btn-forest" type="submit" data-confirm="Mark this refund as issued?">Issue</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$refunds): ?><tr><td colspan="5">No refunds.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
