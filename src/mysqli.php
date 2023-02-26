<?php
namespace madtec\db;
class Mysqli
{
	private $wasmysqli;
	
	private $reqs = array();
	
	public function __construct($host,$user,$pass,$bdd)
    {
		mb_internal_encoding("UTF-8");
		$this->wasmysqli = mysqli_connect($host,$user,$pass);
		mysqli_select_db($this->wasmysqli, $bdd) or die ("no database"); 
		
		if (!$this->wasmysqli) {
		printf("Echec de la connexion : %s\n", mysqli_connect_error());
		exit();
		}
		mysqli_query($this->wasmysqli,"SET NAMES 'UTF8'");
    }
	
		
	function res($sql)
	{

		$result = mysqli_query($this->wasmysqli,$sql);
		$rs = mysqli_fetch_array( $result );
		if(empty($rs)){return "";}
		return($rs[0]);
	}

	function req($sql)
	{

		$result = mysqli_query($this->wasmysqli,$sql);
		return($result);
	}

	function req_mass($sqls)
	{

		for($i = 0; $i < sizeof($sqls); $i++)
		{
			mysqli_query($this->wasmysqli,$sqls[$i]);
		}
	}
	
	function sql_encode($var)
	{
		return $this->esc($var);
	}

	function esc($var)
	{
		if(empty($var)){return "";}
		$var = trim($var);
		$var = mysqli_real_escape_string($this->wasmysqli,$var);
		return $var;
	}
	
	

	function sta($sql)
	{
		$result = mysqli_query($this->wasmysqli,$sql);
		$fields = mysqli_fetch_fields($result);
		$res = array();
		$i = 0;
		while($rs = mysqli_fetch_array($result))
		{
			$champs = array();
			foreach ($fields as $val)
			{
				$champs[$val->name] = $rs[$val->name];
			}
			$res[$i] = $champs;
			$i++;
		}
		return $res;
	}


	function sql_insert($champs,$table, $mode = "")
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
			$sqlchamps .= $sqla."`".$champs[$i][0]."`";
			$sqlvaleurs .= $sqla."'".$this->sql_encode($champs[$i][1])."'";
			$sqla = "";
		}
		$sql .= "(".$sqlchamps.") VALUES (".$sqlvaleurs.")";
		
		if($mode=="")
		{
			mysqli_query($this->wasmysqli,$sql);
			return mysqli_insert_id($this->wasmysqli);
		}
		else if($mode=="multins")
		{
			return ", (".$sqlvaleurs.")";
		}
		else
		{
			return $sql;
		}
	}
	
	function inserts($ch,$val,$tbl)
	{
		$sql = "INSERT INTO ".$this->chformat($tbl)." ";
		
		$sql .= " (";
		for($i=0;$i<sizeof($ch);$i++)
		{
			$sql .= " ".$this->chformat($ch[$i]);
			
			if($i<sizeof($ch)-1)
			{
				$sql .= ",";
			}
		}
		$sql .= " ) VALUES ";
		
		for($i=0;$i<sizeof($val);$i++)
		{
			$sql .= "(";
			for($i2=0;$i2<sizeof($val[$i]);$i2++)
			{
				$sql .= "'".$this->esc($val[$i][$i2])."'";
				if($i2<sizeof($val[$i])-1)
				{
					$sql .= ",";
				}
			}
			$sql .= ")";
			if($i<sizeof($val)-1)
			{
				$sql .= ",";
			}
		}

		
		$this->req($sql);
	}
	
	function sql_multins($champs,$table,$cpt)
	{
		if($cpt==1)
		{
			return $this->sql_insert($champs,$table, "r");
		}
		else
		{
			return $this->sql_insert($champs,$table, "multins");
		}
		
	}
	

	function sql_concat($champs,$mot)
	{
		$sql_c = "";
		for ($i=0;$i<sizeof($champs);$i++)
		{
			if(substr($champs[$i],0,1)=='`')
			{
				$sql_c .= ", UPPER(".$champs[$i].")";
			}
			else
			{
				$sql_c .= ", UPPER(`".$champs[$i]."`)";
			}
			
		}
		$sql_c = "CONCAT_WS( ' '".$sql_c." )";
		

		$mot = str_replace("+"," ",$mot);
		$mot = str_replace("'","",$mot);
		while (strpos($mot,"  ")>0)
		{
			$mot = str_replace("  "," ",$mot);
		}
		$mot = strtoupper(trim($mot));

		$sql = str_replace(" ","%' AND ".$sql_c." LIKE '%",$this->sql_encode($mot)."%'");
		
		$sql = $sql_c." LIKE '%".$sql;

	return $sql;

	}

	function sql_update($champs,$table,$where = " WHERE 1 = 2 ",$mode = "exec")
	{

		$sql = "UPDATE ".$table." SET ";
		reset($champs);
		for ($i = 0; $i <= (sizeof($champs)-1); $i++)
		{
			$sqla = "";
			if($i<(sizeof($champs)-1))
			{
				$sqla = ", ";
			}
			$sql .= "`".$champs[$i][0]."` = '".$this->sql_encode($champs[$i][1])."'".$sqla;
			$sqla = "";
		}
		$sql .= $where;
		
		if($mode=="exec")
			{mysqli_query($this->wasmysqli,$sql);}
		else
			{return $sql;}
	}
	
	function chformat($ch)
	{
		$ch = str_replace(array("`"),"",$ch);
		$ch = "`".trim($ch)."`";
		return $ch;
	}

}

	
	
	
?>
