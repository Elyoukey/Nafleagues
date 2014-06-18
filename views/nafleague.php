<section id="<?php echo $nafleague->id;?>" class="nafleague item">
	<article class="nafleague">
	<form method="post">
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
			<button class="sendlink">Send modification link</button>
			<p class="notice">
			If you are the league comissar and want to modify this league you can click on this button.<br/> You should receive the modification link in your email.
			</p>
		</div>
		<input type="hidden" name="id" value="<?php echo $nafleague->id;?>"/>
		<input type="hidden" name="action" value="nafleague_modificationlink" />
		<?php
		global $current_user;
		if( in_array('administrator' , $current_user->roles)):
			$editlink = $parsed_url;
			$chunkEditLink['id'] = $nafleague->id;
			$editlink['query'] = wp_parse_args($chunkEditLink);
			$target_url = unparse_url($editlink);

		?>
		<div class="adminbuttons">
			<a href="<?php echo $target_url; ?>">Edit</a>
			<a href="<?php echo home_url().'?plugin=nafleagues&action=delete	&id='.$nafleague->id;?>">Delete</a>
		</div>
		<?php endif;?>
	</form>
	</article>
</section>

