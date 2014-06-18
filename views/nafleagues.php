<?php
/*Display the list of nafleagues*/
?>

<div id="nafmap" style="width:100%; height: 500px;">
	<div id="map_canvas" style="width:100%; height: 500px;">Here map of the NAFLeagues soon</div>
</div>
<a href="#addyourleague">Add your league</a>
<hr/>
<p>
<label for="nafleague_lookupfield" class="search">Search (league name or city):</label> <input type="text" id="nafleague_lookupfield" onkeyup="nafleagues_lookup()"/>
</p>
<hr/>
<script type="text/javascript"
      src="http://maps.googleapis.com/maps/api/js?key=AIzaSyBiUCRGsQIIGYARSFJBXv7fAoot4zB03KY&sensor=false">
</script>
<script type="text/javascript">
var leaguelist = new Array();
var marker = new Array();
var infowindow = new Array();
var nafmap;
function nafleagues_lookup()
{
	//$('#nafleague_lookupdiv').style.display='block';
	lookup = document.getElementById('nafleague_lookupfield').value.toLowerCase();
	
	for(i=0;i<leaguelist.length;i++)
	 {
		if(lookup =='' )
		{
			leaguelist[i].show();
			marker[i].setVisible(true);
		}
		else
		{
	 		if( ( leaguelist[i].find('.name').html()+leaguelist[i].find('.city').html()).toLowerCase().lastIndexOf(lookup) == -1 )
			{
				leaguelist[i].hide();
				marker[i].setVisible(false);
			}		
			else 
			{	
				leaguelist[i].show();
				marker[i].setVisible(true);
			}
		}
	 }
	 
}
function nafleague_showLeague(t)
{
	
	infowindow[t].open(nafmap,marker[t]);
	nafmap.setZoom(5);
	nafmap.setCenter(marker[t].getPosition());
}
      
      function initialize() {
        var myOptions = {
          //center: new google.maps.LatLng(45.34692761055676,-2.9),
          center: new google.maps.LatLng(0,0),
          
          zoom: 1,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var nafMarkerImage = new google.maps.MarkerImage('<?php echo home_url( );?>/wp-content/plugins/nafleagues/images/logo_naf.png');
        var nafMarkerImageGrey = new google.maps.MarkerImage('<?php echo home_url( );?>/wp-content/plugins/nafleagues/imageslogo_naf_passed.png');
        nafmap = new google.maps.Map(document.getElementById("map_canvas"),
            myOptions);
        
        var counter = 0;
        jQuery('.nafleague.item').each(function(){
        		
		leaguelist[counter]=jQuery(this);
		infowindow[counter] = new google.maps.InfoWindow({
		    content: jQuery(this).html()
		});
		marker[counter] = new google.maps.Marker({
		      position: new google.maps.LatLng(jQuery(this).find('.lat').html(), jQuery(this).find('.lng').html()),
		      map: nafmap,
		      title: jQuery(this).find('.name').html(),
		      icon: nafMarkerImage,
		      counter: counter
		  });
		google.maps.event.addListener(marker[counter], 'click', function() {
		  infowindow[this.counter].open(nafmap,this);
		});
		
		counter ++;
	});
	
	
      }
      
      jQuery(document).ready(initialize);
      
      
</script>

<div id="nafleagues_list">
<?php 
$parsed_url = parse_url(current_page_url());

$chunkEditLink = wp_parse_args($parsed_url['query']);
$chunkEditLink['action'] = 'adminedit';
$chunkEditLink['plugin'] = 'nafleagues';

foreach( $nafleagues as $nafleague ):
	include('nafleague.php');
endforeach; 
?>
</div>