<?php 

namespace Core\Crud;

use \Core\Connection;
use \Exception;
use \Core\Trace\Helper;

class Update{
	use Helper;
	
	private $tbname;

	private $query;

	private $binds = [];

	private $active = ['id' => false, 'wh' => false, 'andwh' => false, 'orwh' => false];

	public function __construct($table = 'undefined'){
		$this->tbname = $table;
		$this->query = "UPDATE {$table}";
		return $this;
	}

	public function Set($datas = [], $run = false){
		$cols = implode('=?,',array_keys($datas)).'=?';
		$vals = array_values($datas);
		$this->binds = $vals;
		$this->query .= " SET {$cols}";
		
		return (true === $run) ? $this->Run() : $this;
	}

	public function Id($id = null, $colname = 'id', $run = false){
		if(!is_null($id)){
			$this->query .= " WHERE {$colname} = ?";
			$this->active['id'] = true;
			array_push($this->binds, $id);
		}
		
		return (true === $run) ? $this->Run() : $this;
	}

	public function Wh($field = null, $val = null, $run = false){
		if(!is_null($field) && !is_null($val)){
			if($this->active['id'] === false){
				$field_operation = $this->WhatOperation($field);
				var_dump($field_operation);
				$this->query .= " WHERE {$field_operation} ?";
				$this->active['wh'] = true;
				array_push($this->binds, $val);
			}
		}

		return (true === $run) ? $this->Run() : $this;
	}

	public function Andwh($field = null, $val = null, $run = false){
		if(!is_null($field) && !is_null($val)){
			if($this->active['wh'] === true OR $this->active['id'] === true){
				$field_operation = $this->whereOperation($field);
				$this->query .= " AND {$field_operation} ?";
				$this->active['andwh'] = true;
				array_push($this->binds, $val);
			}
		}

		return (true === $run) ? $this->Run() : $this;
	}

	public function Orwh($field = null, $val = null, $run = false){
		if(!is_null($field) && !is_null($val)){
			if($this->active['wh'] === true OR $this->active['id'] === true){
				$field_operation = $this->whereOperation($field);
				$this->query .= " OR {$field_operation} ?";
				$this->active['orwh'] = true;
				array_push($this->binds, $val);
			}
		}

		return (true === $run) ? $this->Run() : $this;
	}

	public function Run(){
		try {
			$run = Connection::Prepare($this->query);
			if(!empty($this->binds)){
				foreach($this->binds as $k => $b){
					$run->bindValue($k+1, $b);
				}
			}
			$run->execute();
			return $run;	
		} catch (\Exception $e) {
			$error = "<br>Não foi possivel executar a query <em><strong>{$this->query}</strong></em>.<br><br> <strong>PDO Message: </strong>{$e->getMessage()}";
			die($error);
		}
	}
}