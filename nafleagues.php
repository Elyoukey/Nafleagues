<?php
/**
 * @package Elyoukey
 * @version 1.0
 */
/*
Plugin Name: nafleagues
Plugin URI: 
Description: This plugin handle a list of NAF leagues around the world.
This plugin handles a comprehensive, complete, autoupdating map of all the Bloodbowl Leagues on the world. 
Whenever it could technically be installed on any wordpress website, the development has been done for the www.thenaf.net website. 


Shortcodes are as follow : 

[nafleagues_display]
displays the map and the full league list

[nafleagues_new]
displays the league form to create new league



Process are as follow:

1------- Creating a league
-Any visitor can create a league by filling the form. 
-Once the informations are stored, the user receive an email with an activation link. At this step the league does not appear in the map
-When clicking the activation link, the league is "active" and is displayed on the map

2-------- Updating a League
-the league commissar (ie the person who created the ligue) can click on the "send modification link" button
-a mail is sent to the league email with a link leading to the edition form
-the commissar updates datas in the form as wishes

the drawback of this is that any visitor can click the button and potentially "spam" a commissar email. i don't see any smart and easy way to avoid this without including an account handling process.


3------- Autoupdating
-a specific part of the plugin check for older leagues older=more than 6 months old (this parameter can be changed in the first lines of the plugin: NAFLEAGUES_LIMIT_PENDING )

-all leagues that are too old are set to "pending" they are still displayed on the map but an email is sent to the commissar with a activation link
if the commissar clicks on the link, the league is updated and he will be mailed again in 6 month
if the commissar does not click on the link within 2 month, the league is set to "outdated" and is not displayed on the map anymore



That's about it. Note that there is no way to delete a league since the system will automatically do it by himself. (and also because i don't want to spend more time on developping this almost useless feature)


Version: 1.0
Author URI: http://elyoukey.com
 * 
 * 
 * This file behave as the controller
 * see /models and /views to have views and models 
*/
require_once('models/nafleague.php');
require_once('models/nafleagues.php');
require_once('models/notifications.php');
require_once('models/logs.php');
require_once('models/messages.php');

wp_register_style( 'nafleagues-css', plugins_url('/css/nafleagues.css', __FILE__) );
wp_enqueue_style( 'nafleagues-css' );

DEFINE( NAFLEAGUES_LIMIT_PENDING, '-4 MONTH' );//time after what the league is pending. an email should be sent to warn the user
//DEFINE( NAFLEAGUES_LIMIT_PENDING, '-6 MONTH' );
DEFINE( NAFLEAGUES_LIMIT_OUTDATED, '-6 MONTH' );//time after which the league is desactivated. the league does not appear on the map anymore, but the previous activation link still works.
    
global $nafleagues_version;
$nafleagues_version = '1.0';


/* --------------------------- deslash $_POST ----------------------------------*/
$POST      = array_map( 'stripslashes_deep', $_POST);

/*
 * --------------------------- INSTALLATION ------------------------------------
 */
register_activation_hook( __FILE__, 'nafleagues_install' );

function nafleagues_install() {
   global $wpdb;
   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
   //main table: leagues
   $table_name = $wpdb->prefix . "nafleagues"; 
   
    $sql1 = "CREATE TABLE $table_name (
id mediumint(9) NOT NULL AUTO_INCREMENT,
status SET( 'pending', 'active', 'outdated', 'deleted' ) NOT NULL DEFAULT 'pending',
activationcode varchar(255) DEFAULT '' NOT NULL,
name tinytext NOT NULL,
description text DEFAULT '' NOT NULL,
url varchar(255) DEFAULT '' NOT NULL,
imageurl varchar(255) DEFAULT '' NOT NULL,
authoremail varchar(255) NOT NULL,
address text DEFAULT '' NOT NULL,
city varchar(255) DEFAULT '' NOT NULL,
country varchar(255) DEFAULT '' NOT NULL,
lng text DEFAULT '' NOT NULL,
lat text DEFAULT '' NOT NULL,
lastupdate timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
UNIQUE KEY  id (id)
    );";

    $r = dbDelta( $sql1 ) ;
    
    $wpdb->query($r);
    
    //log table
   $table_name = $wpdb->prefix . "nafleagues_logs"; 
   
    $sql2 = "CREATE TABLE $table_name (
