<?php
$body = preg_replace("|<style\b[^>]*>(.*?)</style>|s", "", $body);
$body = strip_tags($body);
$body = preg_replace('/(?:(?:\r\n|\r|\n)\s*){2}/s', "\n\n", $body);
$body = str_replace("&nbsp;", "", $body);
$body = preg_replace('/\x20+/', ' ', $body);
echo $body;
?>
