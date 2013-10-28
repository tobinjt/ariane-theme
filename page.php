<?php
  function MakeJewelleryGrid($page_contents) {
    # Turn the CSV from page contents into a data structure.
    $lines = str_getcsv($page_contents, "\n");
    # Discard !!JEWELLERY GRID!!
    array_shift($lines);
    # Discard the header.
    array_shift($lines);
    $ranges = array();
    foreach ($lines as $line) {
      $data = str_getcsv($line, '|');
      $ranges[$data[0]] = array(
        'alt'   => $data[1],
        'image' => $data[2],
        'link'  => $data[3],
      );
    }

    # Turn the data structure into <tr>s.
    $tds = array();
    foreach ($ranges as $range => $data) {
      $tds[] = <<<END_OF_TD
  <td>
    <a href="{$data['link']}">
      <img src="/wp-content/uploads/{$data['image']}" alt="{$data['alt']}" />
    </a>
    <div class="jewellery-grid-name">
      <a href="{$data['link']}">{$range}</a>
    </div>
  </td>
END_OF_TD;
    }

    # Turn the <tr>s into a table with two columns.
    if (count($tds) % 2 == 1) {
      $tds[] = '<td></td>';
    }
    $table = array();
    $table[] = <<<END_OF_TABLE_START
          <div id="jewellery-grid">
            <table>
END_OF_TABLE_START;
    for ($i = 0; $i < count($tds); $i++) {
      if ($i % 2 == 0) {
        $table[] = '<tr>';
      }
      $table[] = $tds[$i];
      if ($i % 2 == 1) {
        $table[] = '</tr>';
      }
    }
    $table[] = <<<END_OF_TABLE_END
            </table>
          </div>
END_OF_TABLE_END;
    return implode("\n", $table);
  }
?>

<?php get_header(); ?>
  <div id="main-content-page">
<?php
  if (is_jewellery_page()) {
    echo '    <div id="jewellery-page">', "\n";
  }
  while (have_posts()) {
    the_post();
?>
      <article class="single-page">
        <div class="entry">
<?php
    // Save the page content so we can check for slider presence in the footer.
    global $PAGE_CONTENT;
    $PAGE_CONTENT = get_the_content();
    if (preg_match('/^!!JEWELLERY GRID!!/', $PAGE_CONTENT)) {
      echo MakeJewelleryGrid($PAGE_CONTENT);
    } else {
      // get_the_content() returns unfiltered content; in particular, shortcodes are
      // not expanded.  Use the_content() instead of processing $PAGE_CONTENT and
      // needing to keep that processing up to date; I assume that getting the page
      // content is reasonably well cached.
      the_content();
    }
?>
        </div>
        <?php edit_post_link('Edit this entry.', '<p>', '</p>'); ?>
      </article>
<?php
  }
  if (is_jewellery_page()) {
    echo '    </div>', "\n";
  }
?>
  </div>

<?php get_footer(); ?>
