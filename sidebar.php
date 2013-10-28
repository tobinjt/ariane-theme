<div id="sidebar">
<?php
  if (!(function_exists('dynamic_sidebar') && dynamic_sidebar('Sidebar Widgets'))) {
    echo "ARGH!  PLEASE CONFIGURE THE SIDEBAR IN 'Appearance &lt; Widgets'";
  }
?>
</div>
