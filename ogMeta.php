<?php
/*
Plugin Name: ogMeta
Plugin URI: http://wordpress.org/extend/plugins/polzo-ogmeta/
Description: Плагин добавляет open graph meta теги.
Version: 0.3
Author: Alexander Kurganow
Author URI: http://polzo.ru
*/

define('OPENGRAPH_NS_URI', 'http://opengraphprotocol.org/schema/');
$opengraph_ns_set = false;

function polzo_ogmeta_add_ns( $output ) {
  global $opengraph_ns_set;
  $opengraph_ns_set = true;

  $output .= ' xmlns:og="' . esc_attr(OPENGRAPH_NS_URI) . '"';
  return $output;
}
add_filter('language_attributes', 'polzo_ogmeta_add_ns');

// Если админка, загружает файл admin.php
if ( is_admin() )
	require plugin_dir_path( __FILE__ ).'admin.php';

add_action( 'wp_head', 'polzo_ogmeta_thumbnails' );

// Миниатюры
function polzo_ogmeta_thumbnails()
{
	global $posts;
	$thumbnail = get_option('polzo_ogmeta_thumbnail');	
	$default = $thumbnail['default'];
	
	if ( is_front_page() || is_search() ) {
		$thumb = $default;
	} else {
		$thumb_set = false;
		
		if ( is_single() || is_page() ) {
			if ( function_exists( 'has_post_thumbnail' ) ) { // Совместимость с темами не поддерживающими встроенные миниатюры
				if ( has_post_thumbnail( $posts[0]->ID ) ) {
					$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $posts[0]->ID) );
					$thumb = $thumb[0]; // URL из массива
					$thumb_set = true;
				}
			}
		}
		
		if ( !$thumb_set ) {
			$content = do_shortcode( $posts[0]->post_content ); // $posts is an array, fetch the first element
			$output = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches);
			if ( $output > 0 ) {
				$thumb = $matches[1][0];
				// #dirtyhack - check for nextgen loader file (if embedded as slideshow), and fallback to default
				if ( strpos( $thumb, 'nextgen-gallery/images/loader.gif' ) ) {
					$thumb = $default;
				}
			} else
				$thumb = $default;
		}
	}

	echo "\n<meta property=\"og:image\" content=\"$thumb\">\n";
}

add_action( 'wp_head', 'polzo_ogmeta_types' );
// Миниатюры
function polzo_ogmeta_types()
{
	$type = get_option( 'polzo_ogmeta_type' );
	echo "\n<meta property='og:type' content='{$type['default']}'>\n";
}

function polzo_ogmeta_metadata() {
  $metadata = array();

   // defualt properties defined at http://opengraphprotocol.org/
  $properties = array(
    // обязательно
    'title', 'url',

    // опционально
    'site_name', 'description',

    // позиционирование
    'longitude', 'latitude', 'street-address', 'locality', 'region',
    'postal-code', 'country-name',

    // контакты
    'email', 'phone_number', 'fax_number',
  );

  foreach ($properties as $property) {
    $filter = 'polzo_ogmeta_' . $property;
    $metadata["og:$property"] = apply_filters($filter, '');
  }

  return apply_filters('polzo_ogmeta_metadata', $metadata);
}

/**
 * Register filters for default Open Graph metadata.
 */
function polzo_ogmeta_default_metadata() {
  add_filter('polzo_ogmeta_title', 'polzo_ogmeta_default_title', 5);
  add_filter('polzo_ogmeta_url', 'polzo_ogmeta_default_url', 5);
  add_filter('polzo_ogmeta_site_name', 'polzo_ogmeta_default_sitename', 5);
}
add_filter('wp', 'polzo_ogmeta_default_metadata');

/**
 * Заголовок.
 */
function polzo_ogmeta_default_title( $title ) {
  if ( is_singular() && empty($title) ) {
    global $post;
    $title = $post->post_title;
  }

  return $title;
}

/**
 * URL.
 */
function polzo_ogmeta_default_url( $url ) {
  if (is_singular()) {
	if ( empty($url) ) $url = get_permalink();
	}
		else {
			$url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		}
	return $url;
}


/**
 * site_name.
 */
function polzo_ogmeta_default_sitename( $name ) {
  if ( empty($name) ) $name = get_bloginfo('name');
  return $name;
}

/**
 * Output Open Graph <meta> tags in the page header.
 */
function polzo_ogmeta_meta_tags() {
  global $opengraph_ns_set;

  $xml_ns = '';
  if ( !$opengraph_ns_set ) {
    $xml_ns = 'xmlns:og="' . esc_attr(OPENGRAPH_NS_URI) . '" ';
  }

  $metadata = polzo_ogmeta_metadata();
  foreach ( $metadata as $key => $value ) {
    if ( empty($key) || empty($value) ) continue;
    echo '<meta ' . $xml_ns . 'property="' . esc_attr($key) . '" content="' . esc_attr($value) . '">' . "\n";
  }
}
add_action('wp_head', 'polzo_ogmeta_meta_tags');

/**
 * Возвращает выдержку из поста в качестве og:description
 */
 
class polzo_ogmeta_class {
	function __construct() {
		if (is_admin()) {
		} else {
			add_action("wp_head", array(&$this, "front_header"));
		}
	}
	
	function front_header() {
		global $post;
		if (is_singular()) {
			the_post();
			echo '<meta property="og:description" content="'.esc_attr(strip_tags(get_the_excerpt())).'" />
';
			rewind_posts();
		}
		else {
			$description = get_bloginfo('description');
			echo '<meta property="og:description" content="'.$description.'">';
		}
	}
}
$ogmeta = new polzo_ogmeta_class();
?>
