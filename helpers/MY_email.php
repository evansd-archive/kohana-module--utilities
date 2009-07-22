<?php
class email extends email_Core
{
	/**
	 * Send an email message.
	 *
	 * @param   string|array  recipient email (and name), or an array of To, Cc, Bcc names
	 * @param   string|array  sender email (and name), or an array of From, Reply-To, Return-Path names
	 * @param   string        message subject
	 * @param   string        message body
	 * @param   boolean       send email as HTML
	 * @return  integer       number of emails sent
	 */
	public static function send($to, $from, $subject, $message, $html = FALSE)
	{
		// Connect to SwiftMailer
		(email::$mail === NULL) and email::connect();

		// Determine the message type
		$html = ($html === TRUE) ? 'text/html' : 'text/plain';

		// Create the message
		$message = new Swift_Message($subject, $message, $html, '8bit', 'utf-8');

		if (is_string($to))
		{
			// Single recipient
			$recipients = new Swift_Address($to);
		}
		elseif (is_array($to))
		{
			if (isset($to[0]) AND isset($to[1]))
			{
				// Create To: address set
				$to = array('to' => $to);
			}

			// Create a list of recipients
			$recipients = new Swift_RecipientList;

			foreach ($to as $method => $set)
			{
				if ( ! in_array($method, array('to', 'cc', 'bcc')))
				{
					// Use To: by default
					$method = 'to';
				}

				// Create method name
				$method = 'add'.ucfirst($method);

				if (is_array($set))
				{
					// Add a recipient with name
					$recipients->$method($set[0], $set[1]);
				}
				else
				{
					// Add a recipient without name
					$recipients->$method($set);
				}
			}
		}

		if (is_string($from))
		{
			// From without a name
			$from = new Swift_Address($from);
		}
		elseif (is_array($from))
		{
			if (isset($from[0]) AND isset($from[1]))
			{
				// Create From: address set
				$from = array('from' => $from);
			}
			
			foreach ($from as $method => $set)
			{
				// Create method name
				$method = 'set'.ucwords(str_replace('-', ' ', $method));
				
				if(in_array($method, array('setFrom', 'setReplyTo', 'setReturnPath')))
				{
					if (is_array($set))
					{
						// Add a recipient with name
						$message->$method(new Swift_Address($set[0], $set[1]));
					}
					else
					{
						// Add a recipient without name
						$message->$method(new Swift_Address($set));
					}
				}
			}
		}

		return email::$mail->send($message, $recipients, $from);
	}
}
