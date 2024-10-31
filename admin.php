<?php
/**
 * Создание страницы настроек
 */
add_action( 'admin_menu', 'polzo_ogmeta_page' );

function polzo_ogmeta_page() {
	$polzo_ogmeta_thumbnail_admin_hook = add_options_page( 'ogMeta Setting', 'Polzo ogMeta', 'manage_options', 'polzo-ogmeta', 'polzo_ogmeta_options_page' );
	
	// Подключает стили для страницы настроек
	add_action( "admin_head-{$polzo_ogmeta_thumbnail_admin_hook}", 'polzo_ogmeta_admin_style' );	
}

/**
 * Стили для страницы настроек
 */
function polzo_ogmeta_admin_style() {
?>
	<style type="text/css">
		.wrap ul {
			list-style-type:disc;
			margin:10px 0 10px 15px;
		}
		.wrap ul ul {
			list-style-type:circle;
		}
		.wrap th {
			text-align: right;
			position: relative;
			top: 6px;
}
	</style>
<?php
}
//Если форма была отправлена, то применить изменения
	if (isset($_POST['polzo_submit_btn'])) 
	{   
	$polzo_ogmeta_thumbnail = $_POST['polzo_ogmeta_thumbnail'];
	$polzo_ogmeta_type = $_POST['polzo_ogmeta_type'];

	update_option('polzo_ogmeta_thumbnail', $polzo_ogmeta_thumbnail);
	update_option('polzo_ogmeta_type', $polzo_ogmeta_type);
	}
/**
 * Верстка
 */
function polzo_ogmeta_options_page() {
?>
	<div class="wrap">
		<?php screen_icon( 'plugins' ); ?>
		<h2>Polzo ogMeta Settings</h2>
		
		<form action="options.php" method="post">
			<?php settings_fields( 'polzo_ogmeta_options' ); ?>
			<?php do_settings_sections( 'polzo_ogmeta_section' ); ?>
			<br />
			<input type="submit" name="polzo_submit_btn" class="button-primary" value="<?php esc_attr_e( 'Save' ); ?>" />
		</form>
	</div>
<?php	
}

/**
 * WordPress Settings API to save plugin's data
 */
add_action( 'admin_init', 'polzo_ogmeta_init' );

function polzo_ogmeta_init() {
	register_setting(
		'polzo_ogmeta_options',	// same to the settings_field
		'polzo_ogmeta_thumb', 'polzo_ogmeta_type',	// options name
		'polzo_ogmeta_thumbnail_validate'	// validation callback
	);
	add_settings_section(
		'default',			// settings section (a setting page must have a default section, since we created a new settings page, we need to create a default section too)
		'Settings',		// section title
		'polzo_ogmeta_section_text',		// text for the section
		'polzo_ogmeta_section'		// specify the output of this section on the options page, same as in do_settings_section
	);
	add_settings_field(
		'polzo_ogmeta_thumb',		// field ID
		'Миниатюра по умолчанию:',	// Field title
		'polzo_ogmeta_thumbnail_setting',	// display callback
		'polzo_ogmeta_section',		// which settings page?
		'default'			// which settings section?
	);
	add_settings_field(
		'polzo_ogmeta_type',		// field ID
		'Тип:',	// Field title
		'polzo_ogmeta_type_setting',	// display callback
		'polzo_ogmeta_section',		// which settings page?
		'default'			// which settings section?
	);
}

function polzo_ogmeta_section_text() {
	echo '
	<p>Подробное описание на <a href="http://polzo.ru/ogmeta" alt="Polzo.ru">странице плагина</a>.</p>
	<p>Предложения и сообщения об ошибках пишите <a href="mailto:akurganow@polzo.ru?subject=ogMeta">сюда</a>, не забывайте в теме указать ogMeta, а в теле письма как можно подробнее опишите ошибку и действия которые ее вызвали.</p>';
}

function polzo_ogmeta_type_setting() {
	$type = get_option( 'polzo_ogmeta_type' );
	echo "<p><input id='polzo_ogmeta_type' name='polzo_ogmeta_type[default]' size='60' type='text' value='{$type['default']}' /></p>
	<p style='
    font-size: 0.9em;
    margin-top: -15px;
    margin-left: 5px;'>Список поддерживаемых типов можно найти <a href='http://opengraphprotocol.org/#types'>здесь</a>";
}

function polzo_ogmeta_thumbnail_setting() {
	$thumb = get_option( 'polzo_ogmeta_thumbnail' );
	echo "<p><input id='polzo_ogmeta_thumb' name='polzo_ogmeta_thumbnail[default]' size='60' type='text' value='{$thumb['default']}' /></p>";
}

function polzo_ogmeta_thumbnail_validate( $input ) {
	$valid['default'] = esc_url_raw( $input['default'] );
	if ( $valid['default'] != $input['default'] ) {
		add_settings_error(
			'polzo_ogmeta_thumb',				// title (?)
			'polzo_ogmeta_thumb_url_error',			// error ID (?)
			'Invalid link! Please enter a proper link',	// error message
			'error'						// message type
		);
	}
	return $valid;	
}
