<?php

namespace AZO;

// Don't redefine the functions if included multiple times.
if (!\function_exists('AZO\\GuzzleHttp\\Promise\\promise_for')) {
    require __DIR__ . '/functions.php';
}
