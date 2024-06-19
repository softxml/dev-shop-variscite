<?php
/**
 * uPress auto login script
 *
 * @package    uPress Auto Login
 * @author     uPress <support@upress.co.il>
 * @link       https://www.upress.co.il
 */
define( 'WP_USE_THEMES', false );
require_once( __DIR__ . '/wp-load.php' );
global $wpdb, $wp_version;

$min_wp_version = '3.7';

if ( version_compare( $wp_version, $min_wp_version, '<' ) ) {
    wp_die( "WordPress version is too old ({$wp_version} < {$min_wp_version})." );
    exit;
}

// No authorization parameter? get out...
if ( empty( $_GET['auth'] ) ) {
    wp_die( 'Authorization failed: Link expired or invalid, try loggin in again through the link in the dashboard.' );
    exit;
}

$current_url = "http" . ( is_ssl() ? 's' : '' ) . "://{$_SERVER['HTTP_HOST']}" . $_SERVER['REQUEST_URI'];
$current_url = substr( $current_url, 0, stripos( $current_url, basename( __FILE__ ) ) - 1 );
if ( $current_url != get_option( 'siteurl' ) ) {
    wp_redirect( get_option( 'siteurl' ) . "/" . basename( __FILE__ ) . "?auth={$_GET['auth']}" );
    exit;
}


function ip_to_num( $ip ) {
    $ips = explode( '.', $ip );

    return ( $ips[3] | $ips[2] << 8 | $ips[1] << 16 | $ips[0] << 24 );
}

function ip_in_range( $ip, $min, $max ) {
    $ip  = ip_to_num( $ip );
    $min = ip_to_num( $min );
    $max = ip_to_num( $max );

    return $min <= $ip && $ip <= $max;
}

function get_server_ip() {
    $server_ip = $_SERVER['SERVER_ADDR'];

    if ( ip_in_range( $server_ip, '10.0.0.1', '10.255.255.255' ) || ip_in_range( $server_ip, '172.16.0.0', '172.31.255.255' ) || ip_in_range( $server_ip, '192.168.0.0', '192.168.255.255' ) ) {
        $server_ip = gethostbyname( gethostname() );
    }

    return $server_ip;
}

function get_client_ip() {
    $client_ip = $_SERVER['REMOTE_ADDR'];

    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
        $client_ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
        $client_ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }

    return $client_ip;
}

$users          = [];
$sites          = [];
$network_admins = [];

$auth_key          = trim( $_GET['auth'] );
$verification_hash = '';
$server_ip         = get_server_ip();
$client_ip         = get_client_ip();

if ( function_exists( 'wp_roles' ) ) {
    $roles = wp_roles()->role_objects;
} else {
    global $wp_roles;
    $roles = $wp_roles->role_objects;
}

uasort( $roles, function ( $a, $b ) {
    if ( 'administrator' == $a->name ) {
        return - 1;
    }
    if ( 'administrator' == $b->name ) {
        return 1;
    }

    return strnatcmp( $a->name, $b->name );
} );

// Load list of users available to login to
if ( is_multisite() ) {
    // Get regular users from all blogs
    // get_sites() not available on wp < 4.6
    if( function_exists( 'get_sites' ) ) {
        $sites = get_sites();
    } else {
        $sites = wp_get_sites();
    }

    foreach ( $sites as $site ) {
        $blog_id    = is_object( $site ) ? $site->blog_id : $site['blog_id'];
        $site_users = get_users( [ 'blog_id' => $blog_id ] );
        $users      = array_merge( $users, $site_users );
    }

    // Get multisite super admins
    $wp_network_admins        = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->users );
    $network_admins_usernames = unserialize( $wpdb->get_var( 'SELECT * FROM ' . $wpdb->sitemeta . ' WHERE meta_key = \'site_admins\'', 3 ) );
    $wp_network_admins        = array_filter( $wp_network_admins, function ( $user ) use ( $network_admins_usernames ) {
        return in_array( $user->user_login, $network_admins_usernames );
    } );
    $wp_network_admins        = array_map( function ( $user ) {
        return get_user_by( 'ID', $user->ID );
    }, $wp_network_admins );
    $users                    = array_merge( $users, $wp_network_admins );
} else {
    // This is a normal wordpress install, get all regular users
    $users = get_users( [ 'role__in' => [ 'administrator', 'editor' ], 'number' => 100 ] );
}

// Filter out duplicate users
$mapped_users = [];
$users        = array_filter( $users, function ( $user ) use ( &$mapped_users ) {
    if ( in_array( $user->ID, $mapped_users ) ) {
        return false;
    }
    $mapped_users[] = $user->ID;

    return true;
} );
sort( $users );


