<?php

class Help extends Notify {

  protected static $default_help_type = NULL;

	public static function msg($title, $text, $type = NULL, $session = FALSE)
	{
		// If we receive a message with no type
		if (is_null($type))
		{
			// If we haven't assigned a default message type
			if (is_null(self::$default_help_type))
			{
				// Get value from config file
				self::$default_help_type = trim(Kohana::$config->load('notify.help.default'));
			}
			// Assign value
			$type = self::$default_help_type;
		}
		else
		{
			$type = trim($type);
		}

		// See if we do not already have a key for that type of message
		// initialize the array
		if ( ! array_key_exists($type, self::$msgs))
		{
			self::$msgs[$type] = array();
		}

		$do_translate = Kohana::$config->load('notify.translate');

    // Force casting and sanitizing
    $title = trim($title);
    $text  = trim($text);

    if ($do_translate)
    {
      $title = __($title);
      $msg   = __($msg);
    }

    self::$msgs[$type][] = array(
      'title' => $title,
      'msg'   => $msg
    );

    // If we haven't assigned a value for $persistent_messages
    if (is_null(self::$persistent_messages))
    {
      // Get value from config file
      self::restore_persistent_messages();
    }

    // Check if message should be stored in session.
    if (self::$persistent_messages OR $session)
    {
      // Store the message in a session for later retrieval
      self::add_message_to_session($msg, $type);
    }

		// Make it chainable
		return self::return_instance();
	}

  public static function render($message_type = NULL)
	{
		// If view is not assigned, get from config file
		if (is_null(self::$view))
		{
			self::$view = Kohana::$config->load('notify.help.view');
		}

		// Merge session messages with normal ones
		self::merge_session_messages();

		// If it's valid $message_type received, we should only render the messages of that type
		if ( ! is_null($message_type) AND array_key_exists($message_type, self::$msgs))
		{
			$vars = array('msgs' => array($message_type => self::$msgs[$message_type]));
		}
		else
		{
			// Render all messages
			$vars = array('msgs' => self::$msgs);
		}

		// Render the view
		$messages =  View::factory(self::$view, $vars)->render();

		// Return the rendered messages
		return $messages;
	}

}