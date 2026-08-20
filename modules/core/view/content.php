<?php

if (!defined('ABSPATH')) {
   exit;
}

?>

<section class="container py-5">

   <?php
   the_title(
      '<h1 class="mb-4">',
      '</h1>'
   );
   ?>

   <?php the_content(); ?>

</section>
