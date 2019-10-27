<?php

   // Define database connection parameters
   $hn      = 'localhost';
   $un      = 'idlanzik_attend';
   $pwd     = '#IdlanZikri#5';
   $db      = 'idlanzik_liqaAttendance';
   $cs      = 'utf8';

   // Set up the PDO parameters
   $dsn   = "mysql:host=" . $hn . ";port=3306;dbname=" . $db . ";charset=" . $cs;
   $opt   = array(
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                       );
   // Create a PDO instance (connect to the database)
   $pdo  = new PDO($dsn, $un, $pwd, $opt);


   // Retrieve the posted data
   $json    =  file_get_contents('php://input');
   $obj     =  json_decode($json);
   $key     =  strip_tags($obj->key);

switch($key){


//LOGIN BY RUASA
    case 'rlogin'   :
        $matric           = filter_var($obj->matric, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_LOW);
        $password         = filter_var($obj->password, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_LOW);

            // Attempt to query database table and retrieve data
       try {
        $sql = "SELECT * FROM ruasa WHERE rua_matric = :matric";
        $stmt  = $pdo->prepare($sql);
        $stmt->bindParam(':matric', $matric, PDO::PARAM_STR);
        //run the query
        $stmt->execute();
        // fetch the information based on the query ran.
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        //check if the user existed.
        if($user && $password==$user['rua_password']){
          echo json_encode(array('username' => $user['rua_name'], 'matric' => $user['rua_matric']));
        }else{
          echo json_encode(array('message' => 'No User Found', 'username' => null));
        }
      }
      //show error if the query is not success
      catch(PDOException $e)
      {
      echo $e->getMessage();
      }
    break;


//LOGIN BY MARUS
    case 'mlogin'   :
        $matric            = filter_var($obj->matric, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_LOW);
        $password         = filter_var($obj->password, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_LOW);

        // Attempt to query database table and retrieve data
     try {
      $sql = "SELECT * FROM marus WHERE mar_matric = :matric";
      $stmt  = $pdo->prepare($sql);
      $stmt->bindParam(':matric', $matric, PDO::PARAM_STR);
      //$stmt->bindParam(':password', $password, PDO::PARAM_STR);
      $stmt->execute();

      $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user && $password == $user['mar_password']){
          //verified
          echo json_encode(array('message' => 'You are logged in', 'username' => $user['mar_name'], 'matric' => $user['mar_matric']));
        }else{
          //user not found
          echo json_encode(array('message' => 'No User found', 'username' => null));
        }
      }
      // Catch any errors in running the prepared statement
      catch(PDOException $e)
      {
      echo $e->getMessage();
      }
    break;


//SESSION CREATED BY RUASA
  case 'rsession'   :
        $topic        = filter_var($obj->topic, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_LOW);
        $date         = filter_var($obj->date, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_LOW);
        $time         = filter_var($obj->time, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_LOW);
        $venue        = filter_var($obj->venue, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_LOW);

        // Attempt to query database table and retrieve data
   try {
    $sql = "INSERT INTO session (session_topic, session_date, session_time, session_venue) VALUES (:topic, :date, :time, :venue)";
    $stmt  = $pdo->prepare($sql);
    $stmt->bindParam(':topic', $topic, PDO::PARAM_STR);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->bindParam(':time', $time, PDO::PARAM_STR);
    $stmt->bindParam(':venue', $venue, PDO::PARAM_STR);
    $stmt->execute();

    echo json_encode(array('message' => 'Session Created'));
  }
  // Catch any errors in running the prepared statement
  catch(PDOException $e)
  {
  echo $e->getMessage();
  }
  break;


//LIST OF THE SESSION CREATED BY RUASA
  case 'getSession'   :
    // Attempt to query database table and retrieve data
    try {
      $stmt = $pdo->query("SELECT * FROM session WHERE session_date > NOW()");
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $data[] = $row;
      }

      //return data as JSON
      echo json_encode($data);
      
    }
    // Catch any errors in running the prepared statement
    catch(PDOException $e)
    {
    echo $e->getMessage();
    }
    break;