id mediumint(9) NOT NULL AUTO_INCREMENT,
league_id int(11) DEFAULT 0 NOT NULL,
type tinytext NOT NULL,
text text DEFAULT '' NOT NULL,
date timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
UNIQUE KEY  id (id)
);";

    $r = dbDelta( $sql2  ) ;

    add_option( "nafleagues_version", $nafleagues_version );
}

/*
 * ------------------------- UPDATES ------------------------------------------
 */

function nafleagues_update_db_check() {
    global $nafleagues_version;
    if (get_site_option( 'nafleagues_version' ) != $nafleagues_version) {
        nafleagues_install();
    }
}
add_action( 'plugins_loaded', 'nafleagues_update_db_check' );

/*
 *  ------------------------ JS include -------------------------------------------
 */

function nafleagues_includejs() {
	wp_enqueue_script(
		'nafleague_js',
		plugins_url( '/js/nafleagues.js' , __FILE__ ),
		array( 'jquery' )
	);
}
add_action( 'wp_enqueue_scripts', 'nafleagues_includejs' );


/*
 * ------------------------- CRON (always run)----------------------------------------------
 */

add_action('wp_loaded', 'nafleague_check');
function nafleague_check()
{
	//retrieve every league to be set on pending
	$nafleaguesToDisable = nafleagues::getToPending();
	foreach($nafleaguesToDisable as $league)
	{
		//set on pending
		$nafleague = new nafleague($league->id);
		
		//generate modification link
		$nafleague->renewActivationcode();
		$nafleague->status = 'pending';
		$nafleague->save( false );
		
		//send email to commissar
		$parsed_url = parse_url(current_page_url());
		$parsed_url = parse_url(home_url());

		$r = wp_parse_args($parsed_url['query']);
		$r['action'] = 'validate';
		$r['uniqid'] = $nafleague->activationcode;
		$r['plugin'] = 'nafleagues';
		$parsed_url['query'] = wp_parse_args($r);
		$target_url = unparse_url($parsed_url);
	
		$datas = array(
		    'mail'=> $nafleague->authoremail,
		    'link'=> $target_url,
		    'leaguename'=>$nafleague->name
		    );
		$to = $nafleague->authoremail;
	
		nafleague_mailing::send('pendingwarning', $datas, $to);
	}
	
	//retrieve every league to be set on outdated
	$nafleaguesToDisable = nafleagues::getToOutdate();
	foreach($nafleaguesToDisable as $league)
	{
		//set on pending
		$nafleague = new nafleague($league->id);
		
		//generate modification link
		$nafleague->status = 'outdated';
		$nafleague->save();
		
		//send email to commissar
		$datas = array(
		    'mail'=> $nafleague->authoremail,
		    'leaguename'=>$nafleague->name
		    );
		$to = $nafleague->authoremail;
	
		nafleague_mailing::send('outdatedwarning', $datas, $to);
	}
	
	return true;
}

/*
 *  ------------------------ ACTIONS -------------------------------------------
 */

