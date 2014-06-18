<?php
/* Form to enter a new league or edit existing one*/
?>
<a name="addyourleague"></a>
<div style="clear:both;"></div>

<form id="nafleague_form" class="nafleague_form" method="POST">
	<h2><?php echo $title;?></h2>
    <div class="field"><label for="nafleague_name">League name</label><input id="nafleague_name" type="text" name="nafleague_name" value="<?php echo $nafleague->name?>"/></div>
    <div class="field"><label for="nafleague_url">URL</label><input id="nafleague_url" type="text" name="nafleague_url" value="<?php echo $nafleague->url; ?>"/></div>
    <div class="field"><label for="nafleague_imageurl">Logo</label><input id="nafleague_imageurl" type="text" name="nafleague_imageurl" value="<?php echo addslashes($nafleague->imageurl)?>"/></div>
    <div class="field"><label for="nafleague_email">email*</label><input id="nafleague_email" type="text" name="nafleague_email" value="<?php echo addslashes($nafleague->authoremail)?>"/></div>
    <div class="field">
        <label for="nafleague_description">Description</label>
        <br/>
        <textarea id="nafleague_description" name="nafleague_description" ><?php echo ($nafleague->description)?></textarea>
    </div>
    <div class="field">
        <label for="nafleague_address">Address</label>
        <br/>
        <textarea id="nafleague_address" name="nafleague_address" value="<?php echo $nafleague->address; ?>"></textarea>
    </div>
    <div class="field"><label for="nafleague_city">City</label><input id="nafleague_city" type="text" name="nafleague_city" value="<?php echo $nafleague->city; ?>"/></div>
    <div class="field"><label for="nafleague_country">Country</label><input id="nafleague_country" type="text" name="nafleague_country" value="<?php echo $nafleague->country;?>"/></div>
    
    <input type="hidden" value="1" name="nafleague_save" />
    <input type="text" name="nafleague_hp" style="display:none"/>
    <input type="hidden" name="nafleague_id" value="<?php echo $nafleague->id; ?>" />
    
    <input type="submit" value="send" id="nafleague_send_button"/>
    
</form>