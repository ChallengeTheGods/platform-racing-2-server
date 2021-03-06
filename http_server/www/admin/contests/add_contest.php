<?php

require_once __DIR__ . '/../../../fns/all_fns.php';
require_once __DIR__ . '/../../../fns/output_fns.php';
require_once __DIR__ . '/../../../queries/contests/contest_insert.php';
require_once __DIR__ . '/../../../queries/staff/actions/admin_action_insert.php';
require_once __DIR__ . '/add_contest_fns.php';

$ip = get_ip();
$action = find('action', 'form');

try {
    // rate limiting
    rate_limit('add-contest-'.$ip, 30, 5);
    rate_limit('add-contest-'.$ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // make sure you're an admin
    $admin = check_moderator($pdo, true, 3);
} catch (Exception $e) {
    output_header('Error');
    echo 'Error: ' . $e->getMessage();
    output_footer();
    die();
}

try {
    // form
    if ($action === 'form') {
        output_header('Add Contest', true, true);
        output_form();
        output_footer();
        die();
    } // add
    elseif ($action === 'add') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request type.');
        }
        add_contest($pdo, $admin);
    } // unknown handler
    else {
        throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "Error: $error<br><br><a href='javascript:history.back()'><- Go Back</a>";
    output_footer();
    die();
}
