<?php
/*
 * object to handle several nafleagues. 
 * list of nafleague 
 */
class nafleagues
{
    public function __construct() {
        ;
    }
    
    /*
     * return all active leagues
     */
    public static function get()
    {
        global $wpdb;
        $q = '
                SELECT * FROM '.$wpdb->prefix . 'nafleagues WHERE status = "active" OR status = "pending" ORDER BY lastupdate DESC
            ';
        $rows = $wpdb->get_results($q);
        return $rows;
    }
    
    /*
     * return all leagues that should be desactivated (6month inactivity)
     */
    public static function getToPending()
    {
        global $wpdb;
        $q = '
                SELECT * FROM '.$wpdb->prefix . 'nafleagues 
                WHERE 
                status = "active" 
                AND  
                lastupdate < DATE_ADD( NOW( ) , INTERVAL '.NAFLEAGUES_LIMIT_PENDING.')
            ';
        $rows = $wpdb->get_results($q);
        return $rows;
    }
    /*
     * return all leagues that should be desactivated (6month inactivity)
     */
    public static function getToOutdate()
    {
        global $wpdb;
        $q = '
                SELECT * FROM '.$wpdb->prefix . 'nafleagues 
                WHERE 
                status != "outdated"
                AND
                lastupdate < DATE_ADD( NOW( ) , INTERVAL '.NAFLEAGUES_LIMIT_OUTDATED.')
            ';
        $rows = $wpdb->get_results($q);
        return $rows;
    }
    
    
    
    
}
?>

