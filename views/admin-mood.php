<!-- This file is used to markup the admin-facing widget. -->

<div class="gravy-widget gravy-events-admin">
   <fieldset>
      <legend>

      </legend>
      <div class="option">
         <label for="<?php echo $this->get_field_id('title')?>">Title:</label>
         <input type="text" class="widefat" id="<?php echo $this->get_field_id('title')?>"
            name="<?php echo $this->get_field_name('title')?>" value="<?php if (isset($title)) { echo esc_attr($title); } ?>">
         <label for="<?php echo $this->get_field_id('location')?>">City, State OR Zip:</label>
         <input type="text" class="widefat" id="<?php echo $this->get_field_id('location')?>"
            name="<?php echo $this->get_field_name('location')?>" value="<?php if (isset($location)) { echo esc_attr($location); } ?>">
         <label for="<?php echo $this->get_field_id('mood')?>">Mood:</label>
         <select name="<?php echo $this->get_field_name('mood')?>" id="<?php echo $this->get_field_id('mood')?>" class="widefat">
            <option value="0" <?php if ( 0 == $instance['mood'] ) echo 'selected="selected"'; ?>>Whatever</option>
            <option value="1" <?php if ( 1 == $instance['mood'] ) echo 'selected="selected"'; ?>>Lively</option>
            <option value="2" <?php if ( 2 == $instance['mood'] ) echo 'selected="selected"'; ?>>Classy</option>
            <option value="3" <?php if ( 3 == $instance['mood'] ) echo 'selected="selected"'; ?>>Brainy</option>
            <option value="4" <?php if ( 4 == $instance['mood'] ) echo 'selected="selected"'; ?>>Playtime</option>
         </select>
         <label for="<?php echo $this->get_field_id('timerange')?>">Time Range:</label>
         <select name="<?php echo $this->get_field_name('timerange')?>" id="<?php echo $this->get_field_id('timerange')?>" class="widefat">
            <option value="TODAY" <?php if ( 'TODAY' == $instance['timerange'] ) echo 'selected="selected"'; ?>>Today</option>
            <option value="THISWEEK" <?php if ( 'THISWEEK' == $instance['timerange'] ) echo 'selected="selected"'; ?>>This Week</option>
            <option value="WEEKEND" <?php if ( 'WEEKEND' == $instance['timerange'] ) echo 'selected="selected"'; ?>>This Weekend</option>
         </select>
         <label for="<?php echo $this->get_field_id('layout')?>">Layout:</label>
         <select name="<?php echo $this->get_field_name('layout')?>" id="<?php echo $this->get_field_id('layout')?>" class="widefat">
            <option value="card" <?php if ( 'card' == $instance['layout'] ) echo 'selected="selected"'; ?>>Card</option>
            <option value="list" <?php if ( 'list' == $instance['layout'] ) echo 'selected="selected"'; ?>>List</option>
         </select>
      </div>
   </fieldset>
</div>