//save new league and send email
add_action('wp_loaded', 'nafleague_save');
function nafleague_save()//save new league or exiting
{
	$POST = array_map( 'stripslashes_deep', $_POST);
	//save only when form is submit
	if( isset($POST['nafleague_hp']) && $POST['nafleague_hp']!='' ){return;}

	//save only when form is submit
	if( !isset($POST['nafleague_save']) ){return;}

	global $wp;

	$nafleague = new nafleague();
	$nafleague->updateFromPost();
	if( !$nafleague->name )die('<span class="error">Error-You must provide a name.</span>');
	if( !$nafleague->authoremail )die('<span class="error">Error - You must provide an email for validation.</span>');
		
	//modify league
	if( !empty( $POST['nafleague_id'] ) )
	{
		$nafleague->status='active';
		if($nafleague->save())
		{
			echo '<span class="success">Your changes has been saved in our database. Thanks.</span>';
		}
		else
		{
			echo '<span class="error">Error in the database (1)</span>';
		}
		//wp_redirect( current_page_url() );//data modified go back home
	}
	else //new league
	{
		$nafleague->renewActivationcode(); 
		if(!$nafleague->save())
		{
			die( '<span class="error">Error in the database(2)</span>' );
		}
		//build activation link
		$parsed_url = parse_url(current_page_url());
		$parsed_url['query'] = wp_parse_args($parsed_url['query'],array(
			'plugin'=>'nafleagues', 
			'uniqid'=>$nafleague->activationcode,
			'action'=>'validate',
			'nl_msg'=>''));
		$target_url = unparse_url($parsed_url);
	
		$datas = array(
		    'mail'=> $nafleague->authoremail,
		    'validatelink'=> $target_url
		    );
		$to = $nafleague->authoremail;
		nafleague_mailing::send('newleague', $datas, $to);

		echo '<span class="success">An eMail has been sent. You must click on the link in it for your league to be displayed on the map. Thanks.</span>';
	}
	exit();
}

//validate pending league thrue emailed link
add_action('wp_loaded', 'nafleague_validate_action');
function nafleague_validate_action(  )
{
	//save only when form is submit
	if( !isset($_GET['plugin']) ){return ;}
	if( !isset($_GET['uniqid']) ){return ;}
	if( !isset($_GET['action']) || $_GET['action']!='validate' ){return ;}
	$uniqid = sanitize_text_field( $_GET['uniqid'] );

	$nafleague = new nafleague();
	$r = $nafleague->loadfromUniqid($uniqid);
	if(!$r)
	{
		echo('Activation id '.$uniqid.' does not exists');
		return true;
	}
	$nafleague->status='active';
	$nafleague->activationcode = '';
	$nafleague->save();
	nafleagues_addmessage('success', "Thanks, your league <b>{$nafleague->name}</b> will be active for 6 months." );
	return true;
}

//send email link for modification
add_action('wp_loaded', 'nafleague_send_modificationlink');
function nafleague_send_modificationlink(  )
{
	$POST = array_map( 'stripslashes_deep', $_POST);
	
	if( !isset($POST['action']) || $POST['action'] != 'nafleague_modificationlink' ){return;}
	if( !isset($POST['id']) ){return;}
	
	global $wp, $msg;
	$nafleague = new nafleague();
	$nafleague->loadFromId((int)$POST['id']);
	$nafleague->renewActivationcode();
	$nafleague->save( false );
	
	$parsed_url = parse_url(current_page_url());

	$r = wp_parse_args($parsed_url['query']);
	$r['action'] = 'edit';
	$r['uniqid'] = $nafleague->activationcode;
	$r['plugin'] = 'nafleagues';
	$parsed_url['query'] = wp_parse_args($r);
	$target_url = unparse_url($parsed_url);

	$datas = array(
	    'mail'=> $nafleague->authoremail,
	    'link'=> $target_url,
	    'leaguename'=>$nafleague->name
	    );
	$to = $nafleague->authoremail;

	nafleague_mailing::send('modificationlink', $datas, $to);
	//wp_redirect( current_page_url('mailsent') );
}

