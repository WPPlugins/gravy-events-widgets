<!-- This file is used to markup the public-facing widget. -->

<div class="gravy-widget gravy-events">
   <?php if ($title) { echo $before_title . $title . $after_title; } ?>
   <div class="gravy-events-container">
      <ul>
      <?php foreach ($events as $event => $detail) { ?>
         <li class="gravy-event-<?php echo $layout ?>">
            <a href="<?php echo $detail->longUrl ?>" class="gravy-event-link" target="_blank">
               <div class="gravy-event-image">
                  <img src="<?php echo $detail->imageUrl ?>" alt="">
                  <?php if ($layout == 'card') { ?>
                  <div class="gravy-discount <?php echo $detail->discount; ?>"></div>
                  <div class="gravy-distance"><?php echo $detail->distance; ?> miles</div>
                  <?php } ?>
               </div>
               <div class="gravy-content">
                  <h4><?php echo $detail->name; ?></h4>
                  <p><?php echo (($detail->venueName)?$detail->venueName . "<br>":''); ?>
                     <em><?php echo $detail->startDate ?></em>
                  </p>
               </div>
            </a>
         </li>
      <?php } ?>
      </ul>
   </div>
</div>