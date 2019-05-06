<?php
namespace madtec\db;
class Mssql
{
	private $mssql;
	
	public function __construct($host,$user,$pass,$bdd)
    {
		try
		{
			$this->mssql = new PDO("sqlsrv:Server=".$host.";Database=".$bdd."", $user, $pass);
			$this->mssql->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_UTF8);
		}
		catch(Exception $e){
			echo $e->getMessage() ;
		}
    }
	
			
	function mssql_concat($ch,$mot)
	{
		$sql_c = " ' ' ";
		for ($i = 0; $i <= (sizeof($ch)-1); $i++)
		{
			$sql_c .= " + ' ' + ".$ch[$i]." ";
		}
		$sql_c = "  ".$sql_c." ";
		

		$mot = strtoupper(trim($mot));
		$mot = str_replace("+"," ",$mot);
		$mot = str_replace("'","",$mot);
		while (strpos($mot,"  ")>0)
		{
			$mot = str_replace("  "," ",$mot);
		}
		

		$sql = str_replace(" ","%' AND ".$sql_c." LIKE '%",$this->mssql_encode($mot)."%'");
		
		$sql = $sql_c." LIKE '%".$sql;
		return $sql;

	}


	function mssql_to_array($sql)
	{

		$sta = $this->mssql->prepare($sql);
		$sta->execute();
		$res = array();
		while($row = $sta->fetch()) {
			$champs = array();
			while (list($key, $val) = each($row)) {
				//echo "$key => $val\n";
				$row[$key] = utf8_encode($val);
				if(is_int($key)){unset($row[$key]);}
			}
			$res[] = $row;
		}

		return $res;
	}

	function msta($sql)
	{
		return $this->mssql_to_array($sql);
	}

	function mssta($sql)
	{
		return $this->mssql_to_array($sql);
	}

	function mssql_encode($str)
	{
		if(get_magic_quotes_gpc())
		{
			$str = stripslashes($str);
		}
		return str_replace("'", "''", $str);
	}

	function mssql_decode($str)
	{
		if(get_magic_quotes_gpc())
		{
			$str = stripslashes($str);
		}
		return trim($str);
	}

	function mssql_insert($champs,$table, $mode = "")
	{
		$sql = "INSERT INTO ".$table." ";
		$sqlchamps = "";
		$sqlvaleurs = "";

		reset($champs);
		for ($i = 0; $i < sizeof($champs); $i++)
		{
			$sqla = "";
			if($i<sizeof($champs)&&$i>0)
			{
				$sqla = ", ";
			}
			$sqlchamps .= $sqla.$champs[$i][0];
			$sqlvaleurs .= $sqla.$champs[$i][1];
			$sqla = "";
		}
		$sql .= "(".$sqlchamps.") VALUES (".$sqlvaleurs.")";
		
		if($mode=="")
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

	function mssql_req($sql)
	{
		$req = $this->mssql->prepare($sql);
		$req->execute();
		
	}

}

	
	
	
?>
