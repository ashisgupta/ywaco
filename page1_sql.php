<?php
//read investment
$investment_options = "SELECT * FROM investment_options";
$result_investment_options = $conn->query($investment_options);
//read expenses
$expense_options = "SELECT * FROM expense_options";
//read all transactions
$transactions = "SELECT expense_investment_name, SUM(buy_sell_amount) FROM transactions WHERE user_id='$current_user_id' GROUP BY expense_investment_name";

$buy_sell_amount_sum = "SELECT SUM(buy_sell_amount) FROM transactions WHERE user_id='$current_user_id'";
$result_buy_sell_amount_sum = $conn->query($buy_sell_amount_sum);
$row_buy_sell_amount_sum = $result_buy_sell_amount_sum->fetch_row();
$row_buy_sell_amount_sum = isset($row_buy_sell_amount_sum[0]) ? $row_buy_sell_amount_sum[0]:0;
$cash_in_hand = $_SESSION["first_cash_in_hand"]+$row_buy_sell_amount_sum;



?>