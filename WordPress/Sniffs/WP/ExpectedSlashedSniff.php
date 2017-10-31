<?php
/**
 * WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace WordPress\Sniffs\WP;

use WordPress\AbstractFunctionParameterSniff;
use PHP_CodeSniffer_Tokens as Tokens;

/**
 * Verify that data passed to functions that expect slashed data is slashed.
 *
 * @package WPCS\WordPressCodingStandards
 *
 * @since
 *
 * @uses    \WordPress\Sniff::$custom_test_class_whitelist
 */
class ExpectedSlashedSniff extends AbstractFunctionParameterSniff {

	/**
	 * Casts that imply the data doesn't need to be slashed.
	 *
	 * @since
	 *
	 * @var int[]
	 */
	protected $slashed_casts = array(
		T_INT_CAST    => true,
		T_DOUBLE_CAST => true,
		T_BOOL_CAST   => true,
	);

	/**
	 * Tokens that are implicitly slashed and so don't need to be flagged.
	 *
	 * @since
	 *
	 * @var int[]
	 */
	protected $slashed_tokens = array(
		T_CONSTANT_ENCAPSED_STRING => true, // TODO
		T_LNUMBER                  => true,
		T_MINUS                    => true,
		T_TRUE                     => true,
		T_FALSE                    => true,
		T_NULL                     => true,
	);

