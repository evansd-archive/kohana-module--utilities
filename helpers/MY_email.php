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
		
		if (is_array($from) AND isset($from['from']))
		{
			foreach(array('reply-to' => 'setReplyTo', 'return-path' => 'setReturnPath') as $key => $method)
			{
				if(isset($from[$key]))
				{
					$address = is_array($from[$key]) ? $from[$key] : array($from[$key], NULL); 
					$message->$method(new Swift_Address($address[0], $address[1]));
				}
			}
			
			$from = $from['from'];
		}

		if (is_string($from))
		{
			// From without a name
			$from = new Swift_Address($from);
		}
		elseif (is_array($from))
		{
			// From with a name
			$from = new Swift_Address($from[0], $from[1]);
		}

		return email::$mail->send($message, $recipients, $from);
	}
}
