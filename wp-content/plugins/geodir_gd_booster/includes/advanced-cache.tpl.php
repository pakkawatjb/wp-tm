<?php
/**
 * GB Booster Plugin
 *
 * This file serves as a template for the GD Booster plugin in WordPress.
 * The GD Booster plugin will fill the `%%` replacement codes automatically.
 *    This file becomes: `/wp-content/advanced-cache.php`.
 *
 * @package geodir_gd_booster\advanced_cache
 * 
 *@copyright GeoDirectory. <http://wpgeodirectory.com>
 * @license GNU General Public License, version 3
 */
namespace geodir_gd_booster
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!defined('GEODIR_GD_BOOSTER_ENABLE'))
		/**
		 * Is GD Booster enabled?
		 *
		 * @var string|integer|boolean A boolean-ish value; e.g. `1` or `0`.
		 */
		define('GEODIR_GD_BOOSTER_ENABLE', '%%GEODIR_GD_BOOSTER_ENABLE%%');

	if(!defined('GEODIR_GD_BOOSTER_DEBUGGING_ENABLE'))
		/**
		 * Is GD Booster debugging enabled?
		 *
		 * @var string|integer|boolean A boolean-ish value; e.g. `1` or `0`.
		 */
		define('GEODIR_GD_BOOSTER_DEBUGGING_ENABLE', '%%GEODIR_GD_BOOSTER_DEBUGGING_ENABLE%%');

	if(!defined('GEODIR_GD_BOOSTER_ALLOW_BROWSER_CACHE'))
		/**
		 * Allow browsers to cache each document?
		 *
		 * @var string|integer|boolean A boolean-ish value; e.g. `1` or `0`.
		 *
		 * @note If this is a `FALSE` (or an empty) value; GD Booster will send no-cache headers.
		 *    If `TRUE`, GD Booster will NOT send no-cache headers.
		 */
		define('GEODIR_GD_BOOSTER_ALLOW_BROWSER_CACHE', '%%GEODIR_GD_BOOSTER_ALLOW_BROWSER_CACHE%%');

	if(!defined('GEODIR_GD_BOOSTER_GET_REQUESTS'))
		/**
		 * Cache `$_GET` requests w/ a query string?
		 *
		 * @var string|integer|boolean A boolean-ish value; e.g. `1` or `0`.
		 */
		define('GEODIR_GD_BOOSTER_GET_REQUESTS', '%%GEODIR_GD_BOOSTER_GET_REQUESTS%%');

	if(!defined('GEODIR_GD_BOOSTER_CACHE_404_REQUESTS'))
		/**
		 * Cache 404 errors?
		 *
		 * @var string|integer|boolean A boolean-ish value; e.g. `1` or `0`.
		 */
		define('GEODIR_GD_BOOSTER_CACHE_404_REQUESTS', '%%GEODIR_GD_BOOSTER_CACHE_404_REQUESTS%%');
	
	if(!defined('GEODIR_GD_BOOSTER_EXCLUDE_COMBINES'))
		/**
		 * Exclude js/css file from combines.
		 *
		 * @var string.
		 */
		define('GEODIR_GD_BOOSTER_EXCLUDE_COMBINES', '%%GEODIR_GD_BOOSTER_EXCLUDE_COMBINES%%');

	if(!defined('GEODIR_GD_BOOSTER_FEEDS_ENABLE'))
		/**
		 * Cache XML/RSS/Atom feeds?
		 *
		 * @var string|integer|boolean A boolean-ish value; e.g. `1` or `0`.
		 */
		define('GEODIR_GD_BOOSTER_FEEDS_ENABLE', '%%GEODIR_GD_BOOSTER_FEEDS_ENABLE%%');

	if(!defined('GEODIR_GD_BOOSTER_DIR'))
		/**
		 * Directory used to store cache files; relative to `WP_CONTENT_DIR`.
		 *
		 * @var string Absolute server directory path.
		 */
		define('GEODIR_GD_BOOSTER_DIR', WP_CONTENT_DIR.'/'.'%%GEODIR_GD_BOOSTER_CACHE_DIR%%');

	if(!defined('GEODIR_GD_BOOSTER_MAX_AGE'))
		/**
		 * Cache expiration time.
		 *
		 * @var string Anything compatible with PHP's {@link \strtotime()}.
		 */
		define('GEODIR_GD_BOOSTER_MAX_AGE', '%%GEODIR_GD_BOOSTER_CACHE_MAX_AGE%%');

	if(!defined('GEODIR_GD_BOOSTER_404_CACHE_FILENAME'))
		/**
		 * 404 file name (if applicable).
		 *
		 * @var string A unique file name that will not conflict with real paths.
		 *    This should NOT include the extension; basename only please.
		 */
		define('GEODIR_GD_BOOSTER_404_CACHE_FILENAME', '----404----');

	if(!defined('GEODIR_GD_BOOSTER_PLUGIN_FILE'))
		/**
		 * Plugin file path.
		 *
		 * @var string Absolute server path to GDB plugin file.
		 */
		define('GEODIR_GD_BOOSTER_PLUGIN_FILE', '%%GEODIR_GD_BOOSTER_PLUGIN_FILE%%');
        
    if (!defined('GEODIR_GD_BOOSTER_MAX_URL_LENGTH')) {
		/**
		 * Exclude js/css file from combines.
		 *
		 * @var string.
		 */
		define('GEODIR_GD_BOOSTER_MAX_URL_LENGTH', '%%GEODIR_GD_BOOSTER_MAX_URL_LENGTH%%');
    }

	if (!defined('GEODIR_GD_BOOSTER_CDN_ENABLED')) {
		/**
		 * Exclude js/css file from combines.
		 *
		 * @var string.
		 */
		define('GEODIR_GD_BOOSTER_CDN_ENABLED', '%%GEODIR_GD_BOOSTER_CDN_ENABLED%%');
	}

	if (!defined('GEODIR_GD_BOOSTER_CDN_ROOT_URL')) {
		/**
		 * Exclude js/css file from combines.
		 *
		 * @var string.
		 */
		define('GEODIR_GD_BOOSTER_CDN_ROOT_URL', '%%GEODIR_GD_BOOSTER_CDN_ROOT_URL%%');
	}

	if (!defined('GEODIR_GD_BOOSTER_CDN_FILE_EXT')) {
		/**
		 * Exclude js/css file from combines.
		 *
		 * @var string.
		 */
		define('GEODIR_GD_BOOSTER_CDN_FILE_EXT', '%%GEODIR_GD_BOOSTER_CDN_FILE_EXT%%');
	}

	/*
	 * Include shared methods between {@link advanced_cache} and {@link plugin}.
	 */
	require_once dirname(GEODIR_GD_BOOSTER_PLUGIN_FILE).'/includes/share.php';

	/**
	 * GD Booster (Advanced Cache Handler)
	 *
	 * @package geodir_gd_booster\advanced_cache
	 * 
	 */
	class advanced_cache extends share # `/wp-content/advanced-cache.php`
	{
		/**
		 * Microtime; defined by class constructor for debugging purposes.
		 *
		 * @var float Result of a call to {@link \microtime()}.
		 */
		public $timer = 0;

		/**
		 * Flagged as `TRUE` if GDB advanced cache is active & running.
		 *
		 * @var boolean `TRUE` if GDB advanced cache is active & running.
		 */
		public $is_running = FALSE;

		/**
		 * Calculated protocol; one of `http://` or `https://`.
		 *
		 * @var float One of `http://` or `https://`.
		 */
		public $protocol = '';

		/**
		 * Calculated version salt; set by site configuration data.
		 *
		 * @var string|mixed Any scalar value does fine.
		 */
		public $version_salt = '';

		/**
		 * Calculated cache path for the current request;
		 *    absolute relative (no leading/trailing slashes).
		 *
		 * @var string Absolute relative (no leading/trailing slashes).
		 *    Defined by {@link maybe_start_output_buffering()}.
		 */
		public $cache_path = '';

		/**
		 * Calculated cache file location for the current request; absolute path.
		 *
		 * @var string Cache file location for the current request; absolute path.
		 *    Defined by {@link maybe_start_output_buffering()}.
		 */
		public $cache_file = '';

		/**
		 * Centralized 404 cache file location; absolute path.
		 *
		 * @var string Centralized 404 cache file location; absolute path.
		 *    Defined by {@link maybe_start_output_buffering()}.
		 */
		public $cache_file_404 = '';

		/**
		 * A possible version salt (string value); followed by the current request location.
		 *
		 * @var string Version salt (string value); followed by the current request location.
		 *    Defined by {@link maybe_start_output_buffering()}.
		 */
		public $salt_location = '';

		/**
		 * Array of data targeted at the postload phase.
		 *
		 * @var array Data and/or flags that work with various postload handlers.
		 */
		public $postload = array(
			'filter_status_header' => TRUE, 'wp_main_query' => TRUE,
			'set_debug_info'       => GEODIR_GD_BOOSTER_DEBUGGING_ENABLE,
		);

		/**
		 * An array of debug info.
		 *
		 * @var array An array of debug info; i.e. `reason_code` and `reason` (optional).
		 */
		public $debug_info = array('reason_code' => '', 'reason' => '');

		/**
		 * Have we caught the main WP loaded being loaded yet?
		 *
		 * @var boolean `TRUE` if main query has been loaded; else `FALSE`.
		 *
		 * @see wp_main_query_postload()
		 */
		public $is_wp_loaded_query = FALSE;

		/**
		 * Is the current request a WordPress 404 error?
		 *
		 * @var boolean `TRUE` if is a 404 error; else `FALSE`.
		 *
		 * @see wp_main_query_postload()
		 */
		public $is_404 = FALSE;

		/**
		 * Last HTTP status code passed through {@link \status_header}.
		 *
		 * @var integer Last HTTP status code (if applicable).
		 *
		 * @see maybe_filter_status_header_postload()
		 */
		public $http_status = 0;

		/**
		 * Is the current request a WordPress content type?
		 *
		 * @var boolean `TRUE` if is a WP content type.
		 *
		 * @see wp_main_query_postload()
		 */
		public $is_a_wp_content_type = FALSE;

		/**
		 * Current WordPress {@link \content_url()}.
		 *
		 * @var string Current WordPress {@link \content_url()}.
		 *
		 * @see wp_main_query_postload()
		 */
		public $content_url = '';

		/**
		 * Flag for {@link \is_user_loged_in()}.
		 *
		 * @var boolean `TRUE` if {@link \is_user_loged_in()}; else `FALSE`.
		 *
		 * @see wp_main_query_postload()
		 */
		public $is_user_logged_in = FALSE;

		/**
		 * Flag for {@link \is_maintenance()}.
		 *
		 * @var boolean `TRUE` if {@link \is_maintenance()}; else `FALSE`.
		 *
		 * @see wp_main_query_postload()
		 */
		public $is_maintenance = FALSE;

		/**
		 * Value for {@link plugin::$file()}.
		 *
		 * @var string The value of {@link plugin::$file()}.
		 *
		 * @see wp_main_query_postload()
		 */
		public $plugin_file = GEODIR_GD_BOOSTER_PLUGIN_FILE;

		/**
		 * No-cache because of the current {@link \PHP_SAPI}.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_PHP_SAPI_CLI = 'nc_debug_php_sapi_cli';

		/**
		 * No-cache because the current request includes the `?gdbAC=0` parameter.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_GDBAC_GET_VAR = 'nc_debug_gdbac_get_var';

		/**
		 * No-cache because of a missing `$_SERVER['HTTP_HOST']`.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_NO_SERVER_HTTP_HOST = 'nc_debug_no_server_http_host';

		/**
		 * No-cache because of a missing `$_SERVER['REQUEST_URI']`.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_NO_SERVER_REQUEST_URI = 'nc_debug_no_server_request_uri';

		/**
		 * No-cache because the {@link \GEODIR_GD_BOOSTER_ALLOWED} constant says not to.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_GEODIR_GD_BOOSTER_ALLOWED_CONSTANT = 'nc_debug_geodir_gd_booster_allowed_constant';

		/**
		 * No-cache because the `$_SERVER['GEODIR_GD_BOOSTER_ALLOWED']` environment variable says not to.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_GEODIR_GD_BOOSTER_ALLOWED_SERVER_VAR = 'nc_debug_geodir_gd_booster_allowed_server_var';

		/**
		 * No-cache because the {@link \DONOTCACHEPAGE} constant says not to.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_DONOTCACHEPAGE_CONSTANT = 'nc_debug_donotcachepage_constant';

		/**
		 * No-cache because the `$_SERVER['DONOTCACHEPAGE']` environment variable says not to.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_DONOTCACHEPAGE_SERVER_VAR = 'nc_debug_donotcachepage_server_var';

		/**
		 * No-cache because the current request method is uncacheable.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_UNCACHEABLE_REQUEST = 'nc_debug_uncacheable_request';

		/**
		 * No-cache because the current request originated from the server itself.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_SELF_SERVE_REQUEST = 'nc_debug_self_serve_request';

		/**
		 * No-cache because the current request is for a feed.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_FEED_REQUEST = 'nc_debug_feed_request';

		/**
		 * No-cache because the current request is systematic.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_WP_SYSTEMATICS = 'nc_debug_wp_systematics';

		/**
		 * No-cache because the current request is for an administrative area.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_WP_ADMIN = 'nc_debug_wp_admin';

		/**
		 * No-cache because the current request is multisite `/files/`.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_MS_FILES = 'nc_debug_ms_files';

		/**
		 * No-cache because the current user is like a logged-in user.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_IS_LIKE_LOGGED_IN_USER = 'nc_debug_is_like_logged_in_user';

		/**
		 * No-cache because the current user is logged into the site.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_IS_LOGGED_IN_USER = 'nc_debug_is_logged_in_user';

		/**
		 * No-cache because the current request contains a query string.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_GET_REQUEST_QUERIES = 'nc_debug_get_request_queries';

		/**
		 * No-cache because the current request is a 404 error.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_404_REQUEST = 'nc_debug_404_request';

		/**
		 * No-cache because the requested page is currently in maintenance mode.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_MAINTENANCE_PLUGIN = 'nc_debug_maintenance_plugin';

		/**
		 * No-cache because the current request is being compressed by an incompatible ZLIB coding type.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_OB_ZLIB_CODING_TYPE = 'nc_debug_ob_zlib_coding_type';

		/**
		 * No-cache because the current request resulted in a WP error message.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_WP_ERROR_PAGE = 'nc_debug_wp_error_page';

		/**
		 * No-cache because the current request is serving an uncacheable content type.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_UNCACHEABLE_CONTENT_TYPE = 'nc_debug_uncacheable_content_type';

		/**
		 * No-cache because the current request sent a non-2xx & non-404 status code.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_UNCACHEABLE_STATUS = 'nc_debug_uncacheable_status';

		/**
		 * No-cache because this is a new 404 error that we are symlinking.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_1ST_TIME_404_SYMLINK = 'nc_debug_1st_time_404_symlink';

		/**
		 * No-cache because we detected an early buffer termination.
		 *
		 * @var string A unique string identifier in the set of `NC_DEBUG_` constants.
		 */
		const NC_DEBUG_EARLY_BUFFER_TERMINATION = 'nc_debug_early_buffer_termination';

		/**
		 * Class constructor/cache handler.
		 */
		public function __construct()
		{
			parent::__construct(); // Shared constructor.

			if(!WP_CACHE || !GEODIR_GD_BOOSTER_ENABLE)
				return; // Not enabled.

			if(defined('WP_INSTALLING') || defined('RELOCATE'))
				return; // N/A; installing|relocating.

			$this->is_running = TRUE;
			$this->timer      = microtime(TRUE);

			$this->load_ac_plugins();
			$this->register_shutdown_flag();
			$this->maybe_stop_browser_caching();
			$this->maybe_start_output_buffering();
		}

		/**
		 * Loads any advanced cache plugin files found inside `/wp-content/ac-plugins`.
		 */
		public function load_ac_plugins()
		{
			if(!is_dir(WP_CONTENT_DIR.'/ac-plugins'))
				return; // Nothing to do here.

			$GLOBALS[__NAMESPACE__.'__advanced_cache']
				= $this; // Define now; so it's available for plugins.

			foreach((array)glob(WP_CONTENT_DIR.'/ac-plugins/*.php') as $_ac_plugin)
				if(is_file($_ac_plugin)) include_once $_ac_plugin;
			unset($_ac_plugin); // Houskeeping.
		}

		/**
		 * Registers a shutdown flag.
		 *
		 * @note In `/wp-settings.php`, GD Booster is loaded before WP registers its own shutdown function.
		 * Therefore, this flag is set before {@link shutdown_action_hook()} fires, and thus before {@link wp_ob_end_flush_all()}.
		 *
		 * @see http://www.php.net/manual/en/function.register-shutdown-function.php
		 */
		public function register_shutdown_flag()
		{
			register_shutdown_function(function ()
			{
				$GLOBALS[__NAMESPACE__.'__shutdown_flag'] = -1;
			});
		}

		/**
		 * Sends no-cache headers (if applicable).
		 */
		public function maybe_stop_browser_caching()
		{
			if(GEODIR_GD_BOOSTER_ALLOW_BROWSER_CACHE)
				return; // Allow in this case.

			if(!empty($_GET['gdbABC']) && filter_var($_GET['gdbABC'], FILTER_VALIDATE_BOOLEAN))
				return; // The query var says it's OK here.

			header_remove('Last-Modified');
			header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
			header('Cache-Control: no-cache, must-revalidate, max-age=0');
			header('Pragma: no-cache');
		}

		/**
		 * Start output buffering (if applicable); or serve a cache file (if possible).
		 *
		 * @note This is a vital part of GD Booster. This method serves existing (fresh) cache files.
		 *    It is also responsible for beginning the process of collecting the output buffer.
		 */
		public function maybe_start_output_buffering()
		{
			if(strcasecmp(PHP_SAPI, 'cli') === 0)
				return $this->maybe_set_debug_info($this::NC_DEBUG_PHP_SAPI_CLI);

			if(empty($_SERVER['HTTP_HOST']))
				return $this->maybe_set_debug_info($this::NC_DEBUG_NO_SERVER_HTTP_HOST);

			if(empty($_SERVER['REQUEST_URI']))
				return $this->maybe_set_debug_info($this::NC_DEBUG_NO_SERVER_REQUEST_URI);

			if(isset($_GET['gdbAC']) && !filter_var($_GET['gdbAC'], FILTER_VALIDATE_BOOLEAN))
				return $this->maybe_set_debug_info($this::NC_DEBUG_GDBAC_GET_VAR);

			if(defined('GEODIR_GD_BOOSTER_ALLOWED') && !GEODIR_GD_BOOSTER_ALLOWED)
				return $this->maybe_set_debug_info($this::NC_DEBUG_GEODIR_GD_BOOSTER_ALLOWED_CONSTANT);

			if(isset($_SERVER['GEODIR_GD_BOOSTER_ALLOWED']) && !$_SERVER['GEODIR_GD_BOOSTER_ALLOWED'])
				return $this->maybe_set_debug_info($this::NC_DEBUG_GEODIR_GD_BOOSTER_ALLOWED_SERVER_VAR);

			if(defined('DONOTCACHEPAGE'))
				return $this->maybe_set_debug_info($this::NC_DEBUG_DONOTCACHEPAGE_CONSTANT);

			if(isset($_SERVER['DONOTCACHEPAGE']))
				return $this->maybe_set_debug_info($this::NC_DEBUG_DONOTCACHEPAGE_SERVER_VAR);

			if($this->is_uncacheable_request_method())
				return $this->maybe_set_debug_info($this::NC_DEBUG_UNCACHEABLE_REQUEST);

			if(isset($_SERVER['REMOTE_ADDR'], $_SERVER['SERVER_ADDR']) && $_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR'])
				if(!$this->is_localhost()) return $this->maybe_set_debug_info($this::NC_DEBUG_SELF_SERVE_REQUEST);

			if(!GEODIR_GD_BOOSTER_FEEDS_ENABLE && $this->is_feed())
				return $this->maybe_set_debug_info($this::NC_DEBUG_FEED_REQUEST);

			if(preg_match('/\/(?:wp\-[^\/]+|xmlrpc)\.php(?:[?]|$)/i', $_SERVER['REQUEST_URI']))
				return $this->maybe_set_debug_info($this::NC_DEBUG_WP_SYSTEMATICS);

			if(is_admin() || preg_match('/\/wp-admin(?:[\/?]|$)/i', $_SERVER['REQUEST_URI']))
				return $this->maybe_set_debug_info($this::NC_DEBUG_WP_ADMIN);

			if(is_multisite() && preg_match('/\/files(?:[\/?]|$)/i', $_SERVER['REQUEST_URI']))
				return $this->maybe_set_debug_info($this::NC_DEBUG_MS_FILES);

			if($this->is_like_user_logged_in()) // Commenters, password-protected access, or actually logged-in.
				return $this->maybe_set_debug_info($this::NC_DEBUG_IS_LIKE_LOGGED_IN_USER);

			if(!GEODIR_GD_BOOSTER_GET_REQUESTS && $this->is_get_request_w_query() && (!isset($_GET['gdbAC']) || !filter_var($_GET['gdbAC'], FILTER_VALIDATE_BOOLEAN)))
				return $this->maybe_set_debug_info($this::NC_DEBUG_GET_REQUEST_QUERIES);

			$this->protocol       = $this->is_ssl() ? 'https://' : 'http://';
			$this->version_salt   = $this->apply_filters(__CLASS__.'__version_salt', '');
			$this->cache_path     = $this->build_cache_path($this->protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], '', $this->version_salt);
			$this->cache_file     = GEODIR_GD_BOOSTER_DIR.'/'.$this->cache_path; // NOT considering a user cache at all in the lite version.
			$this->cache_file_404 = GEODIR_GD_BOOSTER_DIR.'/'.$this->build_cache_path($this->protocol.$_SERVER['HTTP_HOST'].'/'.GEODIR_GD_BOOSTER_404_CACHE_FILENAME);
			$this->salt_location  = ltrim($this->version_salt.' '.$this->protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

			if(is_file($this->cache_file) && filemtime($this->cache_file) >= strtotime('-'.GEODIR_GD_BOOSTER_MAX_AGE))
			{
				list($headers, $cache) = explode('<!--headers-->', file_get_contents($this->cache_file), 2);

				$headers_list = $this->headers_list(); // Headers already sent (or ready to be sent).
				foreach(unserialize($headers) as $_header) // Preserves original headers sent with this file.
					if(!in_array($_header, $headers_list, TRUE) && stripos($_header, 'Last-Modified:') !== 0)
						header($_header); // Only cacheable/safe headers are stored in the cache.
				unset($_header); // Just a little housekeeping.

				if(GEODIR_GD_BOOSTER_DEBUGGING_ENABLE && $this->is_html_xml_doc($cache)) // Only if HTML comments are possible.
				{
					$total_time = number_format(microtime(TRUE) - $this->timer, 5, '.', '');
					$cache .= "\n".'<!-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ -->';
					// translators: This string is actually NOT translatable because the `__()` function is not available at this point in the processing.
					$cache .= "\n".'<!-- '.htmlspecialchars(sprintf(__('GD Booster fully functional :-) Cache file served for (%1$s) in %2$s seconds, on: %3$s.', $this->text_domain), $this->salt_location, $total_time, date('M jS, Y @ g:i a T'))).' -->';
				}
				exit($cache); // Exit with cache contents.
			}
			else // Start buffering output; we may need to cache the HTML generated by this request.
			{
				ob_start(array($this, 'output_buffer_callback_handler')); // Start output buffering.
			}
			return NULL; // Return value not applicable.
		}

		/**
		 * Used to setup debug info (if enabled).
		 *
		 * @param string $reason_code One of the `NC_DEBUG_` constants.
		 * @param string $reason Optionally override the built-in description with a custom message.
		 */
		public function maybe_set_debug_info($reason_code, $reason = '')
		{
			if(!GEODIR_GD_BOOSTER_DEBUGGING_ENABLE)
				return; // Nothing to do.

			$reason = (string)$reason;
			if(!($reason_code = (string)$reason_code))
				return; // Not applicable.

			$this->debug_info = array('reason_code' => $reason_code, 'reason' => $reason);
		}

		/**
		 * Filters WP {@link \status_header()} (if applicable).
		 */
		public function maybe_filter_status_header_postload()
		{
			if(empty($this->postload['filter_status_header']))
				return; // Nothing to do in this case.

			$_this = $this; // Reference needed by the closure below.
			add_filter('status_header', function ($status_header, $status_code) use ($_this)
			{
				if($status_code > 0) // Sending a status?
					$_this->http_status = (integer)$status_code;

				return $status_header; // Pass through this filter.

			}, PHP_INT_MAX, 2);
		}

		/**
		 * Hooks `NC_DEBUG_` info into the WordPress `shutdown` phase (if applicable).
		 */
		public function maybe_set_debug_info_postload()
		{
			if(!GEODIR_GD_BOOSTER_DEBUGGING_ENABLE)
				return; // Nothing to do.

			if(empty($this->postload['set_debug_info']))
				return; // Nothing to do in this case.

			if(is_admin()) return; // Not applicable.

			if(strcasecmp(PHP_SAPI, 'cli') === 0)
				return; // Let's not run the risk here.

			add_action('shutdown', array($this, 'maybe_echo_nc_debug_info'), PHP_INT_MAX - 10);
		}

		/**
		 * Grab details from WP and the GD Booster plugin itself,
		 *    after the main query is loaded (if at all possible).
		 *
		 * This is where we have a chance to grab any values we need from WordPress; or from the GDB plugin.
		 *    It is EXTREMEMLY important that we NOT attempt to grab any object references here.
		 *    Anything acquired in this phase should be stored as a scalar value.
		 *    See {@link output_buffer_callback_handler()} for further details.
		 *
		 * @attaches-to `wp` hook.
		 */
		public function wp_main_query_postload()
		{
			if(empty($this->postload['wp_main_query']))
				return; // Nothing to do in this case.

			if($this->is_wp_loaded_query || is_admin())
				return; // Nothing to do.

			if(!is_main_query()) return; // Not main query.

			$this->is_wp_loaded_query = TRUE;
			$this->is_404             = is_404();
			$this->is_user_logged_in  = is_user_logged_in();
			$this->content_url        = rtrim(content_url(), '/');
			$this->is_maintenance     = function_exists('is_maintenance') && is_maintenance();

			$_this = $this; // Reference for the closure below.
			add_action('template_redirect', function () use ($_this)
			{ // Move this AFTER `redirect_canonical` to avoid buggy WP behavior.
				$_this->is_a_wp_content_type = $_this->is_404 || $_this->is_maintenance
				                               || is_front_page() // See <https://core.trac.wordpress.org/ticket/21602#comment:7>
				                               || is_home() || is_singular() || is_archive() || is_post_type_archive() || is_tax() || is_search() || is_feed();
			}, 11);
		}

		/**
		 * Output buffer handler; i.e. the cache file generator.
		 *
		 * @note We CANNOT depend on any WP functionality here; it will cause problems.
		 *    Anything we need from WP should be saved in the postload phase as a scalar value.
		 *
		 * @param string  $buffer The buffer from {@link \ob_start()}.
		 * @param integer $phase A set of bitmask flags.
		 *
		 * @return string|boolean The output buffer, or `FALSE` to indicate no change.
		 *
		 * @throws \exception If unable to handle output buffering for any reason.
		 *
		 * @attaches-to {@link \ob_start()}
		 */
		public function output_buffer_callback_handler($buffer, $phase)
		{
			if(!($phase & PHP_OUTPUT_HANDLER_END)) // We do NOT chunk the buffer; so this should NOT occur.
				throw new \exception(sprintf(__('Unexpected OB phase: `%1$s`.', $this->text_domain), $phase));

			# Exclusion checks; there are MANY of these...

			$cache = trim((string)$buffer);
			if(!isset($cache[0])) // Allows a `0`.
				return FALSE; // Don't cache an empty buffer.

			if(!isset($GLOBALS[__NAMESPACE__.'__shutdown_flag']))
				return (boolean)$this->maybe_set_debug_info($this::NC_DEBUG_EARLY_BUFFER_TERMINATION);

			if(isset($_GET['gdbAC']) && !filter_var($_GET['gdbAC'], FILTER_VALIDATE_BOOLEAN))
				return (boolean)$this->maybe_set_debug_info($this::NC_DEBUG_GDBAC_GET_VAR);

			if(defined('GEODIR_GD_BOOSTER_ALLOWED') && !GEODIR_GD_BOOSTER_ALLOWED)
				return (boolean)$this->maybe_set_debug_info($this::NC_DEBUG_GEODIR_GD_BOOSTER_ALLOWED_CONSTANT);

			if(isset($_SERVER['GEODIR_GD_BOOSTER_ALLOWED']) && !$_SERVER['GEODIR_GD_BOOSTER_ALLOWED'])
				return (boolean)$this->maybe_set_debug_info($this::NC_DEBUG_GEODIR_GD_BOOSTER_ALLOWED_SERVER_VAR);

			if(defined('DONOTCACHEPAGE')) // WP Super Cache compatible.
				return (boolean)$this->maybe_set_debug_info($this::NC_DEBUG_DONOTCACHEPAGE_CONSTANT);

			if(isset($_SERVER['DONOTCACHEPAGE'])) // WP Super Cache compatible.
				return (boolean)$this->maybe_set_debug_info($this::NC_DEBUG_DONOTCACHEPAGE_SERVER_VAR);

			if($this->is_user_logged_in) // Actually logged into the site.
				return (boolean)$this->maybe_set_debug_info($this::NC_DEBUG_IS_LOGGED_IN_USER);

			if($this->is_like_user_logged_in()) // Commenters, password-protected access, or actually logged-in.
				return (boolean)$this->maybe_set_debug_info($this::NC_DEBUG_IS_LIKE_LOGGED_IN_USER); // Separate debug notice.

			if($this->is_404 && !GEODIR_GD_BOOSTER_CACHE_404_REQUESTS) // Not caching 404 errors.
				return (boolean)$this->maybe_set_debug_info($this::NC_DEBUG_404_REQUEST);

			if(stripos($cache, '<body id="error-page">') !== FALSE) // A WordPress-generated error?
				return (boolean)$this->maybe_set_debug_info($this::NC_DEBUG_WP_ERROR_PAGE);

			if(!$this->function_is_possible('http_response_code')) // Unable to reliably detect HTTP status code?
				if(stripos($cache, '<title>database error</title>') !== FALSE) // Fallback on this hackety hack.
					return (boolean)$this->maybe_set_debug_info($this::NC_DEBUG_WP_ERROR_PAGE);

			if(!$this->has_a_cacheable_content_type()) // Exclude non-HTML/XML content types.
				return (boolean)$this->maybe_set_debug_info($this::NC_DEBUG_UNCACHEABLE_CONTENT_TYPE);

			if(!$this->has_a_cacheable_status()) // This will catch WP Maintenance Mode too.
				return (boolean)$this->maybe_set_debug_info($this::NC_DEBUG_UNCACHEABLE_STATUS);

			if($this->is_maintenance) // <http://wordpress.org/extend/plugins/maintenance-mode>
				return (boolean)$this->maybe_set_debug_info($this::NC_DEBUG_MAINTENANCE_PLUGIN);

			if($this->function_is_possible('zlib_get_coding_type') && zlib_get_coding_type()
			   && (!($zlib_oc = ini_get('zlib.output_compression')) || !filter_var($zlib_oc, FILTER_VALIDATE_BOOLEAN))
			) return (boolean)$this->maybe_set_debug_info($this::NC_DEBUG_OB_ZLIB_CODING_TYPE);

			# Cache directory checks. The cache file directory is created here if necessary.

			if(!is_dir(GEODIR_GD_BOOSTER_DIR) && mkdir(GEODIR_GD_BOOSTER_DIR, 0775, TRUE) && !is_file(GEODIR_GD_BOOSTER_DIR.'/.htaccess'))
				file_put_contents(GEODIR_GD_BOOSTER_DIR.'/.htaccess', $this->htaccess_deny); // We know it's writable here.

			if(!is_dir($cache_file_dir = dirname($this->cache_file))) $cache_file_dir_writable = mkdir($cache_file_dir, 0775, TRUE);
			if(empty($cache_file_dir_writable) && !is_writable($cache_file_dir)) // Only check if it's writable, if we didn't just successfully create it.
				throw new \exception(sprintf(__('Cache directory not writable. GD Booster needs this directory please: `%1$s`. Set permissions to `755` or higher; `777` might be needed in some cases.', $this->text_domain), $cache_file_dir));

			# This is where a new 404 request might be detected for the first time; and where the 404 error file already exists in this case.

			$cache_file_tmp = $this->add_tmp_suffix($this->cache_file); // Cache/symlink creation is atomic; e.g. tmp file w/ rename.

			if($this->is_404 && is_file($this->cache_file_404))
				if(!(symlink($this->cache_file_404, $cache_file_tmp) && rename($cache_file_tmp, $this->cache_file)))
					throw new \exception(sprintf(__('Unable to create symlink: `%1$s` » `%2$s`. Possible permissions issue (or race condition), please check your cache directory: `%3$s`.', $this->text_domain), $this->cache_file, $this->cache_file_404, GEODIR_GD_BOOSTER_DIR));
				else return (boolean)$this->maybe_set_debug_info($this::NC_DEBUG_1ST_TIME_404_SYMLINK);

			/* ------- Otherwise, we need to construct & store a new cache file. -------- */

			if(GEODIR_GD_BOOSTER_DEBUGGING_ENABLE && $this->is_html_xml_doc($cache)) // Only if HTML comments are possible.
			{
				$total_time = number_format(microtime(TRUE) - $this->timer, 5, '.', '');
				$cache .= "\n".'<!-- '.htmlspecialchars(sprintf(__('GD Booster file path: %1$s', $this->text_domain), str_replace(WP_CONTENT_DIR, '', $this->is_404 ? $this->cache_file_404 : $this->cache_file))).' -->';
				$cache .= "\n".'<!-- '.htmlspecialchars(sprintf(__('GD Booster file built for (%1$s) in %2$s seconds, on: %3$s.', $this->text_domain),
				                                                ($this->is_404) ? '404 [error document]' : $this->salt_location, $total_time, date('M jS, Y @ g:i a T'))).' -->';
				$cache .= "\n".'<!-- '.htmlspecialchars(sprintf(__('This GD Booster file will auto-expire (and be rebuilt) on: %1$s (based on your configured expiration time).', $this->text_domain), date('M jS, Y @ g:i a T', strtotime('+'.GEODIR_GD_BOOSTER_MAX_AGE)))).' -->';
			}
			/*
			 * This is NOT a 404, or it is 404 and the 404 cache file doesn't yet exist (so we need to create it).
			 */
			if($this->is_404) // This is a 404; let's create 404 cache file and symlink to it.
			{
				if(file_put_contents($cache_file_tmp, serialize($this->cacheable_headers_list()).'<!--headers-->'.$cache) && rename($cache_file_tmp, $this->cache_file_404))
				{
					if(!(symlink($this->cache_file_404, $cache_file_tmp) && rename($cache_file_tmp, $this->cache_file)))
						throw new \exception(sprintf(__('Unable to create symlink: `%1$s` » `%2$s`. Possible permissions issue (or race condition), please check your cache directory: `%3$s`.', $this->text_domain), $this->cache_file, $this->cache_file_404, GEODIR_GD_BOOSTER_DIR));
					else
						return $cache; // Return the newly built cache; with possible debug information also.
				}

			} // NOT a 404; let's write a new cache file.
			else if(file_put_contents($cache_file_tmp, serialize($this->cacheable_headers_list()).'<!--headers-->'.$cache) && rename($cache_file_tmp, $this->cache_file))
				return $cache; // Return the newly built cache; with possible debug information also.

			@unlink($cache_file_tmp); // Clean this up (if it exists); and throw an exception with information for the site owner.
			throw new \exception(sprintf(__('GD Booster: failed to write cache file for: `%1$s`; possible permissions issue (or race condition), please check your cache directory: `%2$s`.', $this->text_domain), $_SERVER['REQUEST_URI'], GEODIR_GD_BOOSTER_DIR));
		}

		/**
		 * Echoes `NC_DEBUG_` info in the WordPress `shutdown` phase (if applicable).
		 *
		 * @attaches-to `shutdown` hook in WordPress w/ a late priority.
		 */
		public function maybe_echo_nc_debug_info() // Debug info in the shutdown phase.
		{
			if(!GEODIR_GD_BOOSTER_DEBUGGING_ENABLE)
				return; // Nothing to do.

			if(is_admin()) return; // Not applicable.

			if(strcasecmp(PHP_SAPI, 'cli') === 0)
				return; // Let's not run the risk here.

			if($this->debug_info && $this->has_a_cacheable_content_type() && $this->is_a_wp_content_type)
				echo (string)$this->maybe_get_nc_debug_info($this->debug_info['reason_code'], $this->debug_info['reason']);
		}

		/**
		 * Gets `NC_DEBUG_` info (if applicable).
		 *
		 * @param string $reason_code One of the `NC_DEBUG_` constants.
		 * @param string $reason Optional; to override the default description with a custom message.
		 *
		 * @return string The debug info; i.e. full description (if applicable).
		 */
		public function maybe_get_nc_debug_info($reason_code = '', $reason = '')
		{
			if(!GEODIR_GD_BOOSTER_DEBUGGING_ENABLE)
				return ''; // Not applicable.

			$reason = (string)$reason;
			if(!($reason_code = (string)$reason_code))
				return ''; // Not applicable.

			if(!$reason) switch($reason_code)
			{
				case $this::NC_DEBUG_PHP_SAPI_CLI:
					$reason = __('because `PHP_SAPI` reports that you are currently running from the command line.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_GDBAC_GET_VAR:
					$reason = __('because `$_GET[\'gdbAC\']` is set to a boolean-ish FALSE value.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_NO_SERVER_HTTP_HOST:
					$reason = __('because `$_SERVER[\'HTTP_HOST\']` is missing from your server configuration.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_NO_SERVER_REQUEST_URI:
					$reason = __('because `$_SERVER[\'REQUEST_URI\']` is missing from your server configuration.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_GEODIR_GD_BOOSTER_ALLOWED_CONSTANT:
					$reason = __('because the PHP constant `GEODIR_GD_BOOSTER_ALLOWED` has been set to a boolean-ish `FALSE` value at runtime. Perhaps by WordPress itself, or by one of your themes/plugins. This usually means that you have a theme/plugin intentionally disabling the cache on this page; and it\'s usually for a very good reason.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_GEODIR_GD_BOOSTER_ALLOWED_SERVER_VAR:
					$reason = __('because the environment variable `$_SERVER[\'GEODIR_GD_BOOSTER_ALLOWED\']` has been set to a boolean-ish `FALSE` value at runtime. Perhaps by WordPress itself, or by one of your themes/plugins. This usually means that you have a theme/plugin intentionally disabling the cache on this page; and it\'s usually for a very good reason.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_DONOTCACHEPAGE_CONSTANT:
					$reason = __('because the PHP constant `DONOTCACHEPAGE` has been set at runtime. Perhaps by WordPress itself, or by one of your themes/plugins. This usually means that you have a theme/plugin intentionally disabling the cache on this page; and it\'s usually for a very good reason.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_DONOTCACHEPAGE_SERVER_VAR:
					$reason = __('because the environment variable `$_SERVER[\'DONOTCACHEPAGE\']` has been set at runtime. Perhaps by WordPress itself, or by one of your themes/plugins. This usually means that you have a theme/plugin intentionally disabling the cache on this page; and it\'s usually for a very good reason.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_UNCACHEABLE_REQUEST:
					$reason = __('because `$_SERVER[\'REQUEST_METHOD\']` is `POST`, `PUT`, `DELETE`, `HEAD`, `OPTIONS`, `TRACE` or `CONNECT`. These request methods should never (ever) be cached in any way.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_SELF_SERVE_REQUEST:
					$reason = __('because `$_SERVER[\'REMOTE_ADDR\']` === `$_SERVER[\'SERVER_ADDR\']`; i.e. a self-serve request. DEVELOPER TIP: if you are testing on a localhost installation, please add `define(\'LOCALHOST\', TRUE);` to your `/wp-config.php` file while you run tests :-) Remove it (or set it to a `FALSE` value) once you go live on the web.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_FEED_REQUEST:
					$reason = __('because `$_SERVER[\'REQUEST_URI\']` indicates this is a `/feed`; and the configuration of this site says not to cache XML-based feeds.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_WP_SYSTEMATICS:
					$reason = __('because `$_SERVER[\'REQUEST_URI\']` indicates this is a `wp-` or `xmlrpc` file; i.e. a WordPress systematic file. WordPress systematics are never (ever) cached in any way.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_WP_ADMIN:
					$reason = __('because `$_SERVER[\'REQUEST_URI\']` or the `is_admin()` function indicates this is an administrative area of the site.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_MS_FILES:
					$reason = __('because `$_SERVER[\'REQUEST_URI\']` indicates this is a Multisite Network; and this was a request for `/files/*`, not a page.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_IS_LOGGED_IN_USER:
				case $this::NC_DEBUG_IS_LIKE_LOGGED_IN_USER:
					$reason = __('because the current user visiting this page (usually YOU), appears to be logged-in. The current configuration says NOT to cache pages for logged-in visitors. This message may also appear if you have an active PHP session on this site, or if you\'ve left (or replied to) a comment recently. If this message continues, please clear your cookies and try again.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_GET_REQUEST_QUERIES:
					$reason = __('because `$_GET` contains query string data. The current configuration says NOT to cache GET requests with a query string.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_404_REQUEST:
					$reason = __('because the WordPress `is_404()` Conditional Tag says the current page is a 404 error. The current configuration says NOT to cache 404 errors.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_MAINTENANCE_PLUGIN:
					$reason = __('because a plugin running on this installation says this page is in Maintenance Mode; i.e. is not available publicly at this time.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_OB_ZLIB_CODING_TYPE:
					$reason = __('because GD Booster is unable to cache already-compressed output. Please use `mod_deflate` w/ Apache; or use `zlib.output_compression` in your `php.ini` file. GD Booster is NOT compatible with `ob_gzhandler()` and others like this.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_WP_ERROR_PAGE:
					$reason = __('because the contents of this document contain `<body id="error-page">`, which indicates this is an auto-generated WordPress error message.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_UNCACHEABLE_CONTENT_TYPE:
					$reason = __('because a `Content-Type:` header was set via PHP at runtime. The header contains a MIME type which is NOT a variation of HTML or XML. This header might have been set by your hosting company, by WordPress itself; or by one of your themes/plugins.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_UNCACHEABLE_STATUS:
					$reason = __('because a `Status:` header (or an `HTTP/` header) was set via PHP at runtime. The header contains a non-`2xx` status code. This indicates the current page was not loaded successfully. This header might have been set by your hosting company, by WordPress itself; or by one of your themes/plugins.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_1ST_TIME_404_SYMLINK:
					$reason = __('because the WordPress `is_404()` Conditional Tag says the current page is a 404 error; and this is the first time it\'s happened on this page. Your current configuration says that 404 errors SHOULD be cached, so GD Booster built a cached symlink which points future requests for this location to your already-cached 404 error document. If you reload this page (assuming you don\'t clear the cache before you do so); you should get a cached version of your 404 error document. This message occurs ONCE for each new/unique 404 error request.', $this->text_domain);
					break; // Break switch handler.

				case $this::NC_DEBUG_EARLY_BUFFER_TERMINATION:
					$reason = __('because GD Booster detected an early output buffer termination. This may happen when a theme/plugin ends, cleans, or flushes all output buffers before reaching the PHP shutdown phase. It\'s not always a bad thing. Sometimes it is necessary for a theme/plugin to do this. However, in this scenario it is NOT possible to cache the output; since GD Booster is effectively disabled at runtime when this occurs.', $this->text_domain);
					break; // Break switch handler.

				default: // Default case handler.
					$reason = __('due to an unexpected behavior in the application. Please report this as a bug!', $this->text_domain);
					break; // Break switch handler.
			}
			return "\n".'<!-- '.htmlspecialchars(sprintf(__('GD Booster is NOT caching this page, %1$s', $this->text_domain), $reason)).' -->';
		}
	}

	/**
	 * Global GD Booster {@link advanced_cache} instance.
	 *
	 * 
	 *
	 * @var advanced_cache Global instance reference.
	 */
	$GLOBALS[__NAMESPACE__.'__advanced_cache'] = new advanced_cache();
}
namespace // Global namespace.
{
	/**
	 * Postload event handler; overrides core WP function.
	 *
	 * 
	 *
	 * @note See `/wp-settings.php` around line #226.
	 */
	function wp_cache_postload() // See: `wp-settings.php`.
	{
		$advanced_cache = $GLOBALS['geodir_gd_booster__advanced_cache'];
		/** @var $advanced_cache \geodir_gd_booster\advanced_cache */
		if(!$advanced_cache->is_running) return;

		if(!empty($advanced_cache->postload['filter_status_header']))
			$advanced_cache->maybe_filter_status_header_postload();

		if(!empty($advanced_cache->postload['set_debug_info']))
			$advanced_cache->maybe_set_debug_info_postload();

		if(!empty($advanced_cache->postload['wp_main_query']))
			add_action('wp', array($advanced_cache, 'wp_main_query_postload'), PHP_INT_MAX);
	}
}