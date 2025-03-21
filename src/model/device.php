<?php
/**
 * Contains database functions for the devices.
 */

require_once HELPERS_DIR . 'DB.php';

/**
 * Adds device to device table.
 * 
 * @author Pekka Tapio Aalto
 * 
 * @param string $tag     Identity tag, random generated.
 * @param string $token   Authorization token, random genarated.
 * @param string $ip      IP address.
 * @param string $width   Width of the screen.
 * @param string $height  Height of the screen.
 * @param string $version Version of the player.
 * @return int            Id of the inserted row.
 */
function addDevice($tag, $token, $ip, $width, $height, $version) {
  DB::run('INSERT INTO signify_device (tag, token, ip, width, height, version) VALUES (?, ?, ?, ?, ?, ?);',[$tag, $token, $ip, $width, $height, $version]);
  return DB::lastInsertId();
}

/**
 * Check the existence on device token.
 * 
 * Returns the number of tokens found in table. 
 * Returns 1 if token exsists, otherwise returns 0.
 * 
 * @author Pekka Tapio Aalto
 * 
 * @param string $token   Authorization token.
 * @return int            Count of the API tokens in database.
 */
function checkDeviceToken($token) {
  return DB::run('SELECT COUNT(token) FROM signify_device WHERE token =  ?;',[$token])->fetchColumn();
}

/**
 * Check the the lists allocated to the device.
 *  
 * @author Pekka Tapio Aalto
 * 
 * @param int $deviceid   Id of the device.
 * @return array          Lists allocated to device.
 */
function getDeviceLists($deviceid) {
  $query = 'SELECT   dl.row,
                     dl.id_list,
                     l.name, 
                     l.duration,
                     l.active_from, 
                     l.active_due, 
                     l.active, 
                     GREATEST(dl.updated, l.updated) AS updated 
            FROM     signify_devicelist AS dl 
            JOIN     signify_list AS l USING (id_list) 
            WHERE    id_device = ? 
            ORDER BY row;';
  return DB::run($query,[$deviceid]);
}

/**
 * Gets data of the given device.
 *
 * @author Pekka Tapio Aalto
 *
 * @param string $tag     Tag of the device.
 * @return array          Device's data.
 */
function getDeviceWithTag($tag) {
  return DB::run('SELECT * FROM signify_device WHERE tag =  ?;',[$tag])->fetch();
}

/**
 * Gets data of the given device.
 *
 * @author Pekka Tapio Aalto
 *
 * @param string $token   Authorization token.
 * @return array          Device's data.
 */
function getDeviceWithToken($token) {
  return DB::run('SELECT * FROM signify_device WHERE token =  ?;',[$token])->fetch();
}

/**
 * Links device updating device name and descr.
 *
 * @author Pekka Tapio Aalto
 *
 * @param int $iddevice   Id of the device.
 * @param string $name    Name of the device.
 * @param string $descr   Device description.
 * @return int            Row count of the updated rows.
 */
function linkDevice($iddevice, $name, $descr) {
  return DB::run('UPDATE signify_device SET name = ?, descr = ? WHERE id_device = ?', [$name, $descr, $iddevice])->rowCount();
}

/**
 * Update device's screen width and height.
 *
 * @author Pekka Tapio Aalto
 *
 * @param int $iddevice   Id of the device.
 * @param string $width   Width of the screen.
 * @param string $height  Height of the screen.
 * @return int            Row count of the updated rows.
 */
function updateScreenSize($iddevice, $width, $height) {
  return DB::run('UPDATE signify_device SET width = ?, height = ? WHERE id_device = ?', [$width, $height, $iddevice])->rowCount();
}