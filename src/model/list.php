<?php
/**
 * Contains database functions for the lists.
 */

require_once HELPERS_DIR . 'DB.php';

/**
 * Get the content of the list.
 * 
 * @author Pekka Tapio Aalto
 * 
 * @param int $listid     Id of the list.
 * @return array          List's rows.
 */
function getListRows($listid) {
  $query = 'SELECT    lr.id_listrow,
                      lr.row,
                      lr.type AS rowtype,
                      lr.content AS contentid,                        
                      c.name AS contentname,
                      c.type AS contenttype,
                      c.data AS contentdata,
                      c.duration AS contentduration,
                      lr.list AS listid,
                      l.name AS listname,
                      l.duration AS listduration,
                      l.active_from,
                      l.active_due,
                      l.active,
                      GREATEST(lr.updated, COALESCE(c.updated,0), COALESCE(l.updated,0)) AS updated 
            FROM      signify_listrow AS lr
            LEFT JOIN signify_content AS c ON c.id_content = lr.content
            LEFT JOIN signify_list AS l ON l.id_list = lr.list
            WHERE     lr.id_list = ? 
            ORDER BY  row;';
  return DB::run($query,[$listid]);
}