if ( count( $_POST ) ) {
    // Check the verification hash
    $auth_key        = ! empty( $_POST['key'] ) ? trim( $_POST['key'] ) : null;
    $upress_auth     = ! empty( $_POST['auth'] ) ? trim( $_POST['auth'] ) : null;
    $calculated_hash = hash_hmac( 'sha256', $client_ip . $server_ip . $auth_key, 'EoE8mNAT7Ym975yJdNzEob8qS3ijfrONAT7x' );

    if ( empty( $upress_auth ) || ! hash_equals( $calculated_hash, $upress_auth ) ) {
        wp_die( 'Authorization failed: You are not allowed to login at this time.' );
    }

    if ( count( $users ) > 1 ) {
        $user_id    = (int) $_POST['userId'];
        $user       = get_user_by( 'id', $user_id );
        $user_login = $user->user_login;
    } else {
        $user_id    = $users[0]->ID;
        $user_login = $users[0]->user_login;
    }

    $user = wp_set_current_user( $user_id, $user_login );
    wp_set_auth_cookie( $user_id, true );
    do_action( 'wp_login', $user_login, $user );

    wp_redirect( get_admin_url() );
    exit;
} else {
    // Get auth data for current website
    $verify = wp_remote_post( 'https://my4.upress.io/api/autologin/authorize/v2', array(
        'user-agent' => 'uPressAutologin/' . $server_ip,
        'sslverify'  => true,
        'blocking'   => true,
        'body'       => array(
            'v'         => defined( 'AUTOLOGIN_DEV' ) ? AUTOLOGIN_DEV : $auth_key,
            'ip'        => $client_ip,
            'server_ip' => $server_ip,
            'host'      => get_site_url(),
            'dev'       => defined( 'AUTOLOGIN_DEV' ) ? AUTOLOGIN_DEV : ''
        ),
    ) );
    $verify = json_decode( wp_remote_retrieve_body( $verify ), true );
    if ( is_wp_error( $verify ) || ! isset( $verify['hash'] ) ) {
        wp_die( 'Authorization failed: Request expired.' );
    }
    $verification_hash = $verify['hash'];
}
?>
<!doctype html>
<html>
<head>
    <title>uPress Auto Login</title>
    <meta charset="utf-8">
    <link href="https://mycdn.upress.io/themes/upress/assets/css/autologin.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body class="login login-action-login wp-core-ui  locale-en-us">
<div id="login">
    <h1>
        <a href="https://my.upress.io/"
           title="Powered by uPress"
           tabindex="-1"
           style="background-image: none,url('https://www.upress.co.il/themes/upress/assets/img/newhomepage/logo600.png'); width: 160px; background-size: 140px">
            uPress Auto Login
        </a>
    </h1>

    <form method="post">
        <p>
            <label for="userId">Login as</label><br/>
            <select id="userId" name="userId" class="input" <?php echo count( $users ) <= 1 ? 'disabled' : ''; ?>>
                <?php if ( is_multisite() ) : ?>
                    <optgroup label="Super Administrators">
                        <?php foreach ( $users as $user ) : if ( ! in_array( $user->user_login, $network_admins_usernames ) ) {
                            continue;
                        } ?>
                            <option value="<?php echo esc_attr( $user->ID ); ?>">
                                <?php echo $user->user_login; ?>
                                <?php echo $user->user_login !== $user->display_name ? ' (' . $user->display_name . ')' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>

                <?php foreach ( $roles as $key => $role ) : ?>
                    <?php
                    $role_users = array_filter( $users, function ( $user ) use ( $key ) {
                        return $user->has_cap( $key );
                    } );
                    if ( count( $role_users ) <= 0 ) {
                        continue;
                    }
                    ?>
                    <optgroup label="<?php echo esc_attr( ucwords( str_replace( '_', ' ', $role->name ) ) ); ?>">
                        <?php foreach ( $role_users as $user ) : ?>
                            <option value="<?php echo esc_attr( $user->ID ); ?>">
                                <?php echo $user->user_login; ?>
                                <?php echo $user->user_login !== $user->display_name ? ' (' . $user->display_name . ')' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </p>

        <p class="submit">
            <input type="hidden" name="key"
                   value="<?php echo esc_attr( defined( 'AUTOLOGIN_DEV' ) ? AUTOLOGIN_DEV : $auth_key ); ?>">
            <input type="hidden" name="auth" value="<?php echo esc_attr( $verification_hash ); ?>">
            <button name="wp-submit" id="wp-submit" class="button button-primary button-large">Login</button>
        </p>
    </form>

    <p id="backtoblog"><a href="https://my.upress.io/">&larr; Back to uPress Dashboard</a></p>
</div>

<div class="clear"></div>
<script src="<?php echo $current_url; ?>/wp-includes/js/jquery/jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.min.js"></script>
<script>
    jQuery(function ($) {
        $('#userId').select2({
            minimumResultsForSearch: 10
        });
    });
</script>
</body>
</html>
