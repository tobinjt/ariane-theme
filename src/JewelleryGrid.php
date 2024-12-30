<?php

declare(strict_types=1);

// Support for Jewellery grids showing multiple products.

// Extras needed by PHPLint.
/*. require_module 'array'; .*/
/*. require_module 'core'; .*/
/*. require_module 'pcre'; .*/
require_once __DIR__ . '/DataStructures.php';
require_once __DIR__ . '/StoreClosingTimes.php';
require_once __DIR__ . '/Urls.php';
/*. array[string]string .*/ $SLIDER_IMAGES = [];

// Return the original string if an error occurs.  Unlikely to happen in
// practice, but PHPStan warns about it.  I cannot find any way to force
// preg_replace to return null, so that branch is untested :(
function safe_preg_replace(
    string $pattern,
    string $replacement,
    string $subject
): string {
    $result = preg_replace($pattern, $replacement, $subject);
    if (is_null($result)) {
        return $subject;
    }
    return $result;
}

/* ParseJewelleryGridContents: turn the CSV from page contents into a data
 * structure.
 * Args:
 *  $page_contents: string, the contents of the page.  First line (CSV header)
 *    will be removed.  Blank lines will be skipped.  <br /> will be stripped
 *    from the end of each line.
 * Returns:
 *  array, data structure to process.
 */
/**
 * @return array<JewelleryGridEntry>
 */
function ParseJewelleryGridContents(string $page_contents): array
{
    $lines = str_getcsv($page_contents, "\n");
    /*. array[int]JewelleryGridEntry .*/ $ranges = [];
    foreach ($lines as $line) {
        $line = trim(strval($line));
        // Wordpress puts <br /> and </p> and other shite at the end of some
        // lines, so remove all tags from the start and end of each line.
        $line = safe_preg_replace('/^<[^<]+>/', '', $line);
        $line = safe_preg_replace('/<[^<]+>$/', '', $line);
        $line = trim($line);
        if (strpos($line, '#') === 0) {
            continue;
        }
        // Awful hack to work around wordpress turning 276x300 into 276!300,
        // where ! is actually some weird unicode x - this breaks image urls.
        // ARGH.
        $line = safe_preg_replace('/&#215;/', 'x', $line);
        $csv_data = str_getcsv($line, '|');
        // Skip blank lines.  The CSV parser will return an array with a single
        // element when given a blank line.
        if (count($csv_data) === 1) {
            continue;
        }
        // Line format:
        // * Range name|Image description|Image ID(s)|Link to page|Product ID
        // * The top-level jewellery page links to ranges rather than products,
        //   so we can't include purchasing.  We use -1 to indicate that there
        //   isn't a product to offer, and that's checked for later.
        if (count($csv_data) < 5) {
            $csv_data[] = '-1';
        }
        $ranges[] = new JewelleryGridEntry(
            strval($csv_data[0]),
            strval($csv_data[1]),
            strval($csv_data[2]),
            strval($csv_data[3]),
            intval($csv_data[4])
        );
    }
    return $ranges;
}

/* JewelleryGridShortcode: create a table from CSV content.
 * This *must* be used in the enclosing form.
 * Args (names are ugly but Wordpress-standard):
 *  $atts: associative array of attributes, optionally containing $description:
 *    string, the description to display at the top of the page.  If the string
 *    is empty no description will be added.
 *  $content: string, the contents of the page.  Blank lines will be skipped.
 *    <br /> will be stripped from the end of each line.
 *  $tag: unused.
 * Returns:
 *  string, the HTML to insert in the page (Wordpress does that automatically).
 */
/**
 * @param array<string, string> $atts
 */
function JewelleryGridShortcode(
    array $atts,
    string $content,
    string $tag
): string {
    $tag .= 'make the linter happy.';
    $attrs = shortcode_atts(
        [
            'description' => '',
        ],
        $atts
    );

    $description = $attrs['description'];
    $attrs = ['do not use' => 'dollar_attrs'];
    $ranges = ParseJewelleryGridContents($content);
    // Turn the data structure into <divs>s.
    /*. array[int]string .*/ $divs = [];
    $slider_needed = false;
    foreach ($ranges as $i => $entry) {
        $id = 'item-' . $i;
        if (count($entry->images) > 1) {
            global $SLIDER_IMAGES;
            $SLIDER_IMAGES["#{$id}"] = json_encode_wrapper(
              $entry->images_to_data());
            $slider_needed = true;
        }

        $href = $entry->page_url;
        $src = $entry->images[0]->url;
        $alt = $entry->alt;
        $width = $entry->images[0]->width_str;
        $height = $entry->images[0]->height_str;
        $range = $entry->range;
        $div = <<<END_OF_DIV
            <div class="aligncenter jewellery-block">
              <div class="jewellery-picture-container">
                <a href="{$href}">
                  <img src="{$src}" alt="{$alt}"
                    width="{$width}" height="{$height}"
                    class="aligncenter block" id="{$id}-image"/>
                </a>
              </div>
              <div class="larger-text text-centered left-right-margin grey">
                <a href="{$href}">{$range}</a>
              </div>
            </div>
END_OF_DIV;
        $divs[] = $div;
    }

    /*. array[int]string .*/ $html = [];
    $html[] = <<<'END_OF_HTML'
        <div class="jewellery-grid">
END_OF_HTML;
    if ($description !== '') {
        $html[] = <<<END_OF_DESCRIPTION
          <div>
            <p class="grey large-text text-centered">{$description}</p>
          </div>
END_OF_DESCRIPTION;
    }
    $html[] = <<<'END_OF_HTML'
          <div class="flexboxrow jewellery-grid-inner">
END_OF_HTML;
    $html = array_merge($html, $divs);
    $html[] = <<<'END_OF_HTML'
          </div>
        </div>
END_OF_HTML;
    if ($slider_needed) {
        add_action('wp_footer', 'SliderSetupGeneric');
    }
    // Add a newline.
    $html[] = '';
    return do_shortcode(implode("\n", $html));
}