	/**
	 * The list functions and their args that are expected to be slashed.
	 *
	 * @since
	 *
	 * @var array[]
	 */
	public static $expectedSlashedFunctionArgs = array(
		// Uses add_metadata().
		'add_comment_meta' => array( 2 => 'meta_key', 3 => 'meta_value' ),
		// Uses wp_unslash().
		'add_metadata' => array( 3 => 'meta_key', 4 => 'meta_value' ),
		// Uses wp_unslash().
		'add_ping' => array( 2 => 'uri' ),
		// Uses add_metadata().
		'add_post_meta' => array( 2 => 'meta_key', 3 => 'meta_value' ),
		// Uses add_metadata().
		'add_term_meta' => array( 2 => 'meta_key', 3 => 'meta_value' ),
		// Uses add_metadata().
		'add_user_meta' => array( 2 => 'meta_key', 3 => 'meta_value' ),
		// These are directly interpolated into a database query. Failure to slash
		// will result in SQL injection!!
		'check_comment' => array( 1 => 'author', 2 => 'email' ),
		// Uses stripslashes().
		'comment_exists' => array( 1 => 'comment_author' ),
		// Uses delete_metadata().
		'delete_comment_meta' => array( 2 => 'meta_key', 3 => 'meta_value' ),
		// Uses wp_unslash().
		'delete_metadata' => array( 3 => 'meta_key', 4 => 'meta_value' ),
		// Uses delete_metadata().
		'delete_post_meta_by_key' => array( 1 => 'post_meta_key' ),
		// Uses delete_metadata().
		'delete_post_meta' => array( 2 => 'meta_key', 3 => 'meta_value' ),
		// Uses delete_metadata().
		'delete_term_meta' => array( 2 => 'meta_key', 3 => 'meta_value' ),
		// Uses delete_metadata().
		'delete_user_meta' => array( 2 => 'meta_key', 3 => 'meta_value' ),
		// Uses delete_user_meta().
		'delete_user_option' => array( 2 => 'option_name' ),
		// Expects POST data.
		'edit_post' => array( 1 => 'post_data' ),
		// Uses get_term_by( 'name' ).
		'get_cat_ID' => array( 1 => 'category_name' ),
		// Uses get_search_feed_link().
		'get_search_comments_feed_link' => array( 1 => array( 'search_query' ) ),
		// Uses get_search_link().
		'get_search_feed_link' => array( 1 => 'search_query' ),
		// Uses stripslashes().
		'get_search_link' => array( 1 => 'query' ),
		// Uses wp_unslash() when $field is 'name'.
		'get_term_by' => array( 2 => 'value' ),
		// Uses wp_unslash().
		'install_blog' => array( 2 => 'blog_title' ),
		// Uses wp_get_nav_menu_object().
		'is_nav_menu' => array( 1 => 'menu' ),
		// Uses wp_unslash().
		'post_exists' => array( 1 => 'title', 2 => 'content', 3 => 'date' ),
		// Uses update_post_meta() when the $file isn't empty.
		'update_attached_file' => array( 2 => 'file' ),
		// Uses update_metadata().
		'update_comment_meta' => array( 2 => 'meta_key', 3 => 'meta_value' ),
		// Uses wp_unslash().
		'update_metadata' => array( 3 => 'meta_key', 4 => 'meta_value' ),
		// Uses wp_unslash().
		'update_meta' => array( 2 => 'meta_key', 3 => 'meta_value' ),
		// Uses update_metadata().
		'update_post_meta' => array( 2 => 'meta_key', 3 => 'meta_value' ),
		// Uses update_metadata().
		'update_term_meta' => array( 2 => 'meta_key', 3 => 'meta_value' ),
		// Uses update_metadata().
		'update_user_meta' => array( 2 => 'meta_key', 3 => 'meta_value' ),
		// Uses wp_unslash() when a string is passed; also accepts term ID.
		'term_exists' => array( 1 => 'term' ),
		// Uses update_user_meta().
		'update_user_option' => array( 2 => 'option_name', 3 => 'newvalue' ),
		// Uses wp_set_object_terms().
		'wp_add_object_terms' => array( 2 => 'terms' ),
		// Uses wp_update_nav_menu_object().
		'wp_create_nav_menu' => array( 1 => 'menu_name' ),
		// Uses wp_unslash().
		'wp_create_post_autosave' => array( 1 => 'post_data' ),
		// Uses wp_get_nav_menu_object().
		'wp_delete_nav_menu' => array( 1 => 'menu' ),
		// Just passed data through it, but is used by wp_new_comment(),
		// wp_update_comment(), etc.
		'wp_filter_comment' => array( 1 => 'commentarr' ),
		// Uses wp_get_nav_menu_object().
		'wp_get_nav_menu_items' => array( 1 => 'menu' ),
		// Uses get_term_by( 'name' ) if $menu is not a term ID or slug.
		'wp_get_nav_menu_object' => array( 1 => 'menu' ),
		// Uses wp_unslash().
		'wp_insert_comment' => array( 1 => 'commentdata' ),
		// Uses wp_unslash().
		'wp_insert_link' => array( 1 => 'linkdata' ),
		// Uses wp_unslash().
		'wp_insert_term' => array( 1 => 'term' ),
		// Uses wp_insert_comment() and wp_allow_comment().
		'wp_new_comment' => array( 1 => 'commentdata' ),
		// Uses term_exists(). The docs for wp_remove_object_terms() says that it
		// takes only term slugs or IDs, but it is also possible to pass in the term
		// names, and in that case they must be slashed.
		'wp_remove_object_terms' => array( 2 => 'terms' ),
		// Uses term_exists(), and wp_insert_term() if the term doesn't exist and is
		// a string. The docs for wp_set_object_terms() says that it takes only term
		// slugs or IDs, but it is also possible to pass in the term names, and in
		// that case they must be slashed.
		'wp_set_object_terms' => array( 2 => 'terms' ),
		// Uses wp_set_post_terms().
		'wp_set_post_categories' => array( 2 => 'post_categories' ),
		// Uses wp_set_post_terms().
		'wp_set_post_tags' => array( 2 => 'tags' ),
		// Uses wp_set_object_terms().
		'wp_set_post_terms' => array( 2 => 'terms' ),
		// Uses update_post_meta().
		'wp_update_attachment_metadata' => array( 2 => 'data' ),
		// Uses wp_unslash().
		'wp_update_comment' => array( 1 => 'commentarr' ),
		// Uses install_blog().
		'wpmu_create_blog' => array( 3 => 'title' ),
		// Uses wp_unslash().
		'wpmu_validate_blog_signup' => array( 2 => 'blog_title' ),
		// Uses wp_unslash().
		'wpmu_welcome_notification' => array( 4 => 'title' ),
		// Uses wp_unslash().
		'WP_Press_This::side_load_images' => array( 2 => 'content' ),
		// Uses wp_unslash().
		'WP_Customize_Setting::sanitize' => array( 1 => 'value' ),
	);

