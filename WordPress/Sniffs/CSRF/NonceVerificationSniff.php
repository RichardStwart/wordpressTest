<?php

/**
 * WordPress_Sniffs_CSRF_NonceVerificationSniff.
 *
 * PHP version 5
 *
 * @since 0.4.0
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 */

/**
 * Checks that nonce verification accompanies form processing.
 *
 * @since 0.4.0
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @author   J.D. Grimes <jdg@codesymphony.co>
 * @link     https://developer.wordpress.org/plugins/security/nonces/ Nonces on Plugin Developer Handbook
 */
class WordPress_Sniffs_CSRF_NonceVerificationSniff implements PHP_CodeSniffer_Sniff {

	/**
	 * Superglobals to give an error for when not accompanied by an nonce check.
	 *
	 * @since 0.4.0
	 *
	 * @var array
	 */
	public $errorForSuperGlobals = array( '$_POST' );

	/**
	 * Superglobals to give a warning for when not accompanied by an nonce check.
	 *
	 * If the variable is also in the error list, that takes precedence.
	 *
	 * @since 0.4.0
	 *
	 * @var array
	 */
	public $warnForSuperGlobals = array( '$_GET', '$_REQUEST' );

	/**
	 * Custom list of functions which verify nonces.
	 *
	 * @since 0.4.0
	 *
	 * @var array
	 */
	public $customNonceVerificationFunctions = array();

	/**
	 * List of the functions which verify nonces.
	 *
	 * @since 0.4.0
	 *
	 * @var array
	 */
	public static $nonceVerificationFunctions = array(
		'wp_verify_nonce',
		'check_admin_referer',
		'check_ajax_referer',
	);

