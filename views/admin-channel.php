<!-- This file is used to markup the admin-facing widget. -->

<div class="gravy-widget gravy-events-admin">
   <fieldset>
      <legend>
      </legend>
      <div class="option">
         <label for="<?php echo $this->get_field_id('title')?>">Title</label>
         <input type="text" class="widefat" id="<?php echo $this->get_field_id('title')?>"
            name="<?php echo $this->get_field_name('title')?>" value="<?php if (isset($title)) { echo esc_attr($title); } ?>">
         <label for="<?php echo $this->get_field_id('location')?>">City, State, Zip</label>
         <input type="text" class="widefat" id="<?php echo $this->get_field_id('location')?>"
            name="<?php echo $this->get_field_name('location')?>" value="<?php if (isset($location)) { echo esc_attr($location); } ?>">
         <label for="<?php echo $this->get_field_id('channel')?>">Channel</label>
         <select name="<?php echo $this->get_field_name('channel')?>" id="<?php echo $this->get_field_id('channel')?>" class="widefat">
            <optgroup label="Featured Channels">
               <?php echo $featuredChannels ?>
            </optgroup>
               <?php echo $genericChannels ?>
         </select>
         <label for="<?php echo $this->get_field_id('layout')?>">Layout</label>
         <select name="<?php echo $this->get_field_name('layout')?>" id="<?php echo $this->get_field_id('layout')?>" class="widefat">
            <option value="card" <?php if ( 'card' == $instance['layout'] ) echo 'selected="selected"'; ?>>Card</option>
            <option value="list" <?php if ( 'list' == $instance['layout'] ) echo 'selected="selected"'; ?>>List</option>
         </select>
      </div>
   </fieldset>
</div>