	/**
	 * A list of functions and their args that are expected to be partly slashed.
	 *
	 * Sometimes a function takes an argument that is an array, and some of the
	 * values in the array are expected to be slashed while others do not need to be
	 * (because they are a type of value that doesn't need to be slashed, like an
	 * integer or boolean, perhaps). So it is OK to pass the entire array through
	 * a slashing function, but it's also permissible to slash only those args that
	 * need to be (i.e., when the array is being passed to the function directly,
	 * rather than being assigned to a variable first; then we can check each key
	 * value pair and only flag those that really need to be slashed).
	 *
	 * This is a list of those functions. For each function, there is an array which
	 * is indexed by the number of the argument which is expected to be an array with
	 * mixed slashing. The values are lists of the keys within that array that are
	 * expected to be slashed (any other keys are expected unslashed).
	 *
	 * @since
	 *
	 * @var array[]
	 */
	public static $partlySlashedFunctionArgs = array(
		// Uses query_posts(), 'q' is passed as 's'.
		'_wp_ajax_menu_quick_search' => array( 1 => array( 'q' ) ),
		// Uses get_posts() with the $args if they are an array.
		'get_children' => array( 1 => array( 's', 'title' ) ),
		// Uses get_term_by( 'name' ). All of the other args are either integers or
		// accept slug-like values.
		'get_bookmarks' => array( 1 => array( 'category_name' ) ),
		// Uses wp_unslash() on these. All of the other args are either integers or
		// accept slug-like values.
		'get_pages' => array( 1 => array( 'meta_key', 'meta_value' ) ),
		// Uses WP_Query::query().
		'get_posts' => array( 1 => array( 's', 'title' ) ),
		// Uses WP_Query::query().
		'query_posts' => array( 1 => array( 's', 'title' ) ),
		// Uses wp_unslash() on some of these. All of the other args are either
		// integers, slugs, or dates.
		'wp_allow_comment' => array(
			1 => array(
				'comment_author',
				'comment_author_email',
				'comment_author_url',
				'comment_author_IP',
				'comment_content',
				'comment_agent',
			),
		),
		// Uses get_posts().
		'wp_get_nav_menu_items' => array( 2 => array( 's', 'title' ) ),
		// Uses get_children().
		'wp_get_post_revisions' => array( 2 => array( 's', 'title' ) ),
		// Uses get_posts().
		'wp_get_recent_posts' => array( 1 => array( 's', 'title' ) ),
		// Uses wp_insert_post().
		'wp_insert_attachment' => array(
			1 => array(
				'post_content',
				'post_content_filtered',
				'post_title',
				'post_excerpt',
				'post_password',
				'to_ping',
				'pinged',
				'guid',
				'post_category',
				'tags_input',
				'tax_input',
				'meta_input',
			),
		),
		// Uses wp_unslash().
		'wp_insert_post' => array(
			1 => array(
				'post_content',
				'post_content_filtered',
				'post_title',
				'post_excerpt',
				'post_password',
				'to_ping',
				'pinged',
				'guid',
				'post_category',
				'tags_input',
				'tax_input',
				'meta_input',
			),
		),
		// Uses wp_unslash() on this. All of the other args are integers or slugs.
		// The 'name' arg is also expected slashed, but this is always overridden by
		// $term.
		'wp_insert_term' => array( 3 => array( 'description' ) ),
		// Uses wp_insert_post() or wp_update_post(). All other values are slugs or
		// integers.
		'wp_update_nav_menu_item' => array(
			3 => array( 'menu-item-description', 'menu-item-attr-title', 'menu-item-title' )
		),
		// Uses get_term_by( 'name' ) with 'menu-name' and also passes the data to
		// wp_insert_term() if the menu doesn't exist, or else wp_update_term().
		'wp_update_nav_menu_object' => array( 2 => array( 'description', 'menu-name' ) ),
		// Uses wp_insert_post(). If the $postarr is actually a post object and not
		// an array, then it should be unslashed instead.
		'wp_update_post' => array(
			1 => array(
				'post_content',
				'post_content_filtered',
				'post_title',
				'post_excerpt',
				'post_password',
				'to_ping',
				'pinged',
				'guid',
				'post_category',
				'tags_input',
				'tax_input',
				'meta_input',
			),
		),
		// Uses wp_unslash() on these. All of the other args are integers or slugs.
		'wp_update_term' => array( 3 => array( 'description', 'name' ) ),
		// Uses WP_Query::__construct().
		'WP_Customize_Nav_Menus::search_available_items_query' => array( 1 => array( 's' ) ),
		// Uses WP_Query::query().
		'WP_Query::__construct' => array( 1 => array( 's', 'title' ) ),
		// WP_Query::get_posts() uses stripslashes() on the 'title', and
		// WP_Query::parse_search() uses stripslashes() on 's'. All other args are
		// either integers, booleans, or slug-like strings.
		'WP_Query::parse_query' => array( 1 => array( 's', 'title' ) ),
		// WP_Query::get_posts() uses stripslashes() on the 'title', and
		// WP_Query::parse_search() uses stripslashes() on 's'. All other args are
		// either integers, booleans, or slug-like strings.
		'WP_Query::query' => array( 1 => array( 's', 'title' ) ),
		// Uses WP_Query::query().
		'_WP_Editors::wp_link_query' => array( 1 => array( 's' ) ),
	);

