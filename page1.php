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

// $sql_invest = "SELECT SUM(buy_sell_amount) FROM transactions WHERE user_id='$current_user_id'";
// //print_r($sql_invest);die;
// $result_invest = $conn->query($sql_invest);
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
$result_exp_1 = $conn->query($sql_exp);

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
  $sql3 =  "SELECT name, SUM(buy_sell_unit) FROM investments WHERE user_id='$current_user_id' AND name='$investment_type'";
}else {
  $sql3 =  "SELECT name, SUM(sell_amount) FROM investments WHERE user_id='$current_user_id' AND name='$investment_type'";
}
$json = [];
$result3 = $conn->query($sql3);
if(!empty($result_expenses_purchased->num_rows)) {
  while($row3 = $result3->fetch_assoc()){
       $json = $row3;
  }
}


if($investment_type=='Property'){
$units = isset($json['SUM(buy_sell_unit)']) ? $json['SUM(buy_sell_unit)']:0;
if(($units<$property_unit) && isset($_POST['sell'])) {
  $_SESSION["error"] = "Units are not available to sell!";
header("Location:page1.php");

}
} else {
  if(isset($invested_amount['details']) && is_array($invested_amount['details']) ){
     foreach ($invested_amount['details'] as $key => $row5) {
      if(strtolower($row5["name"])==strtolower($investment_type)){
        $t_amt =$row5['current_value'];
      }
     }
  }
  $total_amount = isset($t_amt) ? $t_amt:0;
  // echo "<pre>";
  // print_r($total_amount); echo "<hr>";
  // print_r($inp_amt);
  // print_r($_POST);
 
if( ($total_amount<$inp_amt) && isset($_POST['sell'])) {
   $_SESSION["error"] = "Stock not available!";
header("Location:page1.php");
  die("Not available to sell!");

}
 //die;
}

