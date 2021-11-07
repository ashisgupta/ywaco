<?php
include "autoload.php";
session_start();
$current_user_id = $_SESSION["id"];
include('db.php');
include('page1_sql.php');
$sql = "SELECT * FROM investment_options";
$sql_exp = "SELECT * FROM expense_options";


$sql_h_q = "SELECT SUM(happiness_quotient) FROM expenses_transactions WHERE user_id='$current_user_id'";
$result_h_q = $conn->query($sql_h_q);
$row_h_q = $result_h_q->fetch_row();

$sql_invest = "SELECT SUM(buy_sell_amount) FROM transactions WHERE user_id='$current_user_id'";
//print_r($sql_invest);die;
$result_invest = $conn->query($sql_invest);
$row_invest = 0;//$result_invest->fetch_row();
//print_r($row_invest);die;

//$cash_in_hand = $_SESSION["first_cash_in_hand"];
$year = $_SESSION["year"];

// $sql4 = "SELECT * FROM users where id='$current_user_id'";
// $result4 = $conn->query($sql4);
// $row4 = $result4->fetch_row();
// print_r($sql4);die;

$result = $conn->query($sql);
$result_exp = $conn->query($sql_exp);

if(isset($_POST['next_event'])){
  $year_amount = $_SESSION['year']*1000+10000;
   $cash_in_hand = $row_closing_cash_in_hand+$year_amount;
   
  addYear($_SESSION["id"],$_SESSION["year"],$row_closing_cash_in_hand,$cash_in_hand,2,$conn);

  $_SESSION['year']=$_SESSION['year']+1;
  $sql_events = "SELECT id FROM events";
  $result_events = $conn->query($sql_events);
$row_events = $result_events->fetch_all();$value=[];
// foreach ($row_events as $key => $value) {
//   $value[]=$value;
// }
shuffle($row_events);
$oneRandomElements = array_slice($row_events, 0, 1);
$oneRandomElements = is_array($oneRandomElements) ? current($oneRandomElements):'';
$oneRandomElements = is_array($oneRandomElements) ? current($oneRandomElements):'';
  $sql_event = "SELECT * FROM events WHERE id='$oneRandomElements'";
  $result_event = $conn->query($sql_event);
$row_event = $result_event->fetch_assoc();
$data = setPriceChange($row_event['symbol'],$conn,$current_user_id);

// echo "<pre>";
// print_r($data);
// print_r((($row_event)));
// print_r($_SESSION);
// die;
}

$cash_sql = "SELECT * FROM investments WHERE user_id='$current_user_id'";
$cash_result = $conn->query($cash_sql);
//$cash_all = $cash_result->fetch_assoc();
// echo "<pre>";
//  while($row_exp = $cash_result->fetch_assoc()) {
// print_r($row_exp);
//  }


//die;