//delete a league confirmtion screen
add_filter('the_content', 'nafleague_deleteconfirm');
function nafleague_deleteconfirm( $pagecontent )
{
	global $current_user;
	$isadmin=in_array('administrator' , $current_user->roles);
	if( !isset($_GET['plugin']) || $_GET['plugin']!='nafleagues' ){return $pagecontent;}
	if( !$isadmin || !isset($_GET['action']) || $_GET['action']!='delete' )
	{
		return $pagecontent;
	}
	
	$nafleague = new nafleague();
	$nafleague->loadfromId($_GET['id']);

	$content = '';
	$content .= '
	<form method="post" id="nafleague_form" action="'.current_page_url().'"> 
	Are you sure you want to delete this league ?
	<input type="submit" value="yes" /> 
	<input type="hidden" name="plugin" value="nafleagues" />
	<input type="hidden" name="action" value="delete" />
	<input type="hidden" name="confirm" value="1" />
	<input type="hidden" name="id" value="'.$nafleague->id.'" />
	<input type="button" value="no" onclick="window.history.go(-1);" />
	';
	ob_start();
		//display the result
		include('views/nafleague_noform.php');
		$content .= ob_get_contents();
	ob_end_clean();
	$content .= '</form>';
	return $content;
}

//delete a league + redirection
add_action('wp_loaded', 'nafleague_delete');
function nafleague_delete(  )
{
	global $current_user;
	$isadmin=in_array('administrator' , $current_user->roles);
	
	if( !isset($_POST['plugin']) || $_POST['plugin']!='nafleagues' ){return;}
	if( !$isadmin || !isset($_POST['action']) || $_POST['action']!='delete' ){return;}
	if( !isset($_POST['confirm']) || $_POST['confirm']!='1' )	{return;}
	
	$nafleague = new nafleague();
	$nafleague->loadfromId($_POST['id']);
	$nafleague->status = 'deleted';
	$nafleague->save();
	echo '<span class="success">League <b>'.$nafleague->name.'</b> successfully deleted</div>';
	die();
}
	
//-------------------------- VIEW FORMS --------------------------------
add_filter('the_content', 'nafleague_edit_form');
function nafleague_edit_form( $pagecontent )//display edit form
{
	global $current_user;
	
	$isadmin=in_array('administrator' , $current_user->roles);
	if( !isset($_GET['plugin']) || $_GET['plugin']!='nafleagues' ){return $pagecontent;}
	if( 
		!$isadmin
		||
		!isset($_GET['action']) 
		|| 
		( 
			$_GET['action']!='edit' 
			&& 
			$_GET['action']!='adminedit'
		)
	)
	{
		return $pagecontent;
	}

	if( $_GET['action'] == 'edit' && isset( $_GET['uniqid'] ) )
	{
		$uniqid = sanitize_text_field( $_GET['uniqid'] );
		$nafleague = new nafleague();
		$nafleague->loadfromUniqid($uniqid);
	}
	if( $_GET['action'] == 'adminedit' && isset( $_GET['id'] ) )
	{
		$id = sanitize_text_field( $_GET['id'] );
		$nafleague = new nafleague();
		$nafleague->loadfromId($id);
	}
	
	if(!$nafleague->id)
	{
		return 'This league does not exists anymore or the link is out of date. Please click the "send modificatio" button again to receive a new mail. ';
	}
	
	$title = 'Edit your league';
	ob_start();
		//display the result
		include('views/nafleague_form.php');
		$content = ob_get_contents();
	ob_end_clean();

	return $content;
}

/*-------------------- DISPLAY MESSAGES ------------------------------------------*/
add_filter('the_content', 'nafleagues_showmessages');
function nafleagues_showmessages( $pagecontent )
{
	$messages = nafleagues_getGetMessages();
	
	echo '<div id="nafleague_messages">';
	if( !empty($messages ))
	{
		foreach($messages as $m )
		{
			echo  '<span class='.$m['type'].'>'.$m['message'].'</span>';
		}
	}
	echo '</div>';
	return $pagecontent;
}

//-------------------- SHORTCODES ------------------------------------/**/

/* displayl list of all leagues around the world*/
add_shortcode( 'nafleagues_display', 'nafleagues_display' );
function nafleagues_display($type='map')
{
	 
    //retrieve all datas
    $nafleagues = nafleagues::get();
   
    //display the result
    include('views/nafleagues.php');
    
}

