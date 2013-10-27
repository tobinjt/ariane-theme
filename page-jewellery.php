<?php get_header(); ?>
  <div id="main-content-page">
    <div id="jewellery-page">
      <article class="single-page" id="post-<?php the_ID(); ?>">
        <div class="entry">
          <div id="jewellery-grid">
            <table>
<?php
  # TODO(johntobin): the image URIs will need to change; ideally they should be
  # the same on both sites.
  $ranges = array(
    'confluence' => array(
      'alt'   => 'consluence pendant',
      'image' => '2012/12/IMG_5878.jpg',
      'link'  => '/jewellery/confluence/',
    ),
    'halo' => array(
      'alt'   => 'halo pendants',
      'image' => '2012/12/IMG_5939.jpg',
      'link'  => '/jewellery/halo/',
    ),
    'sentinel' => array(
      'alt'   => 'sentinel pendant',
      'image' => '2012/12/IMG_5911.jpg',
      'link'  => '/jewellery/sentinel/',
    ),
  );

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
?>
            </table>
          </div>
        </div>
      </article>
    </div>
  </div>

<?php get_footer(); ?>
