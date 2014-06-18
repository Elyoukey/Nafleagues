<section id="<?php echo $nafleague->id;?>" class="nafleague item">
	<article class="nafleague">
		<h3 class="name"><?php echo $nafleague->name;?></h3>
		<div class="content">
			<div class="lat" ><?php echo $nafleague->lat;?></div>
			<div class="lng" ><?php echo $nafleague->lng;?></div>
			<div class="url"><?php if($nafleague->url):?><a href="<?php echo $nafleague->url;?>">Website</a><?php endif;?></div>
			<?php if($nafleague->imageurl):?>
			<img src="<?php echo $nafleague->imageurl;?>" class="logo" alt="<?php echo $nafleague->name;?>" />
			<?php endif;?>
			<div class="description"><?php echo nl2br($nafleague->description)  ;?></div>
			<div class="addres"><?php echo $nafleague->address;?></div>
			<div class="city"><?php echo $nafleague->city;?></div>
			<div class="country"><?php echo $nafleague->country;?></div>
			<div class="email">Contact: <?php echo hide_email($nafleague->authoremail);?></div>
			<div class="lastupdate" ><?php echo date('d-M-Y h:i:s', strtotime($nafleague->lastupdate));?></div>
		</div>
	</article>
</section>