//LOAD LIQA GROUP
  case 'loadLiqagroup'   :
    // Attempt to query database table and retrieve data
    try {
        $sql = 'SELECT liqagroup.*, ruasa.rua_name FROM liqagroup INNER JOIN ruasa ON liqagroup.group_ruasa = ruasa.rua_matric';
        $stmt  = $pdo->prepare($sql);
        //$stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->execute();
        while($row  = $stmt->fetch(PDO::FETCH_OBJ))
        {
        // Assign each row of data to associative array
        $data[] = $row;
        }

        // Return data as JSON
        echo json_encode($data);
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
        }
  break;







  //ENROLL LIQA GROUP
  case 'enroll':
    $group_code        = filter_var($obj->group_code, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_LOW);
    $matric = filter_var($obj->matric, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_LOW);

    try {
        $sql = 'INSERT INTO enroll (enrol_mar, enrol_liqa) VALUES (:matric, :group_code)';
        $stmt  = $pdo->prepare($sql);
        $stmt->bindParam(':group_code', $group_code, PDO::PARAM_STR);
        $stmt->bindParam(':matric', $matric, PDO::PARAM_STR);
        $stmt->execute();

        echo json_encode(array('message' => 'You are Enrolled'));
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
        }
    break;



  //RUASA : VIEW SELF-PROFILE
  case 'getSelfProfile'   :
    // Attempt to query database table and retrieve data
    try {
      $stmt = $pdo->query("SELECT rua_matric,rua_name,rua_ic,rua_faculty,rua_email,rua_phone,rua_guardPhone FROM ruasa WHERE rua_matric = : matric");
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $data[] = $row;
      }

      //return data as JSON
      echo json_encode($data);
      
    }
    // Catch any errors in running the prepared statement
    catch(PDOException $e)
    {
    echo $e->getMessage();
    }
    break;



  //MAR'US : VIEW SELF-PROFILE
  case 'getSelfProfile'   :
    // Attempt to query database table and retrieve data
    try {
      $stmt = $pdo->query("SELECT mar_matric,mar_name,mar_ic,mar_faculty,mar_email,mar_phone,mar_guardPhone FROM marus WHERE mar_matric = : matric");
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $data[] = $row;
      }

      //return data as JSON
      echo json_encode($data);
      
    }
    // Catch any errors in running the prepared statement
    catch(PDOException $e)
    {
    echo $e->getMessage();
    }
    break;



  //SCAN ATTENDANCE
  case "scanAttendance":
          // Sanitise URL supplied values
          $scan         = filter_var($obj->scan, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_LOW);
          $matric       = filter_var($obj->matric, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_LOW);

          // Attempt to run PDO prepared statement
          try {
            $sql    = "SELECT * FROM session WHERE session_id = :scan";
            $stmt   = $pdo->prepare($sql);
            $stmt->bindParam(':scan', $scan, PDO::PARAM_STR);
            $stmt->execute();
            
            $session = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($session){
                $sql    = "SELECT * FROM attendance WHERE attend_sessionId= :scan AND attend_marId= :matric";
                $stmt   = $pdo->prepare($sql);
                $stmt->bindParam(':scan', $scan, PDO::PARAM_STR);
                $stmt->bindParam(':matric', $matric, PDO::PARAM_STR);
                $stmt->execute();
                
                $attend = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if($attend){
                    echo json_encode(array('message' => 'You have already marked your attendance for this session' ));
                }else{
                    $sql  = "INSERT INTO attendance(attend_sessionId, attend_marId, attend_status) VALUES(:scan, :matric, 1)";
                    $stmt   = $pdo->prepare($sql);
                    $stmt->bindParam(':matric', $matric, PDO::PARAM_STR);
                    $stmt->bindParam(':scan', $scan, PDO::PARAM_STR);
                    $stmt->execute();
        
                    echo json_encode(array('message' => 'Congratulations your attendance has been marked ' ));
                }
                
            }else{
                echo json_encode(array('message' => 'The QR Code did not exist lol.' ));
            }
            
            
             
          }
          // Catch any errors in running the prepared statement
          catch(PDOException $e)
          {
             echo $e->getMessage();
          }
 
      break;



//LIST OF THE SESSION CREATED BY RUASA
  case 'getMarusList'   :
    // Attempt to query database table and retrieve data
    try {
      $stmt = $pdo->query("SELECT enroll.*, marus.mar_name FROM enroll INNER JOIN marus ON enroll.enrol_mar = marus.mar_matric");
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        $data[] = $row;
      }

      //return data as JSON
      echo json_encode($data);
      
    }
    // Catch any errors in running the prepared statement
    catch(PDOException $e)
    {
    echo $e->getMessage();
    }
    break;





//GET RUASA SELF INFO
  case 'getRSelf'   :
    // Attempt to query database table and retrieve data
    try {
        $sql = "SELECT rua_matric,rua_name,rua_ic,rua_faculty,rua_email,rua_phone,rua_guardPhone FROM ruasa WHERE rua_matric = : matric";
        $stmt  = $pdo->prepare($sql);
        //$stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->execute();
        while($row  = $stmt->fetch(PDO::FETCH_OBJ))
        {
        // Assign each row of data to associative array
        $data[] = $row;
        }

        // Return data as JSON
        echo json_encode($data);
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
        }
  break;



/*
  //ENROLL LIQA GROUP
  case 'enroll':
    $group_code        = filter_var($obj->group_code, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_LOW);
    $matric = filter_var($obj->matric, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_LOW);

    try {
        $sql = 'INSERT INTO enroll (enrol_mar, enrol_liqa) VALUES (:matric, :group_code)';
        $stmt  = $pdo->prepare($sql);
        $stmt->bindParam(':group_code', $group_code, PDO::PARAM_STR);
        $stmt->bindParam(':matric', $matric, PDO::PARAM_STR);
        $stmt->execute();

        echo json_encode(array('message' => 'You are Enrolled'));
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
        }
    break;
}
*/


