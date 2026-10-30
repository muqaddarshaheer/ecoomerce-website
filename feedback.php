<?php
require_once __DIR__ . '/includes/init.php';

$user = current_user();
$error = '';
$form = [
    'name' => $user['full_name'] ?? '',
    'email' => $user['email'] ?? '',
    'subject' => '',
    'message' => '',
    'rating' => '5',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (post('website') !== '') {
        $error = 'The message could not be sent.';
    } else {
        $form['name'] = post('name', 100);
        $form['email'] = strtolower(post('email', 150));
        $form['subject'] = post('subject', 150);
        $form['message'] = post('message', 2000);
        $form['rating'] = (string) (int) post('rating', 1);
        if ($form['name'] === '' || !valid_email($form['email']) || strlen($form['subject']) < 3 || strlen($form['message']) < 10) {
            $error = 'Enter your name, a valid email, a subject and a message of at least 10 characters.';
        } elseif ((int) $form['rating'] < 1 || (int) $form['rating'] > 5) {
            $error = 'Choose a rating from 1 to 5.';
        } else {
            db()->prepare('INSERT INTO feedback (user_id, name, email, subject, message, rating, status) VALUES (?,?,?,?,?,?,\'new\')')
                ->execute([$user['id'] ?? null, $form['name'], $form['email'], $form['subject'], $form['message'], (int) $form['rating']]);
            set_flash('success', 'Thank you. The shop has received your note.');
            redirect('feedback.php');
        }
    }
}

$pageTitle = 'Feedback';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4" style="max-width:720px">
    <h1>Feedback</h1>
    <p>Tell us about an order, a product or the shop. An administrator reads these notes.</p>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="soft-card">
        <?= csrf_field() ?>
        <div class="hp"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
        <label class="form-label">Name</label>
        <input class="form-control mb-3" name="name" value="<?= e($form['name']) ?>" required>
        <label class="form-label">Email</label>
        <input class="form-control mb-3" type="email" name="email" value="<?= e($form['email']) ?>" required>
        <label class="form-label">Subject</label>
        <input class="form-control mb-3" name="subject" value="<?= e($form['subject']) ?>" required>
        <label class="form-label">Rating</label>
        <select class="form-select mb-3" name="rating">
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <option value="<?= $i ?>" <?= (int) $form['rating'] === $i ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
        </select>
        <label class="form-label">Message</label>
        <textarea class="form-control mb-3" name="message" rows="5" required><?= e($form['message']) ?></textarea>
        <button class="btn btn-clay" type="submit">Send feedback</button>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
