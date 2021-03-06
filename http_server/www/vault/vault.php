<?php

require_once __DIR__ . '/../../fns/all_fns.php';
require_once __DIR__ . '/../../queries/users/user_select_expanded.php';
require_once __DIR__ . '/../../queries/servers/server_select.php';
require_once __DIR__ . '/../../queries/guilds/guild_select.php';
require_once __DIR__ . '/../../queries/rank_token_rentals/rank_token_rentals_count.php';
require_once __DIR__ . '/vault_fns.php';

header("Content-type: text/plain");

$ip = get_ip();

try {
    // rate limiting
    rate_limit('vault-listing-'.$ip, 3, 1);
    rate_limit('vault-listing-'.$ip, 15, 4);

    // connect
    $pdo = pdo_connect();

    // get login
    $user_id = token_login($pdo);

    // more rate limiting
    rate_limit('vault-listing-'.$user_id, 5, 2);
    rate_limit('vault-listing-'.$user_id, 30, 10);

    // create listing
    $raw_listings = describeVault(
        $pdo,
        $user_id,
        [
            'stats-boost',
            'epic-everything',
            'guild-fred',
            'guild-ghost',
            'guild-artifact',
            'happy-hour',
            'rank-rental',
            'djinn-set',
            'king-set',
            'queen-set',
            'server-1-day',
            'server-30-days'
        ]
    );

    // weed out only the info we want to return
    $listings = array();
    foreach ($raw_listings as $raw) {
        $listings[] = make_listing($raw);
    }

    // reply
    $r = new stdClass();
    $r->success = true;
    $r->listings = $listings;
    $r->title = 'Vault of Magics';
    $r->sale = false;
    echo json_encode($r);
} catch (Exception $e) {
    $r = new stdClass();
    $r->state = 'canceled';
    $r->error = $e->getMessage();
    echo json_encode($r);
}
