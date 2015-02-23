<?php namespace Smtp;

/**
 * Class Message
 *
 * @author tmaxham
 * @generated 23.02.15
 * @version 23.02.15
 */
class Message {

	private $conn, $data, $id;

	public function __construct(Connection $connection, $data, $id)
	{
		$this->conn = $connection;
		$this->data = $data;
		$this->id = $id;
	}

	/**
	 * @return Connection
	 */
	public function conn()
	{
		return $this->conn;
	}

	public function date($format = 'm.d.Y H:i')
	{
		return date($format, strtotime($this->data['header']->date));
	}

	public function delete($hard = FALSE)
	{
		$this->conn()->delete($this->id, $hard);
	}

	public function from()
	{
		return $this->mailFor('from');
	}

	private function mailFor($key)
	{
		if(isset($this->data['header']->$key)) {
			$full = $this->data['header']->$key;
			if(isset($full[0])) {
				$full = $full[0]->mailbox . '@' . $full[0]->host;
			} else $full = '';
		} else $full = '';
		return $full;
	}

	public function message()
	{
		return  utf8_encode(nl2br(quoted_printable_decode($this->data['rbody'])));
	}

	public function oddEven($attr = FALSE)
	{
		var_dump($this->id);
		if($attr && $this->id%2==0) return $attr;
		return $this->id%2==0;
	}

	public function subject()
	{
		return $this->data['header']->subject;
	}

	public function to()
	{
		return $this->mailFor('to');
	}

} 