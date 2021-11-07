<?php
//read investment
$investment_options = "SELECT * FROM investment_options";
$result_investment_options = $conn->query($investment_options);
//read expenses
$expense_options = "SELECT * FROM expense_options";
//read all transactions
$transactions = "SELECT expense_investment_name, SUM(sell_amount), SUM(buy_amount) FROM transactions WHERE user_id='$current_user_id' GROUP BY expense_investment_name";



$closing_cash_in_hand = "SELECT closing_cash_in_hand FROM transactions WHERE user_id='$current_user_id' ORDER BY `id` DESC LIMIT 1";
$result_closing_cash_in_hand = $conn->query($closing_cash_in_hand);
$no_of_rows_closing_cash_in_hand = $result_closing_cash_in_hand->num_rows;

$row_closing_cash_in_hand = $result_closing_cash_in_hand->fetch_row();
$row_closing_cash_in_hand = isset($row_closing_cash_in_hand[0]) ? $row_closing_cash_in_hand[0]:0;


// $buy_sell_amount_sum = "SELECT SUM(buy_sell_amount) FROM transactions WHERE user_id='$current_user_id'";
// $result_buy_sell_amount_sum = $conn->query($buy_sell_amount_sum);
// $row_buy_sell_amount_sum = $result_buy_sell_amount_sum->fetch_row();
// $row_buy_sell_amount_sum = isset($row_buy_sell_amount_sum[0]) ? $row_buy_sell_amount_sum[0]:0;
$_SESSION["first_cash_in_hand_flag"] = $_SESSION["first_cash_in_hand_flag"]+1;
//print_r($_SESSION["first_cash_in_hand_flag"]);die;

if($no_of_rows_closing_cash_in_hand==0){
	//this code execute only once
	addYear($_SESSION["id"],$_SESSION["year"],0,$_SESSION["first_cash_in_hand"],2,$conn);
	$cash_in_hand = $_SESSION["first_cash_in_hand"];
	setBasicRatesInSession($conn);
	
} else {
   $cash_in_hand = $row_closing_cash_in_hand;
}
$year = $_SESSION['year'];
$expenses_purchased_sql="SELECT expense_investment_name FROM transactions WHERE user_id='$current_user_id' AND buy_sell_type='3' AND year='$year' ";
$result_expenses_purchased = $conn->query($expenses_purchased_sql);
$no_of_rows_expenses_purchased = $result_expenses_purchased->num_rows;
$json_expenses_purchased =[];$json_once_expenses_purchased=[];
while($row_expenses_purchased = $result_expenses_purchased->fetch_assoc()){
     $json_expenses_purchased[] = strtolower($row_expenses_purchased['expense_investment_name']);
}

$once_expenses_purchased_sql="SELECT expense_investment_name FROM transactions WHERE user_id='$current_user_id' AND buy_sell_type='3' AND expense_investment_name IN ('Get Married','Higher Education')";

$result_once_expenses_purchased = $conn->query($once_expenses_purchased_sql);
$no_of_rows_once_expenses_purchased = !empty($result_once_expenses_purchased) ? $result_once_expenses_purchased->num_rows:"";
if(!empty($result_once_expenses_purchased)) {
	while($row_once_expenses_purchased = $result_once_expenses_purchased->fetch_assoc()){
     $json_once_expenses_purchased[] = strtolower($row_once_expenses_purchased['expense_investment_name']);
    }
} else {
	$json_once_expenses_purchased = [];
}

// echo "<pre>";
// print_r($_SESSION);
// die;

$invested_amount = getInvestedAmount($_SESSION['id'],$conn);

$happiness_quotient = getHappinessQuotient($_SESSION['id'],$conn);
// echo "<pre>";
// print_r($happiness_quotient);
// die;


