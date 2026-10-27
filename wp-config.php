<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'layerone' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'MUhe%|q0h!]e0-Tj!9:2S[_LDe)D-R::$;+Ds`x#Yz+Q_-_KX-K^8|ih2<ae;2_g');
define('SECURE_AUTH_KEY',  '?tDPs);J~.=#,ge!a}gZn&9/D}n}a]xCj4QrS{KA4XeU [Hh}|<]e`dZ< l~rf<B');
define('LOGGED_IN_KEY',    '&lXg2<.w]bn[KTnJr93m}M5mPwsVRH!_[,E)*NV)|$k* U9JU_>RE<R1g[?zfW)f');
define('NONCE_KEY',        '/BWIR[(j @[+pxE.Ax`I#p8i+s*1VI`pvjt+jZQ~iv?[/=|J64]8}NTaeb:sUm/8');
define('AUTH_SALT',        'n]/)627i4J; aoMZ:C#c`tCm,Mazx.!L~zJ!S}0ISeoyU>,K^o<y{#KX(`9+&s)~');
define('SECURE_AUTH_SALT', '<?tjgAJr5@y4,ruL</6{c!pC}^`T4n>Z.`g5aIR?hw4zkE{3-mZ_{$|6G+MR6~bF');
define('LOGGED_IN_SALT',   '7T-z2?Q+#wtW%r_.lTbQh8i9rW-KTcU}%HRBm7* @uzY<$ X{l$2M`B@OqrBl;^c');
define('NONCE_SALT',       'nMj;I2qWn+S/O4^y-]`G^{@+*P*ueF|K%@@W5g-IEKzK+L}o~V%iY|VAb%Vq!-bo');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
