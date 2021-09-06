<?php
namespace madtec\db;

class Mssql
{
	private $mssql;
	
	public function __construct($host,$user,$pass,$bdd)
    {
		try
		{
			$this->mssql = new \PDO("dblib:host=".$host.";dbname=".$bdd."", $user, $pass,array(\PDO::ATTR_TIMEOUT => 10));
			$this->mssql->setAttribute(\PDO::SQLSRV_ATTR_ENCODING, \PDO::SQLSRV_ENCODING_UTF8);
		}
		catch(Exception $e){
			echo $e->getMessage() ;
		}
    }
	
			
	function concat($ch,$mot)
	{
		$sql_c = "(";
		for ($i = 0; $i < sizeof($ch); $i++)
		{
		if( $i > 0 ){$sql_c .= " + ";}
			$sql_c .= " ".$ch[$i];
			
		}
		$sql_c .= " ) ";
		

		$mot = strtoupper(trim($mot));
		$mot = str_replace("+"," ",$mot);
		$mot = str_replace("'","",$mot);
		while (strpos($mot,"  ")>0)
		{
			$mot = str_replace("  "," ",$mot);
		}
		

		$sql = str_replace(" ","%' AND ".$sql_c." LIKE '%",$this->encode($mot)."%'");
		
		$sql = $sql_c." LIKE '%".$sql;
		return $sql;

	}
	
	function cast($tbl,$ch)
	{
		$sql = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$this->esc($tbl)."' AND  COLUMN_NAME = '".$this->esc($ch)."'";
		$res = $this->res($sql);
		
		if($res=='int')
		{
			return "CAST(".$this->champ_format($ch)." AS VARCHAR(12))";
		}
		elseif($res=='nvarchar')
		{
			return "CAST(".$this->champ_format($ch)." AS TEXT)";
		}
	}	

	function sta($sql)
	{
		$sta = $this->mssql->prepare($sql);
		$sta->execute();
		$res = array();
		while($row = $sta->fetch()) {

			foreach ($row as $k => $v)
			{
				$row[$k] = utf8_encode($v);
				if(is_int($k)){unset($row[$k]);}
			}
			$res[] = $row;
		}

		return $res;
	}


	
	function esc($str)
	{
		if(get_magic_quotes_gpc())
		{
			$str = stripslashes($str);
		}
		return str_replace("'", "''", $str);
	}

	function decode($str)
	{
		if(get_magic_quotes_gpc())
		{
			$str = stripslashes($str);
		}
		return trim($str);
	}

	
	
	function champ_format($ch)
	{
		$ch = str_replace(array("[","]"),"",$ch);
		$ch = "[".trim($ch)."]";
		return $ch;
	}
	
	function table_format($ch)
	{
		$ch = str_replace(array("[dbo]."),"",$ch);
		$ch = str_replace(array("[","]"),"",$ch);
		$ch = "[dbo].[".trim($ch)."]";
		return $ch;
	}
	
	function val_format($ch)
	{
		if(substr($ch,0,1)=="'" && substr($ch,-1)=="'")
		{
			$ch = substr($ch,1,-1);
		}
		$ch = $this->esc($ch);
		$ch = "'".$ch."'";
		return $ch;
	}
	
	function sql_insert($champs,$table, $mode = "")
	{
		$sql = "INSERT INTO ".$this->table_format($table)." ";
		$sqlchamps = "";
		$sqlvaleurs = "";

		for ($i = 0; $i < sizeof($champs); $i++)
		{
			$sqla = "";
			if($i<sizeof($champs)&&$i>0)
			{
				$sqla = ", ";
			}
			
			$champ = $this->champ_format($champs[$i][0]);
			$val = $this->val_format($champs[$i][1]);
			
			$sqlchamps .= $sqla.$champ;
			$sqlvaleurs .= $sqla.$val;
		}
		$sql .= "(".$sqlchamps.") VALUES (".$sqlvaleurs.")";
		
		if($mode == "")
		{
			$req = $this->mssql->prepare($sql);
			$req->execute();
			$id = $this->mssql->lastInsertId();
			return ($id);
		}
		else
		{
			return $sql;
		}
	}
	
	function update($champs,$table,$where = " WHERE 1 = 2 ",$mode = "")
	{
		return $this->sql_update($champs,$table,$where,$mode);
	}

	function sql_update($champs,$table,$where = " WHERE 1 = 2 ",$mode = "")
	{

		$sql = "UPDATE ".$this->table_format($table)." SET ";

		for ($i=0;$i<sizeof($champs);$i++)
		{
			if($i>0)
			{
				$sql .= ", ";
			}
			$sql .= $this->champ_format($champs[$i][0])." = ".$this->val_format($champs[$i][1]);
		}
		
		if(substr($where,0,1) != " ")
		{
			$where = " ".$where;
		}
		
		$sql .= $where;
		
		if($mode == "")
		{
			$req = $this->mssql->prepare($sql);
			$req->execute();
		}
		else
		{
			return $sql;
		}
	}
	
	function req($sql)
	{
		$req = $this->mssql->prepare($sql);
		$req->execute();
	}
	
	function res($sql,$allch = "")
	{
		$req = $this->mssql->prepare($sql);
		$req->execute();
		$row = $req->fetch();
		if($allch=="")
		{
			return $row[0];
		}
		else
		{
			return $row;
		}
		
	}
	
	
	///// ANCIENNE function
	
	function mssql_insert($champs,$table, $mode = "")
	{
		return $this->sql_insert($champs,$table, $mode = "");
	}
	
	function mssql_req($sql)
	{
		return $this->req($sql);
	}
	
	function encode($str)
	{
		return $this->esc($str);
	}
	

}

	
	
	
?>