if((abs($inp_amt)>$cash_in_hand) && isset($_POST['buy'])){
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
  if(isset($_POST['sell'])){
    $inp_amt = -1 * abs($inp_amt);
  }
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
  <link rel="stylesheet" href="./style.css">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Parallax Template - Materialize</title>

  <!-- CSS  -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>
  <link href="css/style.css" type="text/css" rel="stylesheet" media="screen,projection"/>
  <style type="text/css">
    .card .card-image img {
      height:81px;
    }
    input[type=range] + .thumb.active .value{
      margin-top:0px;
      margin-left: -1px;
    }
  </style>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/prefixfree/1.0.7/prefixfree.min.js"></script>
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

 <div class="section">
      <form class="" method="post" action="page1.php">
        <input type="hidden" name="next_event" value="1">
         
        <div class="card-action">
                <button class="btn waves-effect waves-light" type="submit" name="next_year">Next Year
            <i class="material-icons right">send</i>
              
              </div>
      </form>
    </div>

   <section>
     <div class="row">
      <!-- Start Three rows of investment options -->
       <?php 
       $inv_count=0;$inv_id=[];
      if ($result_investment_options->num_rows > 0) {
  // output data of each row
  while(($row = $result_investment_options->fetch_assoc()) && (in_array($row['id'], [1,2,3])) ) {
    $result1 = $conn->query($transactions);
      $inv_count++;$inv_id[]=$row['id'];
      ?>
      <?php
        $name = str_replace(' ','',$row["name"]);
        $market_value_price = $_SESSION[strtolower($name)];
      ?>
       <?php
        $amt = 0;
        // if(isset($result1) && !empty($result1) && ($result1->num_rows > 0) ) {
        //   // output data of each row
        //   while($row1 = $result1->fetch_assoc()) {
        //     //print_r($row1['name']);print_r($row['name']);
        //     if(strtolower($row1["expense_investment_name"])==strtolower($row["name"])){
        //       $amt = $row1['SUM(sell_amount)'];
        //     }
        //   }
        // }
        if(isset($invested_amount['details']) && is_array($invested_amount['details']) ){
           foreach ($invested_amount['details'] as $key => $row5) {
            if(strtolower($row5["name"])==strtolower($row["name"])){
              $amt =$row5['current_value'];
            }
           }
        }
       ?>
     <form  method="post" action="page1.php">
      <div class="col s12 l2">
       <div class="newcard">
          <div class="banner">
          </div>
          <h5 class="name"><?php echo $row['name']; ?></h5>
          <input type="hidden" name="investment_type" value="<?php echo $row['name']; ?>">
          <input type="hidden" name="current_market_value" value="<?php echo $market_value_price; ?>">
          <div class="title">Yellow Color Metal cant eat but people want it</div>
          <div class="actions">
             <div class="follow-info">
                <h7><a href="#"><span><?php echo $market_value_price; ?></span><small>Price</small></a></h7>
                <h7><a href="#"><span><?php echo $amt; ?></span><small>Owned</small></a></h7>
                <input type="hidden" name="amt_inv" value="<?php echo $amt; ?>">
             </div>
             <h7 style="text-align: center;">
                <a href="#">
                <span>
                <output name="inp_amt_out" id="inp_amt_out" style="color: #26a69a;">5000</output>
                </span>
                <small>   </small>
                </a>
             </h7>
             <div class="title" style="padding: 0 0 0 0;">Select Amount</div>
             <div class="">
                <p class="range-field">
                   <input type="range" id="inp_amt_id" name="inp_amt" min="100" max="10000" step="100" value="5000" oninput="inp_amt_out.value = inp_amt_id.value" />
                </p>
             </div>
             <div class="">
                <table>
                   <tr style="border-bottom:0px;">
                      <td style="padding: 0 0 0 0 !important;">
                         <div class="follow-btn">
                            <button style="background-color:#1c9eff;" type="submit" name="buy">Buy</button>
                         </div>
                      </td>
                      <td style="padding: 0 0 0 0 !important;">
                         <div class="follow-btn">
                            <button style="background-color:#c62828;" type="submit" name="sell">Sell</button>
                         </div>
                      </td>
                   </tr>
                </table>
             </div>
           </div>
        </div>
      </div>
      </form>
        <?php 
      }
    }
        ?>
       <!-- End Three rows of investment options -->
       <div class="col s12 l4">
          <ul class="collection">
            <?php
            if ($result_exp->num_rows > 0) {
              // output data of each row
               $exp_count=0;$exp_id=[];
              while(($row_exp = $result_exp->fetch_assoc()) && ($exp_count<4)) {
                $result_exp1 = $conn->query($transactions);
                $exp_count++;$exp_id[]=$row_exp['id'];
            ?>
            <?php
                $name = str_replace(' ','',$row_exp["name"]);
                $market_value_price = $_SESSION[strtolower($name)];
            ?>
            <?php
            $amt = 0;
            if (isset($result_exp1) && !empty($result_exp1) && ($result_exp1->num_rows > 0)) {
              // output data of each row
              while($row_exp1 = $result_exp1->fetch_assoc()) {
                //print_r($row1['name']);print_r($row['name']);
                if(strtolower($row_exp1["expense_investment_name"])==strtolower($row_exp["name"])){
                  $amt = $row_exp1['SUM(buy_amount)'];
                }
              }
            }
            $input_amount =  isset($row_exp["market_value"]) ? $row_exp["market_value"]:"";
            // echo "<pre> <hr>";print_r($input_amount);  
           ?>
                <form  method="post" action="page1.php">
                  <input type="hidden" name="investment_type" value="<?php echo $row_exp['name']; ?>">
                  <input type="hidden" name="current_market_value" value="<?php echo $market_value_price; ?>">
                  <input id="inp_amt" name="inp_amt" type="hidden" value="<?php echo $input_amount;?>" class="validate"  required>
                  <input type="hidden" name="amt_inv" value="<?php echo $amt; ?>">
                   <input type="hidden" name="happiness_quotient" value="<?php echo $row_exp['happiness_quotient']; ?>">
                  <li class="collection-item avatar">
                     <img src="https://materializecss.com/images/yuna.jpg" alt="" class="circle">
                     <span class="title"><?php echo $row_exp['name']; ?></span>
                     <p>Price : <?php echo $market_value_price; ?> <br>
                     
                     </p>
                     <a href="#!" class="secondary-content">
                        <div class="expense">
                           <div class="actions">
                              <div class="follow-btn">
                                 <?php if(in_array(strtolower($row_exp["name"]), ['get married','higher education'])){
                                          if(in_array(strtolower($row_exp["name"]), $json_once_expenses_purchased)){
                                  ?>
                                            <button style="background-color:#1c9eff;color:grey;" type="submit" name="buy_expenses" disabled>Buy</button>
                                   <?php
                                          } else {
                                          ?>
                                          <button style="background-color:#1c9eff;color:white;" type="submit" name="buy_expenses">Buy</button>
                                  <?php 
                                          }
                                        } else {
                                          if(in_array(strtolower($row_exp["name"]), $json_expenses_purchased)){
                                  ?>
                                          <button style="background-color:#1c9eff;color:grey;" type="submit" name="buy_expenses" disabled>Buy</button>
                                  <?php
                                        } else {
                                        ?>
                                        <button style="background-color:#1c9eff;color:white;" type="submit" name="buy_expenses" >Buy</button>
                                  <?php 
                                        }
                                        }
                                  ?> 

                              </div>
                           </div>
                        </div>
                     </a>
                  </li>
                </form>
                <?php 
      }
    }
        ?>
          </ul>
        </div>
        
 <!--Start of Score card -->
       <div class="col s12 l2">
         <div class="newcard">
            <div class="banner">
            </div>
            <h5 class="name">
            Score Board</h5>
            <div class="title">Keep inestmenting wisely</div>
            <div class="actions">
               <div class="follow-info">
                  <h7><a href="#"><span><?php echo $_SESSION["first_name"]." ".$_SESSION["last_name"]?></span><small>Name</small></a></h7>
                  <h7><a href="#"><span><?php echo $_SESSION["id"];?></span><small>User id</small></a></h7>
                </div>
                <div class="follow-info">
                  <h7><a href="#"><span><?php echo $happiness_quotient; ?></span><small>Happiness Coeffecient</small></a></h7>
                  <h7><a href="#"><span><?php echo $cash_in_hand; ?></span><small>Cash in hand</small></a></h7>
                  </div>
                <div class="follow-info">
                  <h7><a href="#"><span><?php echo abs($invested_amount['total']); ?></span><small>Investment</small></a></h7>
                  <h7><a href="#"><span><?php echo ($invested_amount['current_value']+$cash_in_hand); ?></span><small>Networth</small></a></h7>
                  </div>
                <div class="follow-info">
                  <h7><a href="#"><span><?php echo $_SESSION['year']; ?></span><small>Current Year</small></a></h7>
                  </div>
                <div class="follow-info">
                  <?php 
                  //  $result5 = $conn->query($sql1);
                  $i=0;
                    foreach ($invested_amount['details'] as $key => $row5) {
                      $i++;
                      if ($i % 2 == 0) {
                        echo "</div><div class=\"follow-info\">";
                      }
                    //$json5[] = $row5;
                      echo "<h7><a href=\"#\"><span>".abs($row5['current_value'])."</span><small>".ucwords($row5['name'])."</small></a></h7>";
                    }
                  ?>
               </div>
            </div>
         </div>
      </div>

     </div>
   </section>
  <section>
     <div class="row">
      <!-- Start Three rows of investment options -->
       <?php 
      //echo "<pre>";print_r($inv_id);die;
      if ($result_investment_options_1->num_rows > 0) {
  // output data of each row
  while(($row_1 = $result_investment_options_1->fetch_assoc())  ) {
    if(in_array($row_1['id'], [5,6,7]) ) {
    $result1 = $conn->query($transactions);
      ?>
      <?php
        $name = str_replace(' ','',$row_1["name"]);
        $market_value_price = $_SESSION[strtolower($name)];
      ?>
       <?php
        $amt = 0;
        // if(isset($result1) && !empty($result1) && ($result1->num_rows > 0) ) {
        //   // output data of each row
        //   while($row1 = $result1->fetch_assoc()) {
        //     //print_r($row1['name']);print_r($row['name']);
        //     if(strtolower($row1["expense_investment_name"])==strtolower($row_1["name"])){
        //       $amt = $row1['SUM(sell_amount)'];
        //     }
        //   }
        // }
        if(isset($invested_amount['details']) && is_array($invested_amount['details']) ){
           foreach ($invested_amount['details'] as $key => $row5) {
            if(strtolower($row5["name"])==strtolower($row_1["name"])){
              $amt =$row5['current_value'];
            }
           }
        }
       ?>
     <form  method="post" action="page1.php">
      <div class="col s12 l2">
       <div class="newcard">
          <div class="banner">
          </div>
          <h5 class="name"><?php echo $row_1['name']; ?></h5>
          <input type="hidden" name="investment_type" value="<?php echo $row_1['name']; ?>">
          <div class="title">Yellow Color Metal cant eat but people want it</div>
          <div class="actions">
             <div class="follow-info">
                <h7><a href="#"><span><?php echo $market_value_price; ?></span><small>Price</small></a></h7>
                <h7><a href="#"><span><?php echo $amt; ?></span><small>Owned</small></a></h7>
                <input type="hidden" name="amt_inv" value="<?php echo $amt; ?>">

          <input type="hidden" name="current_market_value" value="<?php echo $market_value_price; ?>">
             </div>
             <h7 style="text-align: center;">
                <a href="#">
                <span>
                <output name="inp_amt_out" id="inp_amt_out" style="color: #26a69a;">5000</output>
                </span>
                <small>   </small>
                </a>
             </h7>
             <div class="title" style="padding: 0 0 0 0;">Select Amount</div>
             <div class="">
                <p class="range-field">
                   <input type="range" id="inp_amt_id" name="inp_amt" min="100" max="10000" step="100" value="5000" oninput="inp_amt_out.value = inp_amt_id.value" />
                </p>
             </div>
             <div class="">
                <table>
                   <tr style="border-bottom:0px;">
                      <td style="padding: 0 0 0 0 !important;">
                         <div class="follow-btn">
                            <button style="background-color:#1c9eff;" type="submit" name="buy">Buy</button>
                         </div>
                      </td>
                      <td style="padding: 0 0 0 0 !important;">
                         <div class="follow-btn">
                            <button style="background-color:#c62828;" type="submit" name="sell">Sell</button>
                         </div>
                      </td>
                   </tr>
                </table>
             </div>
           </div>
        </div>
      </div>
      </form>
        <?php 
      }
      }
    }
        ?>
       <!-- End Three rows of investment options -->
       <div class="col s12 l4">
          <ul class="collection" style="margin-top: -131px;">
            <?php
            if ($result_exp_1->num_rows > 0) {
              // output data of each row
              
              while($row_exp_1 = $result_exp_1->fetch_assoc()) {
                if(in_array($row_exp_1['id'], [5,6,7,8,9,10])) {
                $result_exp1 = $conn->query($transactions);
                
            ?>
            <?php
                $name = str_replace(' ','',$row_exp_1["name"]);
                $market_value_price = $_SESSION[strtolower($name)];
            ?>
            <?php
            $amt = 0;
            if (isset($result_exp1) && !empty($result_exp1) && ($result_exp1->num_rows > 0)) {
              // output data of each row
              while($row_exp1 = $result_exp1->fetch_assoc()) {
                //print_r($row1['name']);print_r($row['name']);
                if(strtolower($row_exp1["expense_investment_name"])==strtolower($row_exp_1["name"])){
                  $amt = $row_exp1['SUM(buy_amount)'];
                }
              }
            }
            $input_amount =  isset($row_exp_1["market_value"]) ? $row_exp_1["market_value"]:"";
            // echo "<pre> <hr>";print_r($input_amount);  
           ?>
                <form  method="post" action="page1.php">
                  <input type="hidden" name="investment_type" value="<?php echo $row_exp_1['name']; ?>">
                  <input type="hidden" name="current_market_value" value="<?php echo $market_value_price; ?>">
                  <input id="inp_amt" name="inp_amt" type="hidden" value="<?php echo $input_amount;?>" class="validate"  required>
                  <input type="hidden" name="amt_inv" value="<?php echo $amt; ?>">
                   <input type="hidden" name="happiness_quotient" value="<?php echo $row_exp_1['happiness_quotient']; ?>">
                  <li class="collection-item avatar">
                     <img src="https://materializecss.com/images/yuna.jpg" alt="" class="circle">
                     <span class="title"><?php echo $row_exp_1['name']; ?></span>
                     <p>Price : <?php echo $market_value_price; ?> <br>
                     
                     </p>
                     <a href="#!" class="secondary-content">
                        <div class="expense">
                           <div class="actions">
                              <div class="follow-btn">
                                 <?php if(in_array(strtolower($row_exp_1["name"]), ['get married','higher education'])){
                                          if(in_array(strtolower($row_exp_1["name"]), $json_once_expenses_purchased)){
                                  ?>
                                            <button style="background-color:#1c9eff;color:grey;" type="submit" name="buy_expenses" disabled>Buy</button>
                                   <?php
                                          } else {
                                          ?>
                                          <button style="background-color:#1c9eff;color:white;" type="submit" name="buy_expenses">Buy</button>
                                  <?php 
                                          }
                                        } else {
                                          if(in_array(strtolower($row_exp_1["name"]), $json_expenses_purchased)){
                                  ?>
                                          <button style="background-color:#1c9eff;color:grey;" type="submit" name="buy_expenses" disabled>Buy</button>
                                  <?php
                                        } else {
                                        ?>
                                        <button style="background-color:#1c9eff;color:white;" type="submit" name="buy_expenses" >Buy</button>
                                  <?php 
                                        }
                                        }
                                  ?> 

                              </div>
                           </div>
                        </div>
                     </a>
                  </li>
                </form>
                <?php 
              }
      }
    }
        ?>
          </ul>
        </div>
        
       

     </div>
   </section>

  
   

   

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
