    <footer id="footer" class="source-org vcard copyright pink">
      <span>&copy;2011-<?php echo date("Y"); echo " ", strtolower(get_bloginfo('name')); ?></span>
      <span><a class="pink" href="tel:353868346825">phone: +353 86 834 6825</a></span>
      <span><a class="pink" href="mailto:ariane@arianetobin.ie">email: ariane@arianetobin.ie</a></span>
    </footer>

  </div>
<?php
  if (current_user_can('edit_pages')) {
    echo <<<VALIDATOR
    <div><a href="http://validator.w3.org/check?uri=referer">Validate</a></div>
VALIDATOR;
  }
  wp_footer();
?>

<script type="text/javascript">
<?php include (TEMPLATEPATH . '/footer.js'); ?>
</script>

  </body>
</html>
