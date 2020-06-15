<?php
/* https://www.icosaedro.it/phplint/phplint2/doc/tutorial.htm#H13_The_mixed_type
* This file provides a simple cast() function that just returns its second
* argument, so that I can use PHPLint's cast support without using its
* implementation.
 */

function cast(string $unused, /*. mixed .*/ $value) {
  return $value;
}

?>
