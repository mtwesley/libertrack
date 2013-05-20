<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * ACL library
 *
 * @package    ACL
 * @author     Synapse Studios
 * @author     Jeremy Lindblom <jeremy@synapsestudios.com>
 * @copyright  (c) 2010 Synapse Studios
 */
class Synapse_ACL {

	/**
	 * @var  array  contains the instances (by request) of ACL
	 */
	protected static $_instances = array();

	/**
	 * @var  array  contains all the ACL rules
	 */
	protected static $_rules = array();

	/**
	 * @var  array  An array containing all valid items
	 */
	public static $valid = NULL;

	/**
	 * Creates/Retrieves an instance of ACL based on a request. The request can
	 * be passed in a variety of forms for convenience, it can be: a `Request`
	 * object, an array of requests parts (directory, controller, action), a
	 * URI, or NULL (which will resolve to the current request).
	 *
	 * @param   mixed  Request object, array of request parts, or uri string
	 * @return  ACL
	 */
	public static function instance($request = NULL)
	{
		// Initialize the $valid array
		self::_initialize_valid_items();

		// Determine the request object, if one was not provided
		if (is_string($request))
		{
			// Get the request by URI
			$request = Request::factory($request);
		}
		elseif ($request === NULL)
		{
			// Use the current request
			$request = Request::current() ?: Request::initial();
		}

		// Get the request parts array (directory, controller, action)
		if ($request instanceof Request)
		{
			$request_parts = array
			(
				'directory'  => $request->directory(),
				'controller' => $request->controller(),
				'action'     => $request->action(),
			);
		}
		elseif (is_array($request))
		{
			$request_parts = Arr::extract($request, array('directory', 'controller', 'action'));
		}
		else
		{
			throw new Synapse_ACL_Exception('Could not determine the request from the provided parameter.');
		}

		// Use the imploded request parts as the key for this instance
		$key = implode('/', $request_parts);

		// Register the instance if it doesn't exist
		if ( ! isset(self::$_instances[$key]))
		{
			self::$_instances[$key] = new ACL($request_parts);
		}

		return self::$_instances[$key];
	}

	/**
	 * Factory for an ACL rule. Stores it in the rules array, automatically.
	 *
	 * @return  ACL_Rule
	 */
	public static function rule(ACL_Rule $rule = NULL)
	{
		// Initialize the $valid array
		self::_initialize_valid_items();

		// If no rule provided, use a new, blank one
		if ($rule === NULL)
		{
			$rule = new ACL_Rule;
		}

		// Return the rule after storing in the rules array
		return self::$_rules[] = $rule;
	}

	/**
	 * Remove all previously-added rules
	 *
	 * @return  void
	 */
	public static function clear()
	{
		// Remove all rules
		self::$_rules = array();
	}

	/**
	 * Initializes the `$valid` arrays for roles and capabilities.
	 *
	 * @return  void
	 */
	protected static function _initialize_valid_items()
	{
		// Get list of all valid items
		if (self::$valid === NULL)
		{
			// Setup the array
			self::$valid = array();

			// Get the valid roles
			self::$valid['roles'] = array();

			if ($public_role = Kohana::$config->load('acl.public_role'))
			{
				self::$valid['roles'][] = $public_role;
			}

			foreach (ORM::factory('role')->find_all() as $role)
			{
				self::$valid['roles'][] = $role->name;
			}

			// Get the valid capabilities
			if (Kohana::$config->load('acl.support_capabilities'))
			{
				self::$valid['capabilities'] = array();
				foreach (ORM::factory('capability')->find_all() as $capability)
				{
					self::$valid['capabilities'][] = $capability->name;
				}
			}
		}
	}

	/**
	 * @var  array  The request object to which this instance of ACL is for
	 */
	protected $_parts = NULL;

	/**
	 * @var  Model_User  The current use as retreived by the Auth module
	 */
	protected $_user  = NULL;

	/**
	 * Constructs a new ACL object for a request
	 *
	 * @param   array  The request parts
	 * @return  void
	 */
	protected function __construct(array $parts)
	{
		// Store the request for this instance
		$this->_parts = $parts;

		// Get the user (via Auth)
		$this->_user = Auth::instance()->get_user() ?: ORM::factory('user');
	}

	/**
	 * Check if a user is allowed to the request based on the ACL rules
	 *
	 *     $uri_parts = array('controller' => 'account', 'action' => 'update');
	 *     $allowed   = ACL::instance($uri_parts)->allows_user($user);
	 *
	 * @param   Model_User  The user to allow
	 * @return  boolean
	 */
	public function allows_user(Model_User $user = NULL)
	{
		// Use the object's user, unless another is provided
		$user = $user ?: $this->_user;

		// Compile the rules
		$rule = $this->_compile_rules();

		// Check if this user has access to this request
		return $rule->allows_user($user);
	}

	/**
	 * This is the procedural method that executes ACL logic and responses
	 *
	 * @return  boolean
	 * @throws  Kohana_Request_Exception
	 * @uses    ACL::_compile_rules
	 * @uses    ACL::verify_request
	 */
	public function authorize()
	{
		// Current request or initial request if this hasn't happened yet
		$request = Request::current() ?:  Request::initial();

		if ( ! empty(self::$_rules))
		{
			// Compile the rules
			$rule = $this->_compile_rules();

			// Check if this user has access to this request
			if ($rule->allows_user($this->_user))
				return TRUE;
		}

		// Set the HTTP status to 403 - Access Denied
		$request->status = 403;

		// Execute the callback (if any) from the compiled rule
		$rule->perform_callback($this->_user);

		// Throw an exception (403) if no callback has altered program flow
		throw new Kohana_Request_Exception('You are not authorized to access this resource.', NULL, 403);
	}

	/**
	 * Compliles the rule from all applicable rules to this request
	 *
	 * @return  ACL_Rule  The compiled rule
	 */
	protected function _compile_rules()
	{
		// Resolve and separate multi-action rules
		$resolved_rules = array();
		foreach (self::$_rules as $rule)
		{
			$resolved_rules = array_merge($resolved_rules, $rule->resolve($this->_parts));
		}

		// Create a blank, base rule to compile down to
		$compiled_rule = new ACL_Rule;

		// Merge rules together that apply to this request
		foreach ($resolved_rules as $rule)
		{
			if ($rule->valid() AND $rule->applies_to($this->_parts))
			{
				$compiled_rule = $compiled_rule->merge($rule);
			}
		}

		return $compiled_rule;
	}

} // End ACL
