<?php
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
define('DB_NAME', 'wp_web_fundeeh');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '123456');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'Ro$ BGc@V%q^S$[+u{IAmKpBdr8>uu6_aOv7^r4_UJF<E]1?)(0Q*_dK3pQC9~if');
define('SECURE_AUTH_KEY',  '{%^!C0dUk Wr=*CK8saWO^!Ki]}u4WC$y,;#^}`u:;@~z^I3:/Lga6<t.:z&%W]K');
define('LOGGED_IN_KEY',    'sj/W6Sg[-WmGfVK@([NV$+k(+)/bnxCW #m%][7f4nuOu5@VN&+X;1h]w&lTt51F');
define('NONCE_KEY',        '/gx[4WGMUmRHd-V1./iKinDcTk._3V]_HpyVhV#dq0?^mr8WDc(I;3^}35?myv=T');
define('AUTH_SALT',        '}.g/~-AFU}?NM[qQ]dA.(eN38S;$ZVvB{k=(D^!qzXZF1B`Q(B[1c3L?H3rIb50}');
define('SECURE_AUTH_SALT', '0/a[yl_o5nAM=}(0n3|0V3~zuDcUou}iO!V-BGMio} BN1$#x${TP_c9c}eY5~L.');
define('LOGGED_IN_SALT',   'Pk1NiJI1@8sptpAQvmJ5B+cHwO-C5AfI($* Ch{;(aIQT6;2H<:KV`0s}@oWQ!6}');
define('NONCE_SALT',       '6qY.?SXg_pxlGou1gz{jID`H!C4c?84]r4Po1,uX_MN,;dNNk|%ofz|&@GaTG.d;');

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