	/**
	 * A list of functions and their args that are expected to be partly slashed and
	 * partly unslashed.
	 *
	 * Sometimes a function takes an argument that is an array, and some of the
	 * values in the array are expected to be slashed while others specifically
	 * expected to be unslashed.
	 *
	 * See self::$partlySlashedFunctionArgs for an explanation of the array format.
	 *
	 * @since
	 *
	 * @var array[]
	 */
	public static $mixedSlashedFunctionArgs = array(
		// Uses get_bookmarks(). When 'categorize' is true, passing 'categroy_name'
		// doesn't make sense, so this only applies when 'categorize' false. In fact,
		// when 'categorize' is true, the 'category_name' is passed to get_terms() as
		// 'name__like', which is expected unslashed. So it is only expected slashed
		// when 'categorize' is false.
		'wp_list_bookmarks' => array(
			1 => array(
				'slashed' => array( 'category_name' ),
				'unslashed' => array(
					'title_li',
					'title_before',
					'title_after',
					'class',
					'category_before',
					'category_after',
				),
			),
		),
		// Uses get_pages().
		'wp_dropdown_pages' => array(
			1 => array(
				'slashed' => array( 'meta_key', 'meta_value' ),
				'unslashed' => array(
					'selected',
					'name',
					'id',
					'show_option_none',
					'show_option_no_change',
					'option_none_value',
				),
			),
		),
		// Uses get_pages().
		'wp_list_pages' => array(
			1 => array(
				'slashed' => array( 'meta_key', 'meta_value' ),
				'unslashed' => array( 'date_format', 'link_after', 'link_before', 'title_li' ),
			),
		),
		// Uses wp_unslash(), but hashes the password first. Also uses
		// update_user_meta().
		'wp_insert_user' => array(
			1 => array(
				'slashed' => array(
					'description',
					'display_name',
					'first_name',
					'last_name',
					'nickname',
					'user_email',
					'user_url',
				),
				'unslashed' => array( 'user_pass' ),
			),
		),
		// Uses wp_insert_user().
		'wp_update_user' => array(
			1 => array(
				'slashed' => array(
					'description',
					'display_name',
					'first_name',
					'last_name',
					'nickname',
					'user_email',
					'user_url',
				),
				'unslashed' => array( 'user_pass' ),
			),
		),
//		'wp_insert_category' => array( 3 => array( 'name', 'description' ) ),
	);

