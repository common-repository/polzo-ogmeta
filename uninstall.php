<?php
// Escape out if the file is not called from WordPress
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

delete_option( 'polzo_ogmeta_thumbnail' );
delete_option( 'polzo_ogmeta_type' );