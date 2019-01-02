<?php
function set_url(string $url) {
  $_SERVER['REQUEST_URI'] = $url;
}

function set_hostname(string $hostname) {
  $_SERVER['SERVER_NAME'] = $hostname;
}

?>
