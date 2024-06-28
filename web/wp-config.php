<?php
//Begin Really Simple SSL session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple SSL

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'sitramin_strmdbinc');

/** MySQL database username */
define('DB_USER', 'sitramin_stinuzr');

/** MySQL database password */
define('DB_PASSWORD', 'UEZsqJzQ*qM}');

/** MySQL hostname */
define('DB_HOST', 'localhost:3306');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '2n}LcR$%;O6mcwUDJ!4bb-[m)K_kBuG*VNj=$@h-A-nbD4Enf#]UD@GcP1p|8,SK');
define('SECURE_AUTH_KEY',  'Ac] SYIhe8J-p!T~hF*JOdpI~$&J.{>Z^>TFv[VC1jY,fogqq}35RM j_g<S}zmt');
define('LOGGED_IN_KEY',    '`P2fRn%d`lha^n/*0mMSadV<:oIs?Yj1rj>^w#eLaeT#Vndy^VklV3WM}qcChBe}');
define('NONCE_KEY',        'b9lN;>k^5w.8s.ExBq_]B%|T-] ur^N ?pWYT%E+y0,RI{u-Z},#5}*JgG1u0,R=');
define('AUTH_SALT',        'HbWH|JozP=DS%I<Q+GgzhL.b}6d2ws2fBLy&E5e|ry~*E$&^OR?i/bmt3o9MLl%F');
define('SECURE_AUTH_SALT', 'W9q?b~1@OG[aq9]j=o5L]%^*K91t1uY!&0gly-R&/Z^n;6y55w0i7c#cZF{]2w8k');
define('LOGGED_IN_SALT',   'Z+/qUP0d@dP2Y`D!fJZx;VXgE9o`[&uOUXj|<O]suHhZ{P>L%<*L0BU4@wB[b+V1');
define('NONCE_SALT',       'D,l;#i1_X<(-EI|m9MvVTg2@9or1$RMC8/ursh2>PcXj8D]{C/BXW-0A$b*w)c@Y');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
