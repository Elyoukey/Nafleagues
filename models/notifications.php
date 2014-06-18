<?php
/* Generic model to send notifications by mail*/
class nafleague_mailing
{
    
    
    static function send( $template , $sDatas, $to )
    {
        $m = file_get_contents( dirname(__FILE__).'/../mails/'.$template.'.htm');
        foreach(  $sDatas as $k => $v )
        	{
        		$datas['{'.$k.'}']=$v;
        	}
        $m = str_replace( array_keys($datas), $datas, $m);
        $tm = explode('<!-- [] -->',$m);
        $subject = $tm[0];
        $message = $tm[1];

        $headers = 'Content-type: text/html'."\r\n";
        $result = wp_mail($to, $subject, $message, $headers);
        return $result;
    }
}

?>
