<?php

//handle singleton object
function nafleagues_addmessage( $type, $message)
{
	$m = nafleagues_messages::getInstance();
	$m->addMessage($type,$message);
}

function nafleagues_getmessages()
{
	$m = nafleagues_messages::getInstance();
	return $m->getMessages();
}
function nafleagues_clearmessages()
{
	$m = nafleagues_messages::getInstance();
	return $m->clearMessages();
}

function nafleagues_getGetMessages()//get all $_GET messages in nl_msg table
{
	$m = nafleagues_messages::getInstance();
	switch($_GET['nl_msg'])
	{
		case 'mailsent':
			$m->addMessage('success','A mail has been sent to your email with the activation link');
		default:
			break;
	}
	return $m->getMessages();
}
class nafleagues_messages {
	/**
	* @var Singleton
	* @access private
	* @static
	*/
	private static $_instance = null;
	public $messages;
	/**
	* Constructeur de la classe
	*
	* @param void
	* @return void
	*/
		private function __construct() {
	}
	/**
	* Méthode qui crée l'unique instance de la classe
	* si elle n'existe pas encore puis la retourne.
	*
	* @param void
	* @return Singleton
	*/
	public static function getInstance() {
		if(is_null(self::$_instance)) {
			self::$_instance = new nafleagues_messages();
		}
		return self::$_instance;
	}
	
	public function addMessage($type,$msg)
	{
		$this->messages[] = array('type'=>$type,'message'=>$msg); 
		return true;
	}
	public function getMessages($type='all')
	{
		return $this->messages;  
	}
	public function clearMessages($type,$msg)
	{
		$this->message=array();
		return true;  
	}
}
?>
