<?php
// Simple mail helper using mail() and configurable from init.php
function send_mail($to, $subject, $body, $headers = []) {
    $defaultHeaders = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
    ];
    $all = array_merge($defaultHeaders, $headers);
    return mail($to, $subject, $body, implode("\r\n", $all));
}
