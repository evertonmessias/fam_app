<?php

namespace App\System;

use App\System\API_Key;

class API_Totp {
    // Generates a TOTP key valid for current TIME + next time (default = 3600, 1h, so we get current hour + next hour)
    public static function generate (API_Key $apiKey, int $time = 3600) {
        // Create a TOTP counter
        $counter1 = floor(time() / $time);
        $counter2 = floor($counter1 + 1);

        // Create a TOTP validator code pair
        $validator1 = strrev(sha1($apiKey->key . strrev($counter1) . $apiKey->id));
        $validator2 = strrev(sha1($apiKey->key . strrev($counter2) . $apiKey->id));

        // Create a TOTP validator passcode pair
        $passcode1 = password_hash($validator1, PASSWORD_BCRYPT);
        $passcode2 = password_hash($validator2, PASSWORD_BCRYPT);

        // Return all of them
        return [
            'public' => [$passcode1, $passcode2],
            'public_string' => base64_encode($passcode1) . ':' . base64_encode($passcode2),
            'private' => [$validator1, $validator2]
        ];
    }

    // Validates a token with TOTP
    public static function validate (API_Key $apiKey, $totp, int $time = 3600) {
        // Checks if our totp is a string (public_string) and decodes into array
        if (is_string($totp)) {
            // Decode strings
            $totp = explode(':', $totp);
            $totp = [
                base64_decode($totp[0]),
                base64_decode($totp[1])
            ];
        }

        // Tests data consistency
        if (
            is_null($apiKey) ||
            empty($totp) ||
            !is_array($totp) ||
            count($totp) != 2
        ) return false;

        // Tests if our supplied API Key is valid
        if (!API_Key::validate($apiKey->key, $apiKey->private_key)) return false;

        // Generates a pair of public (totp) and private keys for validation
        $current = static::generate($apiKey, $time);

        // Tests private0 against public0 (totp0)
        if (password_verify($current['private'][0], $totp[0])) return true;

        // Tests private0 against public1 (totp1)
        if (password_verify($current['private'][0], $totp[1])) return true;

        // Tests private1 against public1 (totp1)
        if (password_verify($current['private'][1], $totp[1])) return true;

        // Tests private1 against public0 (totp0)
        if (password_verify($current['private'][1], $totp[0])) return true;

        // Returns false if no token was verified (default)
        return false;
    }
}