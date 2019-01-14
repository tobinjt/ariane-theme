    <footer id="footer" class="source-org vcard copyright highlight">
      <ul>
        <li>&copy;2011-<?php echo date('Y'); echo ' ', strtolower(get_bloginfo('name')); ?></li>
        <li><a class="highlight" href="tel:353868346825">phone: +353 86 834 6825</a></li>
        <li><a class="highlight" href="mailto:ariane@arianetobin.ie">email: ariane@arianetobin.ie</a></li>
      </ul>
    </footer>

  </div>
<?php
  wp_footer();
?>

<?php if (is_single()) {
  include(TEMPLATEPATH . '/footer.js');
} ?>
  </body>
</html>
