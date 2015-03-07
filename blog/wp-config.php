<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link http://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'librecores-blog');

/** MySQL database username */
define('DB_USER', 'librecores-blog');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

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
define('AUTH_KEY',         '$X]avz) 5,?GfLA4D:|On%`tREejNWAo+#}JN>13OLZ`6<Pgo4,,sKhw[or%&D~D');
define('SECURE_AUTH_KEY',  '?P!{?H!wf5(|8 x:<W<))[}5W,dfCW#%`WO$FV].z|Q{Y%6Ims[amh%>7LEQCA.P');
define('LOGGED_IN_KEY',    '92|N/;:))FK|j$`nHH&K+w#SE>j=]aDl6Kk@LFxT;B0~k{YTMuoN DjCiwf3gg@r');
define('NONCE_KEY',        'y<].&o|1M>Wt7Q>Sjtv9ezKX0M<p-p`V_R?{YDLEJT0he+g$}=`ZT3V%G<WkYkcc');
define('AUTH_SALT',        'oeb#10E1hz$#CaCaJNi88S&=c9 xcb)4[>`/$p$x.4f(W)v BnX[G+%PJ<>!tOj<');
define('SECURE_AUTH_SALT', 'Q_Dtmu%DaU, /5AKX}d5`p<e33Jj~%{q&yz<h[w1+6yM!WzH:=oJV`puTpp|<-c=');
define('LOGGED_IN_SALT',   'vMn%}.+Jzo5a9P0riU)Wf-+]Db?`A2(<eoN]Z),0q?gOB)u@`~XK!!!?uLT(Tcg1');
define('NONCE_SALT',       'J,u<_uMAslGXPAj~#p|b]qtax)7|hl[@(i^J+U,/gZIB{]QX9;nclafMFZ)Wz4!+');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
