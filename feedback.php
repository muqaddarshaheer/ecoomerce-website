<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

verify_csrf();
$user = current_user();
if (post('website') !== '') {
    set_flash('danger', 'The message could not be sent.');
    redirect('index.php');
}

$name = post('name', 100);
$email = strtolower(post('email', 150));
$subject = post('subject', 150);
$message = post('message', 2000);
$rating = (int) post('rating', 1);

if ($name === '' || !valid_email($email) || strlen($subject) < 3 || strlen($message) < 10) {
    set_flash('danger', 'Enter your name, a valid email, a subject and a message of at least 10 characters.');
    redirect('index.php');
}
if ($rating < 1 || $rating > 5) {
    set_flash('danger', 'Choose a rating from 1 to 5.');
    redirect('index.php');
}

db()->prepare('INSERT INTO feedback (user_id, name, email, subject, message, rating, status) VALUES (?,?,?,?,?,?,\'new\')')
    ->execute([$user['id'] ?? null, $name, $email, $subject, $message, $rating]);
set_flash('success', 'Thank you. The shop has received your note.');
redirect('index.php');
