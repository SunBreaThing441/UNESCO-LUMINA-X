
<?php

session_start();
require_once("connect.php");
error_reporting(E_ERROR | E_PARSE);
$aa=$_SESSION["aadh"];
if(empty($_SESSION['email']))
{         
   header("location:login.php?notice=auth_required");
   exit();
}
$sql="SELECT * FROM `personal_details` WHERE aadhaar= '$aa' ";
$result=mysqli_query($connect,$sql);
$a=$result->fetch_assoc();
$sql="SELECT * FROM `attachment` WHERE aadhaar= '$aa' ";
$result=mysqli_query($connect,$sql);
$b=$result->fetch_assoc();
if ($b['aadhaar']===$_SESSION['aadh']) {
   
       
    
    header("location:preview.php");

}




                        if(isset($_POST['next3'])){
                          $aadh=$_SESSION['aadh'];

                          // Helper upload processor
                          function processLuminaUpload($fileKeyAlt, $sessionKey) {
                              $f = null;
                              foreach ($fileKeyAlt as $k) {
                                  if (!empty($_FILES[$k]) && $_FILES[$k]['error'] === 0 && !empty($_FILES[$k]['name'])) {
                                      $f = $_FILES[$k];
                                      break;
                                  }
                              }
                              if (!$f) return '';

                              $img_name = $f['name'];
                              $img_tmp = $f['tmp_name'];
                              $img_ex = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
                              $allowed = array("jpg", "jpeg", "png");

                              if (in_array($img_ex, $allowed)) {
                                  $new_name = uniqid("IMG-", true) . '.' . $img_ex;
                                  $target_dir = 'gov/upload/';
                                  if (!file_exists($target_dir)) {
                                      mkdir($target_dir, 0777, true);
                                  }
                                  $dest = $target_dir . $new_name;
                                  if (move_uploaded_file($img_tmp, $dest)) {
                                      $_SESSION[$sessionKey] = $dest;
                                      return $dest;
                                  }
                              }
                              return '';
                          }

                          // Process the 7 mandatory/conditional file streams
                          $img_upload_path  = processLuminaUpload(['passport'], 'passport');
                          $img_upload_path1 = processLuminaUpload(['aadh', 'aadhaar'], 'aadhimg');
                          $img_upload_path2 = processLuminaUpload(['passbook'], 'passbookimg');
                          $img_upload_path3 = processLuminaUpload(['castecertificate', 'income'], 'castecertificate');
                          $img_upload_path4 = processLuminaUpload(['cfeesrecipt', 'fees'], 'feesrecipt');
                          $img_upload_path5 = processLuminaUpload(['markssheet', 'marksheet'], 'markssheet');
                          $img_upload_path6 = processLuminaUpload(['hfees', 'hostelfees'], 'hfees');

                          // Check if record already exists in attachment
                          $check_att = mysqli_query($connect, "SELECT * FROM attachment WHERE aadhaar = '$aadh'");
                          if (mysqli_num_rows($check_att) > 0) {
                              $sql = "UPDATE attachment SET 
                                      passportimg = IF('$img_upload_path' != '', '$img_upload_path', passportimg),
                                      aadhaarimg = IF('$img_upload_path1' != '', '$img_upload_path1', aadhaarimg),
                                      passbookimg = IF('$img_upload_path2' != '', '$img_upload_path2', passbookimg),
                                      incomeimg = IF('$img_upload_path3' != '', '$img_upload_path3', incomeimg),
                                      feesimg = IF('$img_upload_path4' != '', '$img_upload_path4', feesimg),
                                      markssheetimg = IF('$img_upload_path5' != '', '$img_upload_path5', markssheetimg),
                                      hostelfeesimg = IF('$img_upload_path6' != '', '$img_upload_path6', hostelfeesimg)
                                      WHERE aadhaar = '$aadh'";
                              $result = mysqli_query($connect, $sql);
                          } else {
                              $sql = "INSERT INTO attachment(aadhaar,passportimg,aadhaarimg,passbookimg,incomeimg,feesimg,markssheetimg,hostelfeesimg) 
                                      VALUES ('$aadh','$img_upload_path','$img_upload_path1','$img_upload_path2','$img_upload_path3','$img_upload_path4','$img_upload_path5','$img_upload_path6')";
                              $result = mysqli_query($connect, $sql);
                          }

                          if ($result) {
                              header("location:preview.php");
                              exit();
                          }
                        }

?>







<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship</title>

    
    <link rel="stylesheet" href="css/style.css">
    <style>
           .from{
    border-radius: 20px;
   background-color:white ;
   font-family: 'Courier New', Courier, monospace;
   display: table-caption;
   position: absolute;
   
   padding: 20px 50px 30px 50px;
   margin-top: 3%;
   width: 40%;
   height: auto;

   top: 50%;
   left: 65%;
   transform: translate(-80%,-50%);
}
.file label{
    font-family: fantasy;
    color:rgb(67 70 70);
   font-size:140%
}

            
    </style>
   
</head>
<body>
    <section>

        <nav>
            <h1>Scholarship</h1>
            <div class="navlink">

                <ul>
                   <li><a href="about.html">About</a></li>
                   <li><a href="home.php?data=1">Home</a></li>
                     <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
    </section>
    <?php
     $message="";
      
     ?>
    
        <div class="from">
                <form action="attachment.php" method="post" enctype='multipart/form-data'>
           <div class="span">Attachment</div>
           <div class="form">
          
        
           
            <div class="file">
                    <label>Passport size photo :-</label>
                    <input type="file" name="passport" id="passport" Required>
            </div>
                    <div class="file">
                    <label>Aadhar card :-</label>
                    <input type="file" name="aadh" id="aadh" Required>
            </div>
                    <div class="file">
                            <label>Passbook:-</label>
                            <input type="file" name="passbook" id="passbook" Required>
                         </div>
                         <div class="file">
                        <label>Caste/Income Certificate:-</label>
                        <input type="file" name="castecertificate" id="castecertificate" Required>
                </div>
                <div class="file">
                <label>Fees Recipt:-</label>
                <input type="file" name="cfeesrecipt" id="cfeesrecipt" Required>  
        </div>
        <div class="file">
                <label>Last semester/year marks card:-</label>
                <input type="file" name="markssheet" id="markssheet" Required>  
        </div>
    
           <?php
           
           if($a['hostel']=='yes'){

              echo' <div class="file">';
              echo'    <label>Hostel Fees Recipt:-</label>';
              echo'<input type="file" name="hfees" id="hfees" Required>  ';
              echo' </div>';
           }?>

   

                    
                    
               </div>
               <section>
               <div class="button">
                <input type="submit" value="&#8594" name="next3" >
        </div>
        
    </section>
</form> 
        
</div>
<?php}?>
</section>
       </body>
       </html>