	/**
	 * The list of filters and their args that are expected to be slashed.
	 *
	 * @todo Sniff for these.
	 *
	 * @since ${PROJECT_VERSION}
	 *
	 * @var array
	 */
	public $expectedSlashedFilterArgs = array(
		// Result passed through wp_unslash().
		'add_ping' => array( 1 => 'new' ),
		// Result passed through wp_unslash().
		'pre_comment_author_email' => array( 1 => 'author_email_cookie' ),
		// Result passed through wp_unslash().
		'pre_comment_author_name' => array( 1 => 'author_cookie' ),
		// Result passed through wp_unslash().
		'pre_comment_author_url' => array( 1 => 'author_url_cookie' ),
		// Called in wp_filter_comment().
		'pre_comment_content' => array( 1 => 'comment_content' ),
		// Called in wp_filter_comment().
		'pre_comment_user_agent' => array( 1 => 'comment_agent' ),
		// Called in wp_insert_user().
		'pre_user_description' => array( 1 => 'description' ),
		// Called in wp_insert_user().
		'pre_user_display_name' => array( 1 => 'display_name' ),
		// Called in wp_insert_user().
		'pre_user_email' => array( 1 => 'raw_user_email' ),
		// Called in wp_insert_user().
		'pre_user_first_name' => array( 1 => 'first_name' ),
		// Called in wp_insert_user().
		'pre_user_last_name' => array( 1 => 'last_name' ),
		// Called in wp_insert_user().
		'pre_user_nickname' => array( 1 => 'nickname' ),
		// Called in wp_insert_user().
		'pre_user_url' => array( 1 => 'raw_user_url' ),
		// Result passed to wp_list_bookmarks().
		'widget_links_args' => array( 1 => 'widget_links_args' ),
		// Result passed to wp_list_pages().
		'widget_pages_args' => array( 1 => 'args' ),
	);

	/**
	 * Functions that slash data.
	 *
	 * @since
	 *
	 * @var array
	 */
	public static $slashingFunctions = array(
		'wp_slash' => true,
	);

	/**
	 * Functions that return slashed data automatically.
	 *
	 * @since
	 *
	 * @var array
	 */
	public static $autoSlashingFunctions = array(
		'esc_url_raw'                => true,
		'esc_url'                    => true,
		'get_current_user_id'        => true,
		'sanitize_key'               => true,
		'sanitize_title'             => true,
		'sanitize_title_with_dashes' => true,
		'time'                       => true,
		'wp_filter_comment'          => true,
	);

