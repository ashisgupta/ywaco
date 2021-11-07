<?php
include "autoload.php";


session_start();
include('db.php');

if(isset($_POST['submit'])){ //check if form was submitted
  // Start the session
session_start();

//   $sql = "INSERT INTO users (first_name, last_name, email,dob,institution_name)
// VALUES ('".$_POST['first_name']."', '".$_POST['last_name']."', '".$_POST['email']."', '".$_POST['dob']."', '".$_POST['institutions']."'')";


  // Escape user inputs for security
$first_name = mysqli_real_escape_string($conn, $_REQUEST['first_name']);
$last_name = mysqli_real_escape_string($conn, $_REQUEST['last_name']);
$email = mysqli_real_escape_string($conn, $_REQUEST['email']);
$dob = mysqli_real_escape_string($conn, $_REQUEST['dob']);
$institution_name = mysqli_real_escape_string($conn, $_REQUEST['institutions']);
$first_cash_in_hand = 10000;
$year = 1;
 
// Attempt insert query execution
$sql = "INSERT INTO users (first_name, last_name, email, dob, institution_name,first_cash_in_hand) VALUES ('$first_name', '$last_name', '$email', '$dob', '$institution_name','$first_cash_in_hand')";
// Set session variables
$_SESSION["first_name"] = $_REQUEST['first_name'];
$_SESSION["last_name"] = $_REQUEST['first_name'];
$_SESSION["email"] = $_REQUEST['email'];
$_SESSION["first_cash_in_hand"] = $first_cash_in_hand;
$_SESSION["year"] = $year;
$_SESSION["first_cash_in_hand_flag"]=0;


if ($conn->query($sql) === TRUE) {
  $last_id = $conn->insert_id;
  $_SESSION["id"] = $last_id;
  header("Location:page1.php");
die();
  //echo "New record created successfully";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}
  //echo $message;
die;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Parallax Template - Materialize</title>

  <!-- CSS  -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>
  <link href="css/style.css" type="text/css" rel="stylesheet" media="screen,projection"/>
</head>
<body>
  <nav class="white" role="navigation">
    <div class="nav-wrapper container">
      <a id="logo-container" href="#" class="brand-logo">Logo</a>
      <ul class="right hide-on-med-and-down">
        <li><a href="#">Navbar Link</a></li>
      </ul>

      <ul id="nav-mobile" class="sidenav">
        <li><a href="#">Navbar Link</a></li>
      </ul>
      <a href="#" data-target="nav-mobile" class="sidenav-trigger"><i class="material-icons">menu</i></a>
    </div>
  </nav>



  <div class="container">
    <div class="section">
      <div class="col s12 center">
          <h3><i class="mdi-content-send brown-text"></i></h3>
          <h4>Login</h4>
      </div>
      <!--   Icon Section   -->
      <div class="row">
      <form class="col s12" method="post" action="index.php">
        <div class="row">
          <div class="input-field col s6">
            <input id="first_name" name="first_name" type="text" class="validate" required>
            <label for="first_name">First Name</label>
          </div>
          <div class="input-field col s6">
            <input id="last_name" name="last_name" type="text" class="validate" required>
            <label for="last_name">Last Name</label>
          </div>
        </div>
        <div class="row">
          <div class="input-field col s6">
            <input id="email" name="email" type="text" class="validate" required>
            <label for="email">Email</label>
          </div>
          <div class="input-field col s6">
            <input id="dob" name="dob" type="date" class="datepicker validate" required>
            <label for="dob">Date Of Birth</label>
          </div>
        </div>
        <div class="row">
          <div class="input-field col s12">
            <input id="institutions" type="text" name="institutions" class="validate" required>
            <label for="institutions">Institutions</label>
          </div>
        </div>
        <div class="row">
          <div class="input-field col s12">
            <p>
              <label>
                <input type="checkbox" required />
                <span>
                  
                  <!-- Modal Trigger -->
                  <a class="waves-effect waves-light modal-trigger" href="#modal1">Terms and Conditions </a>

                </span>
              </label>
            </p>
          </div>
        </div>
        <div class="row">
          <button class="btn waves-effect waves-light" type="submit" name="submit">Submit
            <i class="material-icons right">send</i>
          </button>
        </div>
      </form>
    </div>

    </div>
  </div>

  <div>
     <!-- Modal Structure -->
    <div id="modal1" class="modal modal-fixed-footer">
      <div class="modal-content">
        <h4>Terms and Conditions</h4>
        <p>Welcome! You have just started your career as a first year at a very high performance and high paying company </p>
        <p>The Company pays you 10000 rs every year (did we mention it’s a high paying company? ;P Amount is just for game mechanics) </p>
        <p>You will receive a minimum 1000 rs raise every year</p>
        <p>On the day of your first salary - the president of your company bumps into you and advises you to use the money wisely </p>
        <p>He suggests visiting the market and checking all the financial options available to you</p>
        <p>Some options are readily available, while others may requir building some networth before they can be unlocked</p>
        <p>The Game will start with you first year salary of 10000rs </p>
        <p>You have 2 minutes to decide how you want to use the available salary</p>
        <p>Read the descrition of each investment carefully</p>
        <p>Input the amount you want to invest </p>
        <p>Post 2 minutes the game will take the last available inputs as final and compute respective returns, if any</p>
        <p>Each item has different characteristics and will give different returns depending upon events and overall economic scenario</p>
        <p>total 7 rounds will be played - each round representing a period of time and having certain fixed and random events</p>
        <p>Each round will have 2 minutes to decide the utiization of money fo that year </p>
        <p>Goal: to get the Highest networth towards the end of the game</p>
        <br>
        <h4>Tips</h4>
        <p>You can leave some money as cash also</p>
      </div>
      <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Close</a>
      </div>
    </div>

  </div>


 



  <footer class="page-footer teal">
    <div class="container">
      <div class="row">
        <div class="col l6 s12">
          <h5 class="white-text">Company Bio</h5>
          <p class="grey-text text-lighten-4">We are a team of college students working on this project like it's our full time job. Any amount would help support and continue development on this project and is greatly appreciated.</p>


        </div>
        <div class="col l3 s12">
          <h5 class="white-text">Settings</h5>
          <ul>
            <li><a class="white-text" href="#!">Link 1</a></li>
            <li><a class="white-text" href="#!">Link 2</a></li>
            <li><a class="white-text" href="#!">Link 3</a></li>
            <li><a class="white-text" href="#!">Link 4</a></li>
          </ul>
        </div>
        <div class="col l3 s12">
          <h5 class="white-text">Connect</h5>
          <ul>
            <li><a class="white-text" href="#!">Link 1</a></li>
            <li><a class="white-text" href="#!">Link 2</a></li>
            <li><a class="white-text" href="#!">Link 3</a></li>
            <li><a class="white-text" href="#!">Link 4</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="footer-copyright">
      <div class="container">
      Made by <a class="brown-text text-lighten-3" href="http://materializecss.com">Materialize</a>
      </div>
    </div>
  </footer>


  <!--  Scripts-->
  <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
  <script src="js/materialize.js"></script>
  <script src="js/init.js"></script>
  <script type="text/javascript">

  $(document).ready(function(){
    $('#modal1').modal();
  });
          
  </script>

  </body>
</html>