	/**
	 * Whether the custom functions have been added to the default list yet.
	 *
	 * @since 0.4.0
	 *
	 * @var bool
	 */
	public static $addedCustomFunctions = false;

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {

		return array(
			T_VARIABLE,
		);
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int                  $stackPtr  The position of the current token
	 *                                        in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process( PHP_CodeSniffer_File $phpcsFile, $stackPtr ) {

		// Merge any custom functions with the defaults, if we haven't already.
		if ( ! self::$addedCustomFunctions ) {
			self::$nonceVerificationFunctions = array_merge(
				self::$nonceVerificationFunctions
				, $this->customNonceVerificationFunctions
			);

			self::$addedCustomFunctions = true;
		}

		$tokens = $phpcsFile->getTokens();
		$instance = $tokens[ $stackPtr ];

		$superglobals = array_merge(
			$this->errorForSuperGlobals
			, $this->warnForSuperGlobals
		);

		if ( ! in_array( $instance['content'], $superglobals ) ) {
			return;
		}

		if ( $this->is_assignment( $stackPtr, $phpcsFile ) ) {
			return;
		}

		if ( $this->has_nonce_check( $stackPtr, $phpcsFile ) ) {
			return;
		}

		// If we're still here, no nonce-verification function was found.
		$severity = ( in_array( $instance['content'], $this->errorForSuperGlobals ) ) ? 0 : 'warning';

		$phpcsFile->addError(
			'Processing form data without nonce verification.'
			, $stackPtr
			, 'NoNonceVerification'
			, array()
			, $severity
		);

	} // end process()

	/**
	 * Check if this token has an associated nonce check.
	 *
	 * @param int                  $stackPtr  The position of the current token in
	 *                                        the stack of tokens.
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 *
	 * @return bool
	 */
	protected function has_nonce_check( $stackPtr, $phpcsFile ) {

		/**
		 * @var array {
		 *      A cache of the scope that we last checked for nonce verification in.
		 *
		 *      @var string $file  The name of the file.
		 *      @var int    $start The index of the token where the scope started.
		 *      @var int    $end   The index of the token where the scope ended.
		 *      @var bool|int $nonce_check The index of the token where an nonce
		 *                         check was found, or false if none was found.
		 * }
		 */
		static $last;

		$start = 0;
		$end = $stackPtr;

		$tokens = $phpcsFile->getTokens();

		// If we're in a function, only look inside of it.
		if ( $phpcsFile->hasCondition( $stackPtr, T_FUNCTION ) ) {
			$f = $phpcsFile->findPrevious( T_FUNCTION, $stackPtr );
			$start = $tokens[ $f ]['scope_opener'];
		}

		// We allow for isset( $_POST['var'] ) checks to come before the nonce check.
		// If this is inside an isset(), check after it as well, all the way to the
		// end of the scope.
		if ( $this->is_in_isset_or_empty( $stackPtr, $phpcsFile ) ) {
			$end = ( 0 === $start ) ? count( $tokens ) : $tokens[ $start ]['scope_closer'];
		}

		// Check if we've looked here before.
		$filename = $phpcsFile->getFilename();

		if (
			$filename === $last['file']
			&& $start === $last['start']
		) {

			// If we have already found an nonce check in this scope, we just need to
			// check whether it comes before or after this token. If not, we can
			// still go ahead and return false if we've already checked to the end of
			// the search area.
			if ( false !== $last['nonce_check'] ) {
				return ( $last['nonce_check'] < $stackPtr );
			} elseif ( $end <= $last['end'] ) {
				return false;
			}

			// We haven't checked this far yet, but we can still save work by
			// skipping over the part we've already checked.
			$start = $last['end'];
		} else {
			$last = array(
				'file'  => $filename,
				'start' => $start,
				'end'   => $end,
			);
		}

		// Loop through the tokens looking for nonce verification functions.
		for ( $i = $start; $i < $end; $i++ ) {

			// If this isn't a function name, skip it.
			if ( T_STRING !== $tokens[ $i ]['code'] ) {
				continue;
			}

			// If this is one of the nonce verification functions, we can bail out.
			if ( in_array( $tokens[ $i ]['content'], self::$nonceVerificationFunctions ) ) {
				$last['nonce_check'] = $i;
				return true;
			}
		}

		// We're still here, so no luck.
		$last['nonce_check'] = false;

		return false;
	}

	/**
	 * Check if a token is inside of an isset() or empty() statement.
	 *
	 * @since 0.4.0
	 *
	 * @param int $stackPtr                   The index of the token in the stack.
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 *
	 * @return bool Whether the token is inside an isset() or empty() statement.
	 */
	protected function is_in_isset_or_empty( $stackPtr, $phpcsFile ) {

		$tokens = $phpcsFile->getTokens();

		if ( ! isset( $tokens[ $stackPtr ]['nested_parenthesis'] ) ) {
			return false;
		}

		end( $tokens[ $stackPtr ]['nested_parenthesis'] );
		$open_parenthesis = key( $tokens[ $stackPtr ]['nested_parenthesis'] );
		reset( $tokens[ $stackPtr ]['nested_parenthesis'] );

		return in_array( $tokens[ $open_parenthesis - 1 ]['code'], array( T_ISSET, T_EMPTY ) );
	}

	/**
	 * Check if this variable is being assigned a value.
	 *
	 * E.g., $var = 'foo';
	 *
	 * Also handles array assignments to arbitrary depth:
	 *
	 * $array['key'][ $foo ][ something() ] = $bar;
	 *
	 * @since 0.4.0
	 *
	 * @param int $stackPtr                   The index of the token in the stack.
	 *                                        This must points to either a T_VARIABLE
	 *                                        or T_CLOSE_SQUARE_BRACKET token.
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 *
	 * @return bool Whether the token is a variable being assigned a value.
	 */
	protected function is_assignment( $stackPtr, $phpcsFile ) {

		$tokens = $phpcsFile->getTokens();

		// Must be a variable or closing square bracket (see below).
		if ( ! in_array( $tokens[ $stackPtr ]['code'], array( T_VARIABLE, T_CLOSE_SQUARE_BRACKET ) ) ) {
			return false;
		}

		$next_non_empty = $phpcsFile->findNext(
			PHP_CodeSniffer_Tokens::$emptyTokens
			, $stackPtr + 1
			, null
			, true
			, null
			, true
		);

		// No token found.
		if ( false === $next_non_empty ) {
			return false;
		}

		// If the next token is an assignment, that's all we need to know.
		if ( in_array( $tokens[ $next_non_empty ]['code'], PHP_CodeSniffer_Tokens::$assignmentTokens ) ) {
			return true;
		}

		// Check if this is an array assignment, e.g., $var['key'] = 'val';
		if ( T_OPEN_SQUARE_BRACKET === $tokens[ $next_non_empty ]['code'] ) {
			return $this->is_assignment( $tokens[ $next_non_empty ]['bracket_closer'], $phpcsFile );
		}

		return false;
	}

} // end class