	/**
	 * Groups of functions to restrict.
	 *
	 * @since
	 *
	 * @return array
	 */
	public function getGroups() {
		$this->target_functions = array_merge(
			self::$expectedSlashedFunctionArgs
			, self::$partlySlashedFunctionArgs
			, self::$mixedSlashedFunctionArgs
		);

		return parent::getGroups();
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @since
	 *
	 * @param int $stackPtr The position of the current token in the stack.
	 *
	 * @return int|void Integer stack pointer to skip forward or void to continue
	 *                  normal file processing.
	 */
	public function process_token( $stackPtr ) {

		if ( $this->has_whitelist_comment( 'slashing', $stackPtr ) ) {
			return;
		}

		parent::process_token( $stackPtr );

	} // End process_token().

	/**
	 * Process the parameters of a matched function.
	 *
	 * @since
	 *
	 * @param int    $stackPtr        The position of the current token in the stack.
	 * @param array  $group_name      The name of the group which was matched.
	 * @param string $matched_content The token content (function name) which was matched.
	 * @param array  $parameters      Array with information about the parameters.
	 *
	 * @return void
	 */
	public function process_parameters( $stackPtr, $group_name, $matched_content, $parameters ) {

		$function_name = $matched_content;

		if (
			isset( self::$mixedSlashedFunctionArgs[ $function_name ] )
			|| isset( self::$partlySlashedFunctionArgs[ $function_name ] )
		) {
			$this->process_mixed_function_args( $parameters, $function_name );
		}

		if ( isset( self::$expectedSlashedFunctionArgs[ $function_name ] ) ) {
			$this->process_expected_slashed_args( $parameters, $function_name );
		}

	} // End process_parameters().

	/**
	 * Process a function that expects some arguments with mixed slashing.
	 *
	 * @since
	 *
	 * @param array  $parameters    Info on the function parameters.
	 * @param string $function_name The function name.
	 */
	protected function process_mixed_function_args( $parameters, $function_name ) {

		$phpcsFile = $this->phpcsFile;
		$tokens = $this->tokens;

		if ( isset( self::$mixedSlashedFunctionArgs[ $function_name ] ) ) {
			$args = self::$mixedSlashedFunctionArgs[ $function_name ];
			$is_mixed = true;
		} else {
			$args = self::$partlySlashedFunctionArgs[ $function_name ];
			$is_mixed = false;
		}

		foreach ( $args as $arg_index => $keys ) {

			if ( ! isset( $parameters[ $arg_index ]['start'] ) ) {
				break;
			}

			$argPtr = $parameters[ $arg_index ]['start'];

			$arg_start = $phpcsFile->findNext(
				Tokens::$emptyTokens,
				$argPtr,
				null,
				true,
				null,
				true
			);

			if ( $is_mixed ) {
				$slashed_keys = $keys['slashed'];
				$unslashed_keys = $keys['unslashed'];
			} else {
				$slashed_keys = $keys;
				$unslashed_keys = array();
			}

			// If this is the array declaration itself, we can check per-key.
			// Otherwise, we can only give a general error/warning.
			if ( T_ARRAY !== $tokens[ $arg_start ]['code'] ) {

				if ( $is_mixed ) {

					$phpcsFile->addWarning(
						'%s() expects the value of %s to be slashed with wp_slash(), and %s to be unslashed.',
						$argPtr,
						'ExpectedMixed',
						array(
							$function_name,
							implode( ', ', $slashed_keys ),
							implode( ', ', $unslashed_keys )
						)
					);

				} else {

					if (
						! isset( self::$slashingFunctions[ $tokens[ $arg_start ]['content'] ] )
						&& ! isset( self::$autoSlashingFunctions[ $tokens[ $arg_start ]['content'] ] )
					) {

						$this->addError(
							'%s() expects the value of %s to be slashed with wp_slash().',
							$argPtr,
							'ExpectedPartlySlashed',
							array( $function_name, implode( ', ', $slashed_keys ) )
						);
					}
				}

				break;
			}

			$array_opener = $phpcsFile->findNext(
				Tokens::$emptyTokens,
				$arg_start + 1,
				null,
				true,
				null,
				true
			);

			// This likely indicates a syntax error.
			if ( ! isset( $tokens[ $array_opener ]['parenthesis_closer'] ) ) {
				break;
			}

			$start = $array_opener;

			while (
				$next_double_arrow = $phpcsFile->findNext(
					T_DOUBLE_ARROW,
					$start + 1,
					$tokens[ $array_opener ]['parenthesis_closer']
				)
			) {
				$is_slashed = false;
				$start      = $next_double_arrow + 1;

				$value_ptr = $phpcsFile->findNext(
					Tokens::$emptyTokens,
					$next_double_arrow + 1,
					null,
					true,
					null,
					true
				);

				// These tokens are implicitly slashed, so we don't need to slash them.
				if (
					isset( $this->slashed_tokens[ $tokens[ $value_ptr ]['code'] ] )
					|| isset( $this->slashed_casts[ $tokens[ $value_ptr ]['code'] ] )
				) {
					continue;
				}

				$key_ptr = $phpcsFile->findPrevious(
					Tokens::$emptyTokens,
					$next_double_arrow - 1,
					null,
					true,
					null,
					true
				);

				if ( T_CONSTANT_ENCAPSED_STRING !== $tokens[ $key_ptr ]['code'] ) {
					continue;
				}

				$key_name = trim( $tokens[ $key_ptr ]['content'], '\'"' );

				if (
					isset( self::$slashingFunctions[ $tokens[ $value_ptr ]['content'] ] )
					|| isset( self::$autoSlashingFunctions[ $tokens[ $value_ptr ]['content'] ] )
				) {
					$is_slashed = true;
				}

				if ( ! $is_slashed && in_array( $key_name, $slashed_keys, true ) ) {

					$this->addError(
						'%s() expects the value of %s to be slashed with wp_slash().',
						$value_ptr,
						'ExpectedKeySlashed',
						array( $function_name, $key_name )
					);

				} elseif ( $is_slashed && in_array( $key_name, $unslashed_keys, true ) ) {

					$this->addError(
						'%s() expects the value of %s to be unslashed.',
						$value_ptr,
						'ExpectedKeyUnslashed',
						array( $function_name, $key_name )
					);
				}
			}
		}

	} // End process_mixed_function_args()

	/**
	 * Process a function that expects some args to be slashed.
	 *
	 * @since
	 *
	 * @param array  $parameters    Info on the function parameters.
	 * @param string $function_name The function name.
	 */
	protected function process_expected_slashed_args( $parameters, $function_name ) {

		$phpcsFile = $this->phpcsFile;
		$tokens = $this->tokens;

		// Special handling for get_term_by( 'name' ).
		if ( 'get_term_by' === $function_name && isset( $parameters[1] ) ) {

			$byPtr = $phpcsFile->findNext(
				Tokens::$emptyTokens,
				$parameters[1]['start'] + 1,
				null,
				true,
				null,
				true
			);

			// If we know what we are getting the term by, and it isn't 'name', then
			// we can skip this. Otherwise we're getting it by name or the arg is
			// supplied dynamically, so we don't know whether we are or not.
			if (
				T_CONSTANT_ENCAPSED_STRING === $tokens[ $byPtr ]['code']
				&& trim( $tokens[ $byPtr ]['content'], '\'"' ) !== 'name'
			) {
				return;
			}
		}

		foreach ( self::$expectedSlashedFunctionArgs[ $function_name ] as $arg_index => $name ) {

			if ( ! isset( $parameters[ $arg_index ] ) ) {
				break;
			}

			$argPtr = $parameters[ $arg_index ]['start'];

			$in_cast = false;
			$watch   = true;

			for ( $i = $argPtr; $i <= $parameters[ $arg_index ]['end']; $i++ ) {

				if ( T_COMMA === $tokens[ $i ]['code'] ) {
					$watch = true;
					continue;
				}

				// If we're not watching right now, do nothing.
				if ( ! $watch ) {

					// Wake up on concatenation characters, another part to check.
					if ( T_STRING_CONCAT === $tokens[ $i ]['code'] ) {
						$watch = true;
					}

					continue;
				}

				// Ignore whitespaces and comments.
				if ( in_array( $tokens[ $i ]['code'], Tokens::$emptyTokens ) ) {
					continue;
				}

				// Skip to the end of a function call if it has been casted to a safe value.
				if ( $in_cast && T_OPEN_PARENTHESIS === $tokens[ $i ]['code'] ) {
					$i = $tokens[ $i ]['parenthesis_closer'];
					$in_cast = false;
					continue;
				}

				// Handle arrays for those functions that accept them.
				if ( T_ARRAY === $tokens[ $i ]['code'] ) {
					$i ++; // Skip the opening parenthesis.
					continue;
				}

				if ( in_array( $tokens[ $i ]['code'], array( T_DOUBLE_ARROW, T_CLOSE_PARENTHESIS, T_STRING_CONCAT ) ) ) {
					continue;
				}

				// Allow tokens that are implicitly slashed.
				if ( isset( $this->slashed_tokens[ $tokens[ $i ]['code'] ] ) ) {
					continue;
				}

				// If we were watching before, stop now. That way we'll error once
				// for a set of unslashed tokens, instead of for each of the tokens.
				$watch = false;

				// Allow int/float/bool casted variables.
				if ( isset( $this->slashed_casts[ $tokens[ $i ]['code'] ] ) ) {
					$in_cast = true;
					continue;
				}

				$content = $tokens[ $i ]['content'];

				// If this is a function call.
				if ( T_STRING === $tokens[ $i ]['code'] ) {

					// We can fast-forward to the end of this function call, we don't
					// need to check each token because we're going to check whether
					// the result of the function is slashed (below).
					$paren_opener = $phpcsFile->findNext( Tokens::$emptyTokens, $i + 1, null, true );

					if ( isset( $tokens[ $paren_opener ]['parenthesis_closer'] ) ) {
						$i = $tokens[ $paren_opener ]['parenthesis_closer'];
					}

					// If the function is a slashing function we continue to the next
					// token instead of giving an error.
					if (
						isset( self::$slashingFunctions[ $content ] )
						|| isset( self::$autoSlashingFunctions[ $content ] )
					) {
						continue;
					}

				} elseif ( T_VARIABLE === $tokens[ $i ]['code'] ) {
					if ( in_array( $content, $this->input_superglobals, true ) ) {
						continue;
					}
				}

				$this->addError(
					'%s() expects the value of the $%s arg to be slashed with wp_slash(); %s found.',
					$i,
					'MissingSlashing',
					array( $function_name, $name, $content )
				);
			}
		}

	} // End process_expected_slashed_args()

	/**
	 * Records an error against a specific token in the file being sniffed.
	 *
	 * @since ${PROJECT_VERSION}
	 *
	 * @param string  $error    The error message.
	 * @param int     $stackPtr The stack position where the error occurred.
	 * @param string  $code     A violation code unique to the sniff message.
	 * @param array   $data     Replacements for the error message.
	 */
	protected function addError( $error, $stackPtr, $code, $data ) {

		if ( $this->is_inside_slashed_function_definition( $stackPtr ) ) {
			$this->phpcsFile->addWarning( $error, $stackPtr, $code, $data );
		} else {
			$this->phpcsFile->addError( $error, $stackPtr, $code, $data );
		}
	}

	/**
	 * Determines whether or not a token is inside of a slashed function declaration.
	 *
	 * @since ${PROJECT_VERSION}
	 *
	 * @param int $stackPtr The position of the token in the stack.
	 *
	 * @return bool Whether the token is inside the definition of a slashed function.
	 */
	protected function is_inside_slashed_function_definition( $stackPtr ) {

		// Check if we are inside a function.
		$function_ptr = $this->phpcsFile->getCondition( $stackPtr, T_FUNCTION );

		if ( ! $function_ptr ) {
			return false;
		}

		$function_name = $this->phpcsFile->findNext(
			Tokens::$emptyTokens,
			$function_ptr + 1,
			null,
			true,
			null,
			true
		);

		$function_name = $this->tokens[ $function_name ]['content'];

		// If we are inside a function that expects slashed arguments we don't
		// really need to flag any internal function calls, because if any of
		// them expects slashed data but isn't receiving it, that is likely the
		// reason that the wrapping function is in this list.
		if (
			isset( self::$expectedSlashedFunctionArgs[ $function_name ] )
			|| isset( self::$partlySlashedFunctionArgs[ $function_name ] )
			|| isset( self::$mixedSlashedFunctionArgs[ $function_name ] )
		) {
			return true;
		}

		return false;
	}

} // End class.
