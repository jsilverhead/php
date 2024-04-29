<?php

class User
{
	protected string $name;
	protected int $id;
	protected string $email;
	protected string $password;

	public function __construct($name, $id, $email, $password)
	{
		$this->name = $name;
		$this->id = $id;
		$this->email = $email;
		$this->password = $password;
	}

	public function getInfo()
	{
		$info = "name: $this->name, id: $this->id, email: $this->email, password: $this->password";

		return $info;
	}
}

class Admin extends User
{
	protected string $type;

	public function __construct($name, $id, $email, $password, $type)
	{
		parent::__construct($name, $id, $email, $password);
		$this->type = $type;
	}

	public function getInfo()
	{
		$info = parent::getInfo();
		$info .= ", type: $this->type";
	}
}

$administrator = new Admin($name = "John", $id = 22, $email = "lovedogs@gmail.com", $password = "12345678", $type = "admin");

var_dump($administrator);