/* display a form to enter a new league */
add_shortcode( 'nafleagues_new', 'nafleague_new' );
function nafleague_new()
{
    $nafleague = null;
    //display the result
    $title = 'Add your league';
    include('views/nafleague_form.php');
}



//--------------------------------------------utility functions --------------
function current_page_url($msg=false) {
	$pageURL = 'http';
	if( isset($_SERVER["HTTPS"]) ) {
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	
	//add $get message
	if($msg)
	{
		if(strpos($pageURL,'?')){$pageURL.='&nl_msg=';}else{$pageURL.='?nl_msg=';}
		$pageURL.=$msg;
	}
	
	return $pageURL;
}

function unparse_url($parsed_url) { 
  $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : ''; 
  $host     = isset($parsed_url['host']) ? $parsed_url['host'] : ''; 
  $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : ''; 
  $user     = isset($parsed_url['user']) ? $parsed_url['user'] : ''; 
  $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : ''; 
  $pass     = ($user || $pass) ? "$pass@" : ''; 
  $path     = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 
  if(is_array($parsed_url['query']))
  { 
	$params = array();
	foreach($parsed_url['query'] as $a=>$b)
	{
		$params[]=$a.'='.$b;
	}
	$parsed_url['query'] = implode( '&', $params );
  }
  $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : ''; 

  $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : ''; 
  return "$scheme$user$pass$host$port$path$query$fragment"; 
} 


function hide_email($email)
{ $character_set = '+-.0123456789@ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';

  $key = str_shuffle($character_set); $cipher_text = ''; $id = 'e'.rand(1,999999999);

  for ($i=0;$i<strlen($email);$i+=1) $cipher_text.= $key[strpos($character_set,$email[$i])];

  $script = 'var a="'.$key.'";var b=a.split("").sort().join("");var c="'.$cipher_text.'";var d="";';

  $script.= 'for(var e=0;e<c.length;e++)d+=b.charAt(a.indexOf(c.charAt(e)));';

  $script.= 'document.getElementById("'.$id.'").innerHTML="<a href=\\"mailto:"+d+"\\">"+d+"</a>"';

  $script = "eval(\"".str_replace(array("\\",'"'),array("\\\\",'\"'), $script)."\")"; 

  $script = '<script type="text/javascript">/*<![CDATA[*/'.$script.'/*]]>*/</script>';

  return '<span id="'.$id.'">[javascript protected email address]</span>'.$script;

}

/*generate a bunch of kleagues for testing purpose*/
function generateDatas()
{
	$base ='abcdefghijklmnopqrstuvwxyz';
	global $wpdb;
	for($i=0;$i<150;$i++)
	{
		$name='';	
	
		for($j=0;$j<10;$j++)
		{
			$name.=$base[rand(1,26)];
		}
$name.=' Bowl';

$q = "
INSERT INTO `wp_nafleagues` (`id`, `status`, `activationcode`, `name`, `description`, `url`, `authoremail`, `address`, `city`, `country`, `lng`, `lat`, `lastupdate`, `imageurl`) VALUES (NULL, 'active', '', '$name', 'apien fringilla molestie. Integer sit amet adipiscing justo. Nunc eu neque mauris. Pellentesque adipiscing convallis elit. Nullam suscipit, augue eget mollis fringilla, neque nisi vestibulum sem, at tempor nibh augue vel lacus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vivamus id est quis nibh luctus accumsan ut sit amet purus. Curabitur massa enim, posuere a pharetra eu, iaculis vel mauris. ', '', 'elyoukey@gmail.com', '', 'Toulouse', '', '-43.2007101', '-22.9133954', now(), '');
";
		if( !$wpdb->query($q) )
        	{
		echo($q);
        	return false;
	        }
        	else
        	{
        	return true;
        	}
	}
}