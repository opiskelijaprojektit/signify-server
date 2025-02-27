<?php
/**
 * Contains common functions which are not project specific.
 */

 /**
 * Create a token for authentication.
 *
 * @return string Returns a randomly generated token string.
 */
function createAuthToken() {
  return bin2hex(random_bytes(64));
}

/**
 * Create a random string of the desired length.
 *
 * @param int $length Length of the string to be generated.
 * @return string     Generated random string.
 */
function createHash($length) {

  // Define the characters used in a string.
  $chars = "2346789BCDFGHJKMPQRTVWXY";

  // Count the number of characters in use.
  $charcount = strlen($chars);

  // The variable where the result is stored.
  $result = "";

  // Repeat for the length of the string to be formed.
  for ($i = 1; $i <= $length; $i++) {

    // Randomly select the index number of the character. 
    // If there are 24 characters, then the index will 
    // take a value between 1-24.
    $index = rand(1, $charcount);

    // Find the character corresponding to the sequence 
    // number from the string of characters that use it. 
    // The string can be treated like a table, with the 
    // first character in index position 0.
    $char = $chars[$index - 1];

    // Append the character to the end of the result variable.
    $result = $result . $char;

  }

  // Returns the generated string.
  return $result;

}

/**
 * Find out the IP address of the browser.
 *
 * @return string         IP address of the browser.
 */
function getIPaddress() {
  return isset($_SERVER['HTTP_CLIENT_IP'])
         ? $_SERVER['HTTP_CLIENT_IP']
         : (isset($_SERVER['HTTP_X_FORWARDED_FOR'])
           ? $_SERVER['HTTP_X_FORWARDED_FOR']
           : $_SERVER['REMOTE_ADDR']);    
}

?>