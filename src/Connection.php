<?php namespace Smtp;

/**
 * Class SmtpConnection
 *
 * @author tmaxham
 * @generated 23.02.15
 * @version 23.02.15
 */
class Connection {

	private $parsedURL, $host, $user, $pass, $conn, $path;

	private $port = 143;

	public $inbox = [];

	private $msg_cnt = 0;

	public function __construct($anURL)
	{
		$this->parsedURL = parse_url($anURL);
		$this->prepareConnection();
		$this->connect();
		$this->inbox();
	}

	/**
	 * Close the server connection.
	 */
	public function close()
	{
		$this->inbox = array();
		$this->msg_cnt = 0;
		imap_expunge($this->conn);
		imap_close($this->conn);
	}

	/**
	 * Open the server connection. The imap_open function parameters will need
	 * to be changed for the particular server.
	 */
	private function connect()
	{
		$server = '{'.$this->host;
		$server = $this->port? $server.':'.$this->port:$server;
		$server = $this->path? $server.$this->path:$server;
		$this->conn = imap_open($server.'}', $this->user, $this->pass);
	}

	public function delete($id, $hard)
	{
		imap_delete($this->conn, $id);
		if($hard) imap_expunge($this->conn);
		unset($this->inbox[$id]);
		return TRUE;
	}

	/**
	 * Read the inbox.
	 */
	private function inbox()
	{
		$this->msg_cnt = imap_num_msg($this->conn);
		$in = array();

		for($i = 1; $i <= $this->msg_cnt; $i++) {

			$in[$i] = new Message($this, [
				'index'     => $i,
				'header'    => imap_headerinfo($this->conn, $i),
				'body'      => imap_body($this->conn, $i),
				'rbody'		=> imap_fetchbody($this->conn, $i, 1.1),
				'structure' => imap_fetchstructure($this->conn, $i),
			], $i);
		}
		$this->inbox = $in;
	}

	public function msg($id=NULL)
	{
		if(!is_int($id)) return $this->inbox;
		if(isset($this->inbox[$id])) return $this->inbox[$id];
		else return NULL;
	}

	private function prepareConnection()
	{
		foreach(['host', 'user', 'pass'] as $type)
			if(!$this->setConnectionType($type)) return FALSE;
		$this->setConnectionType('port');
		$this->setConnectionType('path');
		return TRUE;
	}

	protected function setConnectionType($type)
	{
		if(!isset($this->parsedURL[$type])) return FALSE;
		else $this->$type = $this->parsedURL[$type];
		return TRUE;
	}

} 