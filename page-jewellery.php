<?php get_header(); ?>
  <div id="main-content-page">
    <div id="jewellery-page">
      <article class="single-page" id="post-<?php the_ID(); ?>">
        <div class="entry">
          <div id="jewellery-grid">
            <table>
<?php
  while (have_posts()) {
    the_post();
    $lines = str_getcsv(get_the_content(), "\n");
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

    if (count($tds) % 2 == 1) {
      $tds[] = '<td></td>';
    }
    $table = array();
    for ($i = 0; $i < count($tds); $i++) {
      if ($i % 2 == 0) {
        $table[] = '<tr>';
      }
      $table[] = $tds[$i];
      if ($i % 2 == 1) {
        $table[] = '</tr>';
      }
    }
    echo implode("\n", $table);
  }
?>
            </table>
          </div>
        </div>
      </article>
    </div>
  </div>

<?php get_footer(); ?>
