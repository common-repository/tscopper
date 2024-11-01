<?php

class tsdb {
  var $tsdbh;

  function tsdb ($user, $password, $dbname, $dbhost) {
    $this->tsdbh = new mysqli($dbhost, $user, $password, $dbname);
    if ($this->tsdbh->connect_error) {
      //echo 'db connection failed for tscopper';
      //echo $this->tsdbh->connect_errno;
      wp_die(__('db connection failed for tscopper'));
    }
  }
  
  function query ($query, array $params = NULL) {
    if (isset($params)) {
      $types = $this->fieldTypesOfArray($params);
      return $this->queryWithType($query, $types, $params);
    } else {
      return $this->queryWithType($query);
    }
  }
  
  function queryWithType ($query, $types = NULL, array $params = NULL) 
  {
    $statement = $this->tsdbh->prepare($query);
    if (!$statement) {
     //echo 'SQL QUERY FAIL';
     //echo $this->tsdbh->error;
     // wp_die(__('SQL Query failed (for tscopper)'));
     wp_die(__($this->tsdbh->error));
    }
    if (count($params) > 0)
      call_user_func_array(array($statement,'bind_param'), array_merge(array($types), $params));
      
    $metadata = $statement->result_metadata(); 
    $fields = $metadata->fetch_fields(); 
    
    
    foreach ($fields as $field) {
      $var = $field->name;
      $$var = NULL;
      $row[$var] = &$$var;
      $list[$field->name] = NULL;
    }
   
    $statement->execute();

   call_user_func_array(array($statement,'bind_result'), $row);
    while($statement->fetch()) {

      $new_row = array();
      foreach ($row as $key => $value) {     
       $new_row[$key] = $value;
      }
      $rows[] = $new_row;
    }
    return $rows;
  }
  
  function fieldTypesOfArray (array $fields) 
  {
    $type= '';
    foreach ($fields as $field) {
      switch(gettype($field)) {
        case "boolean":
          $type .= 's';
          break;
        case "integer":
          $type .= 'i';
          break;
        case "double":
          $type .= 'd';
          break;
        case "string":
          $type .= 's';
          break;
        case "NULL":
          $type .= 's';
          break;
        // unsupported
        case "array":
        case "object":
        case "resource":
        case "unknown type":
          $type .= 's';
          break;          
      }
    }
    return $type;
  }
  function tsdb_close()
  {
    $this->tsdbh->close();
  }
  

}

?>
