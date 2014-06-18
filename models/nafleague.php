<?php
/*object to handle a unique nafleague
 */
class nafleague
{
    public $id;
    public $status;
    public $activationcode; 
    public $name;
    public $text;
    public $url;
    public $authoremail;
    public $address;
    public $city;
    public $country;
    public $lng;
    public $lat;
              
              
    public function __construct( $id = null ) {
    	    if($id)
    	    {
    	    	    $this->loadFromId($id);
    	    }
    }
    
    public function loadFromUniqid( $activationcode)
    {
    	    global $wpdb;
		$query = 'SELECT * FROM '. $wpdb->prefix . "nafleagues".' WHERE activationcode = "'.$wpdb->escape($activationcode).'"';
		
		$r = $wpdb->get_row($query, OBJECT);
        	$this->populateFrom($r);        	
		return $r;
    	    
    }
    public function loadFromId( $id )
    {
    	    global $wpdb;
		$query = 'SELECT * FROM '. $wpdb->prefix . "nafleagues".' WHERE id = "'.$wpdb->escape($id).'"';
		
		$r = $wpdb->get_row($query, OBJECT);
        	$this->populateFrom($r);
		return $r;
    	    
    }
    
    private function populateFrom($r)
    {
    	    
		$this->id=$r->id;
		$this->status=$r->status;
		$this->activationcode=$r->activationcode; 
		$this->name=$r->name;
		$this->description=$r->description;
		$this->url=$r->url;
		$this->imageurl=$r->imageurl;
		$this->authoremail=$r->authoremail;
		$this->address=$r->address;
		$this->city=$r->city;
		$this->country=$r->country;
		$this->lng=$r->lng;
		$this->lat=$r->lat;
		$this->lastupdate = $r->lastupdate;
		$this->text=$r->text;
		
		
    }
    
    /*
     * create new league from post datas
     * or update datas according to post datas
     */
    public function updateFromPost()
    {
	$POST = array_map( 'stripslashes_deep', $_POST);
        //check activation code
        if( isset ($POST['activationcode'] ) )
        {
            //TODO handle activation code to prevent any one to change any league;
        }
        
        //update fields
        $this->id = mysql_real_escape_string( $POST['nafleague_id']);
        $this->name = $POST['nafleague_name'];
        $this->url = $POST['nafleague_url'];
        $this->imageurl = $POST['nafleague_imageurl'];
        $this->authoremail = $POST['nafleague_email'];
        $this->description = $POST['nafleague_description'];
        $this->address = $POST['nafleague_address'];
        $this->city = $POST['nafleague_city'];
        $this->country = $POST['nafleague_country'];
        
        //update status
        $this->status = 'pending';
        
    }
    
    public function renewActivationcode()
    {
    	    $this->activationcode = md5( uniqid().time() );
    }
    
    public function save( $updateDate = true )
    {
    	//test empty fields
    	if($this->name.$this->authoremail == '' )return false;
    	
        //update geolocalisation
        $location = $this->address.' '.$this->city.' '.$this->country;
        $wsurl = 'http://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($location).'&sensor=false';
        $data = file_get_contents($wsurl);
	
        $oLocation = json_decode($data);
        if($oLocation->status != 'OK')
        {
        	echo '<span class="warning">Geolocalisation failed.</span>';
        	//die('Geolocalisation failed : '.$oLocation->status.' <br/>');
        }
        $this->lat = $oLocation->results[0]->geometry->location->lat;
        $this->lng = $oLocation->results[0]->geometry->location->lng;
        
        global $wpdb;
        $id = ((int)$this->id)?(int)$this->id:'null';
        $q = '
                INSERT INTO '.$wpdb->prefix . 'nafleagues  (
                `id` ,
                `activationcode` ,
                `name` ,
                `url` ,
                `imageurl` ,
                `authoremail` ,
                `lng` ,
                `lat` ,
                `status` ,
                `address` ,
                `city` ,
                `country` ,
                `description`
                )
                VALUES (

                    '.$id.' ,
                    "'.$wpdb->_escape( $this->activationcode ).'", 
                    "'.$wpdb->_escape( htmlentities($this->name) ).'", 
                    "'.$wpdb->_escape( $this->url ).'", 
                    "'.$wpdb->_escape( $this->imageurl ).'", 
                    "'.$wpdb->_escape( $this->authoremail ).'",
                    "'.$wpdb->_escape( $this->lng ).'",
                    "'.$wpdb->_escape( $this->lat ).'",
                    "'.$wpdb->_escape( $this->status ).'",
                    "'.$wpdb->_escape( $this->address ).'",
                    "'.$wpdb->_escape( $this->city ).'",
                    "'.$wpdb->_escape( $this->country ).'",
                    "'.$wpdb->_escape( $this->description ).'"
                )
                ON DUPLICATE KEY UPDATE
                name = "'.$wpdb->_escape( htmlentities( $this->name ) ).'", 
                activationcode = "'.$wpdb->_escape( $this->activationcode ).'", 
                url =    "'.$wpdb->_escape( $this->url ).'", 
                imageurl =    "'.$wpdb->_escape( $this->imageurl ).'", 
                authoremail =    "'.$wpdb->_escape( $this->authoremail ).'",
                lng =    "'.$wpdb->_escape( $this->lng ).'",
                lat =    "'.$wpdb->_escape( $this->lat ).'",
                status =    "'.$wpdb->_escape( $this->status ).'",
                address =    "'.$wpdb->_escape( $this->address ).'",
                city =    "'.$wpdb->_escape( $this->city ).'",
                country =    "'.$wpdb->_escape( $this->country ).'",
                description =    "'.$wpdb->_escape( $this->description ).'"'
		.( ($updateDate)?',lastupdate = now()':'' ).'
                ;
';

        if( !$wpdb->query($q) )
        {
        	return false;
        }
        else
        {
        	return true;
        }
    }
}
?>