//print_r($result1->fetch_assoc());die;
//print_r($_SESSION);die;
if(isset($_POST['sell']) || isset($_POST['buy']) ){ //check if form was submitted

//   $input = $_POST['first_name']; //get input text
//   $message = "Success! You entered: ".$input;
//   echo $message;die;

    // Escape user inputs for security
$investment_type = mysqli_real_escape_string($conn, $_REQUEST['investment_type']);
$amt_inv = mysqli_real_escape_string($conn, $_REQUEST['amt_inv']);
$inp_amt = mysqli_real_escape_string($conn, isset($_REQUEST['inp_amt'])? $_REQUEST['inp_amt']:0);
$amt_inv = is_numeric($amt_inv)?$amt_inv:0 +$inp_amt;
$current_market_value = mysqli_real_escape_string($conn, $_REQUEST['current_market_value']);
$property_unit = isset($_REQUEST['property_unit']) ? mysqli_real_escape_string($conn, $_REQUEST['property_unit']):"0";
$opening_cash_in_hand = $cash_in_hand;
if(isset($_POST['buy'])) {
  $closing_cash_in_hand = $opening_cash_in_hand - $inp_amt;
  //$inp_amt = -1 * abs($inp_amt);
  $buy_sell_type=1;
} else {
  $closing_cash_in_hand = $opening_cash_in_hand + $inp_amt;
  $buy_sell_type=2;
}

if($investment_type=='Property'){
  $sql3 =  "SELECT name, SUM(buy_sell_unit) FROM investments WHERE user_id='$current_user_id' AND name='$investment_type'  GROUP BY name";
}else {
  $sql3 =  "SELECT name, SUM(buy_sell_amount) FROM investments WHERE user_id='$current_user_id' AND name='$investment_type'  GROUP BY name";
}

$result3 = $conn->query($sql3);
while($row3 = $result3->fetch_assoc()){
     $json = $row3;
}

if($investment_type=='Property'){
$units = isset($json['SUM(buy_sell_unit)']) ? $json['SUM(buy_sell_unit)']:0;
if(($units<$property_unit) && isset($_POST['sell'])) {
  $_SESSION["error"] = "Units are not available to sell!";
header("Location:page1.php");

}
} else {
  $total_amount = isset($json['SUM(buy_sell_amount)']) ? $json['SUM(buy_sell_amount)']:0;
  // echo "<pre>";
  // print_r($total_amount); echo "<hr>";
  // print_r($inp_amt);
  // print_r($_POST);
 
if( ($total_amount<$inp_amt) && isset($_POST['sell'])) {
   $_SESSION["error"] = "Cash not available!";
header("Location:page1.php");
  die("Not available to sell!");

}
 //die;
}

if(abs($inp_amt)>$cash_in_hand){
   $_SESSION["error"] = "Cash not available!";
header("Location:page1.php");
  die("Cash not available!");
}


// print_r($qry_result->fetch_assoc());
// print_r($check_portfolio);
// die;

 $id = $_SESSION["id"];
//Insert Investments
$sql = "INSERT INTO transactions (expense_investment_name, user_id, current_market_value, invested_amount, transactions, buy_sell_unit,opening_cash_in_hand, closing_cash_in_hand, year,buy_sell_type) VALUES ('$investment_type', '$id', '$current_market_value', '$amt_inv', '$inp_amt', '$property_unit', '$opening_cash_in_hand', '$closing_cash_in_hand', '$year','$buy_sell_type')";

if ($conn->query($sql) === TRUE) {
$check_portfolio = "SELECT * FROM portfolio WHERE user_id='$current_user_id' AND name='$investment_type' ";
$qry_result = $conn->query($check_portfolio);
$qry_row = $qry_result->fetch_assoc();
if(!empty($qry_row)) {
  $id=$qry_row['id'];
  $invested_value = $inp_amt+$qry_row['invested_value'];
  $current_value = $inp_amt+$qry_row['current_value'];
      $update = "UPDATE portfolio SET invested_value = '$invested_value', current_value = '$current_value' WHERE id='$id';";
     // print_r($update);die;
      $conn->query($update);
} else {

  $sql = "INSERT INTO portfolio (name, user_id, new_rate, invested_value, current_value) VALUES 
                         ('$investment_type', '$id', '$current_market_value', '$inp_amt', '$inp_amt')";
                         if ($conn->query($sql) === TRUE) {
                           header("Location:page1.php");
die();
                          } else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}
}

 


 
  //echo "New record created successfully";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

} elseif(isset($_POST['buy_expenses'])){
//   echo "<pre>";
// print_r($_POST);
// die;
$investment_type = mysqli_real_escape_string($conn, $_REQUEST['investment_type']);
$happiness_quotient = mysqli_real_escape_string($conn, $_REQUEST['happiness_quotient']);
$amt_inv = mysqli_real_escape_string($conn, $_REQUEST['amt_inv']);
$inp_amt = mysqli_real_escape_string($conn, isset($_REQUEST['inp_amt'])? $_REQUEST['inp_amt']:"");
$current_market_value = mysqli_real_escape_string($conn, $_REQUEST['current_market_value']);
$property_unit = isset($_REQUEST['property_unit']) ? mysqli_real_escape_string($conn, $_REQUEST['property_unit']):"0";
 $id = $_SESSION["id"];
 if(abs($inp_amt)>$cash_in_hand){
   $_SESSION["error"] = "Cash not available!";
header("Location:page1.php");
  die("Cash not available!");
}
$opening_cash_in_hand = $cash_in_hand;
$closing_cash_in_hand = $cash_in_hand - $current_market_value;
$year = $_SESSION['year'];

// insert expenses
$sql = "INSERT INTO transactions (expense_investment_name, user_id, current_market_value, invested_amount, transactions,buy_amount, buy_sell_unit,happiness_quotient,buy_sell_type,opening_cash_in_hand, closing_cash_in_hand, year) VALUES ('$investment_type', '$id', '$current_market_value', '$amt_inv', '$current_market_value', '$current_market_value', '$property_unit','$happiness_quotient','3','$opening_cash_in_hand', '$closing_cash_in_hand', '$year')";
if ($conn->query($sql) === TRUE) {
  header("Location:page1.php");
die();
  //echo "New record created successfully";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

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

<?php
       

        if (!empty($_SESSION['error'])){
    ?>
    <div class="alert"> 
    <?php
        echo $_SESSION['error'];
        unset($_SESSION['error']);
        }
    ?>
   </div>


  <div class="container">

    <div class="section">
      <form class="" method="post" action="page1.php">
        <input type="hidden" name="next_event" value="1">
         
        <div class="card-action">
                <button class="btn waves-effect waves-light" type="submit" name="next_year">Next Year
            <i class="material-icons right">send</i>
              
              </div>
      </form>
    </div>
    <div class="section">
      <div class="col s12 center">
          <h3><i class="mdi-content-send brown-text"></i></h3>
          <h4>Expenses</h4>
      </div>
      <!--   Icon Section   -->
      <div class="row">

     <div class="col s8 center">
      <?php 

      if ($result_exp->num_rows > 0) {
  // output data of each row
  while($row_exp = $result_exp->fetch_assoc()) {
    $result_exp1 = $conn->query($transactions);

      ?>
      <?php
                $name = str_replace(' ','',$row_exp["name"]);
                $market_value_price = $_SESSION[strtolower($name)];
                 ?> 
       <form class="col m6" method="post" action="page1.php">
        <input type="hidden" name="investment_type" value="<?php echo $row_exp['name']; ?>">
       
        <input type="hidden" name="current_market_value" value="<?php echo $market_value_price; ?>">


        <input type="hidden" name="happiness_quotient" value="<?php echo $row_exp['happiness_quotient']; ?>">
      <div class="row">
          <div class="">
            <div class="card blue-grey darken-1">
              <div class="card-content white-text">
                <span class="card-title"><?php echo isset($row_exp["name"]) ? $row_exp["name"]:""; ?></span>
                
                <p>Current Market Price : <?php echo $market_value_price; ?></p>
                <?php
                
                $amt = 0;
                 if (isset($result_exp1) && !empty($result_exp1) && ($result_exp1->num_rows > 0)) {
  // output data of each row
  while($row_exp1 = $result_exp1->fetch_assoc()) {
//print_r($row1['name']);print_r($row['name']);
if(strtolower($row_exp1["expense_investment_name"])==strtolower($row_exp["name"])){
  $amt = $row_exp1['SUM(buy_amount)'];
}
  }}
  $input_amount =  isset($row_exp["market_value"]) ? $row_exp["market_value"]:"";
  // echo "<pre> <hr>";print_r($input_amount);
  
                 ?>
                <p>Amount Invested : <?php echo $amt; ?></p>
                 <input type="hidden" name="amt_inv" value="<?php echo $amt; ?>">
                <p>
                  
                   <input id="inp_amt" name="inp_amt" type="hidden" value="<?php echo $input_amount;?>" class="validate"  required>
            <label for="inp_amt">Amount</label>
                   <?php 
                 
                   ?>
                   
                </p>
              </div>
              <div class="card-action">
                <?php if(in_array(strtolower($row_exp["name"]), ['get married','higher education'])){

                  if(in_array(strtolower($row_exp["name"]), $json_once_expenses_purchased)){
                    ?>
                      <button class="btn waves-effect waves-light" type="submit" name="buy_expenses" disabled>Buy
                       <i class="material-icons right">send</i>
                    <?php
                  } else {
                 ?>
                  <button class="btn waves-effect waves-light" type="submit" name="buy_expenses">Buy
            <i class="material-icons right">send</i>

                <?php 
              }

              } else {
                if(in_array(strtolower($row_exp["name"]), $json_expenses_purchased)){
                  ?>
                  <button class="btn waves-effect waves-light" type="submit" name="buy_expenses" disabled>Buy
            <i class="material-icons right">send</i>
                  <?php
                } else {
                ?> 
                  <button class="btn waves-effect waves-light" type="submit" name="buy_expenses">Buy
            <i class="material-icons right">send</i>
                <?php 
              }
              } ?> 
              
              </div>
            </div>
          </div>
        </div>
      </form>
        <?php 
      }
    }
        ?>

 <?php 
      if ($result_investment_options->num_rows > 0) {
  // output data of each row
  while($row = $result_investment_options->fetch_assoc()) {
    $result1 = $conn->query($transactions);
  
      ?>
      <?php

                 $name = str_replace(' ','',$row["name"]);

                $market_value_price = $_SESSION[strtolower($name)];
                 ?> 
       <form class="col s8 m6" method="post" action="page1.php">
        <input type="hidden" name="investment_type" value="<?php echo $row['name']; ?>">
       
        <input type="hidden" name="current_market_value" value="<?php echo $market_value_price; ?>">
      <div class="row">
          <div class="col s8 m6">
            <div class="card blue-grey darken-1">
              <div class="card-content white-text">
                <span class="card-title"><?php echo isset($row["name"]) ? $row["name"]:""; ?></span>
                 
                <p>Current Market Price : <?php echo $market_value_price; ?></p>
                <?php
                $amt = 0;
                 if(isset($result1) && !empty($result1) && ($result1->num_rows > 0) ) {
  // output data of each row
  while($row1 = $result1->fetch_assoc()) {
//print_r($row1['name']);print_r($row['name']);
if(strtolower($row1["expense_investment_name"])==strtolower($row["name"])){
  $amt = $row1['SUM(sell_amount)'];
}
  }}
                 ?>
                <p>Amount Invested : <?php echo $amt; ?></p>
                 <input type="hidden" name="amt_inv" value="<?php echo $amt; ?>">
                <p>
                  <?php
                  if($row["type"]==1) {

                  ?>
                  <div class="row">
                    <div class="input-field col s12">
    <select name="property_unit">
      <option value="" disabled selected>Choose your option</option>
      <option value="1">1 Unit</option>
      <option value="2">2 Unit</option>
      <option value="3">3 Unit</option>
    </select>
    <label>Property is available in units to buy.</label>
  </div>
                  </div>
                  <?php
                } else {

                   ?>
                   <input id="inp_amt" name="inp_amt" type="text" class="validate" required>
            <label for="inp_amt">Amount</label>
                   <?php 
                 }
                   ?>
                   
                </p>
              </div>
              <div class="card-action">
                <button class="btn waves-effect waves-light" type="submit" name="buy">Buy
            <i class="material-icons right">send</i>
                <button class="btn waves-effect waves-light" type="submit" name="sell">Sell
            <i class="material-icons right">send</i>
              </div>
            </div>
          </div>
        </div>
      </form>
        <?php 
      }
    }
        ?>



     </div>


     <div class="col s4 center">
   
       <div class="row">
        <h4>Session id : <?php echo $_SESSION["id"];?></h4>
          <h4>Name : <?php echo $_SESSION["first_name"]." ".$_SESSION["last_name"]?></h4>
          <h4>Cash in hand : <?php echo $cash_in_hand; ?></h4>
        <h4>Happiness Coeffecient : <?php echo $happiness_quotient; ?></h4>

       
        <div>
          <h4>Investment :  <?php echo abs($invested_amount['total']); ?></h4>
          <h4>Networth :  <?php echo ($invested_amount['current_value']+$cash_in_hand); ?></h4>
          <h4>Year : <?php echo $_SESSION['year']; ?></h4>
          <h4>Investment Details </h4>
          <?php 
//  $result5 = $conn->query($sql1);
foreach ($invested_amount['details'] as $key => $row5) {
     //$json5[] = $row5;
     echo "<h5>".ucwords($row5['name'])." : "."<strong>".abs($row5['current_value'])."</h5></strong>";
}


          ?>

        </div>
       </div>

    </div>

    </div>
  </div>

  <div>
     <!-- Modal Structure -->
    <div id="modal1" class="modal modal-fixed-footer">
      <div class="modal-content">
        <h4>Yearly Event : <?php echo $_SESSION['year']. " year"; ?></h4>
        <p>Event Name : <?php 
        if(isset($row_event['event_name'])) {
          echo  $row_event['event_name']; 
        }
        ?></p>
        <p>Event Description : <?php 
        if(isset($row_event['description'])) {
          echo  $row_event['description']; 
        }
        ?></p>
        
        <br>
        
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

<form class="col m6" method="post" action="page1.php" name="floating_button">

<div class="fixed-action-btn">
  <a class="btn-floating btn-large red">
    <i class="large material-icons">navigate_next</i>
  </a>
  <ul>
    <li><a class="btn-floating red"><i class="material-icons">insert_chart</i></a></li>
    <li><a class="btn-floating yellow darken-1"><i class="material-icons">format_quote</i></a></li>
    <li><a class="btn-floating green"><i class="material-icons">publish</i></a></li>
    <li><a class="btn-floating blue"><i class="material-icons">attach_file</i></a></li>
  </ul>
</div>
</form>
  <!--  Scripts-->
  <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
  <script src="js/materialize.js"></script>
  <script src="js/init.js"></script>
  <script type="text/javascript">

  $(document).ready(function(){
    $('#modal1').modal();
    $('select').formSelect();
    $('.fixed-action-btn').floatingActionButton();

        
  });
          
  </script>
  <?php if(isset($_POST['next_event'])){ 

    ?>
  <script type="text/javascript">

  $(document).ready(function(){
    
    $('#modal1').modal('open');

        
  });
          
  </script>
  <?php } ?>

  </body>
</html>