function addYear($id,$year,$opening_cash_in_hand,$closing_cash_in_hand,$buy_sell_type,$conn){

	$sql = "INSERT INTO transactions (expense_investment_name, user_id,sell_amount, transactions,opening_cash_in_hand, closing_cash_in_hand, year,buy_sell_type) VALUES ('Salary', '$id', '$closing_cash_in_hand', '$closing_cash_in_hand', '$opening_cash_in_hand', '$closing_cash_in_hand', '$year','$buy_sell_type')";
	if ($conn->query($sql) === TRUE) {
		return true;
	} else {
		return false;
	}
}

function setBasicRatesInSession($conn){
	
	$investment_options_sql = "SELECT * FROM events_prices where Event='Base Rate'";
	$result_investment_options = $conn->query($investment_options_sql);
	$row_result_investment = $result_investment_options->fetch_assoc();
	foreach ($row_result_investment as $key => $value) {
		if(!(in_array($key, ['id','Event','created_at','updated_at']))) {
			//echo $key." : ",$value."<br>";
			$_SESSION[$key]=$value;
		}
	
	}
	
}

function setPriceChange($symbol,$conn,$user_id) {
	$event = 'Scene '.$symbol;
	$investment_options_sql = "SELECT * FROM events_prices where Event='$event'";
	$result_investment_options = $conn->query($investment_options_sql);
	$row_result_investment = $result_investment_options->fetch_assoc();
	//set price change in session
	foreach ($row_result_investment as $key => $value) {
	    if(!(in_array($key, ['id','Event','created_at','updated_at']))) {
	      //echo $key." : ",$value."<br>";
	      if(strlen((string)$value)>2){
	        $_SESSION[$key]=$_SESSION[$key]+$value;
	      } else {
	        $_SESSION[$key]=($_SESSION[$key]*(100+$value))/100;
	      }
	      
	    }
  
  	}
  	//set price change in portfolio
  	$portfolio_sql = "SELECT * FROM portfolio where user_id='$user_id'";
  	$result_portfolio = $conn->query($portfolio_sql);
  	while($row_portfolio = $result_portfolio->fetch_assoc()){
  		$old_value = $row_portfolio['current_value'];
  		$old_rate = $row_portfolio['new_rate'];
  		$name = str_replace(' ','',$row_portfolio["name"]);
        $new_rate = $_SESSION[strtolower($name)];
  		$current_value = ($old_value * $new_rate)/$old_rate;
  		$id=$row_portfolio['id'];
  		$update = "UPDATE portfolio SET old_value = '$old_value', old_rate = '$old_rate',  new_rate = '$new_rate', current_value = '$current_value' WHERE id='$id';";
  		//print_r($update);die;
  		$conn->query($update);

  	}
	return $row_result_investment;
}


function getAssociatedRows($sql, $conn) {
	$result = $conn->query($sql);
	$json   = [];
	while($row = $result->fetch_assoc()){
		$json[] = $row;
	}
	return $json;
}


function getInvestedAmount($user_id,$conn){
	$sql = "SELECT * FROM portfolio WHERE user_id='$user_id'";
	$result = $conn->query($sql);
	$json   = []; $total_investment = 0;
	while($row = $result->fetch_assoc()){
		$json[] = $row;
	}

	$investments = 0;$current_value=0;
	if(!empty($json)){
		$investments_arr = array_column($json, 'invested_value');
		$investments = array_sum($investments_arr);
		$current_value_arr = array_column($json, 'current_value');
		$current_value = array_sum($current_value_arr);
	}
	$investment_arr['total'] = $investments;
	$investment_arr['current_value'] = $current_value;
	$investment_arr['details'] = $json;
	return $investment_arr;
}

function getHappinessQuotient($user_id,$conn){
	$sql = "SELECT SUM(happiness_quotient) FROM transactions WHERE user_id='$user_id'";
	$result = $conn->query($sql);
	$result = $result->fetch_assoc();
	$happiness_quotient = isset($result['SUM(happiness_quotient)']) ?$result['SUM(happiness_quotient)']:0;
	return $happiness_quotient;
}



?>