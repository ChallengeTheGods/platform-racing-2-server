<?php

//--- creates a player if the log in was successful -----------------------
function process_register_login($server_socket, $data)
{
    if ($server_socket->process == true) {
        global $login_array, $player_array, $guild_id, $guild_owner;

        $login_obj = json_decode($data);
        $login_id = $login_obj->login->login_id;
        $group = $login_obj->user->power;
        $user_id = $login_obj->user->user_id;

        $socket = @$login_array[$login_id];
        unset($login_array[$login_id]);

        if (isset($socket)) {
            if (!$server_socket->process) {
                $socket->write('message`Login verify failed.');
                $socket->close();
                $socket->onDisconnect();
            } elseif ($guild_id != 0 && $guild_id != $login_obj->user->guild) {
                $socket->write('message`You are not a member of this guild.');
                $socket->close();
                $socket->onDisconnect();
            } elseif (isset($player_array[$user_id])) {
                $existing_player = $player_array[$user_id];
                $existing_player->write('message`You were disconnected because you logged in somewhere else.');
                $existing_player->remove();

                $socket->write('message`Your account was already running on this server. '.
                    'It has been logged out to save your data. Please log in again.');
                $socket->close();
                $socket->onDisconnect();
            } elseif (\pr2\multi\LocalBans::isBanned($login_obj->user->name)) {
                $socket->write('message`You have been kicked from this server for 30 minutes.');
                $socket->close();
                $socket->onDisconnect();
            } else {
                $player = new \pr2\multi\Player($socket, $login_obj);
                $socket->player = $player;
                if ($player->user_id == $guild_owner) {
                    $player->becomeServerOwner();
                } elseif ($player->group <= 0) {
                    $player->becomeGuest();
                }

                $socket->write('loginSuccessful`'.$group);
                $socket->write('setRank`'.$player->active_rank);
                $socket->write('ping`' . time());
            }
        }
    }
}
