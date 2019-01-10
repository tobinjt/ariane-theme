<?php
function clear_server_variables() {
  // Can't replace $_SERVER entirely because it breaks phpunit.
  unset($_SERVER['REQUEST_URI']);
  unset($_SERVER['SERVER_NAME']);
}

function set_url(string $url) {
  $_SERVER['REQUEST_URI'] = $url;
}

function set_hostname(string $hostname) {
  $_SERVER['SERVER_NAME'] = $hostname;
}
