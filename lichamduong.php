<?php
//Chuyen doi ngay
//Lay Ngày Julius
function getJulius($intNgay, $intThang, $intNam)
{
	$a = (14 - $intThang) / 12;
	$y = $intNam + 4800 - $a;
	$m = $intThang + 12 * $a - 3;
	$jd = $intNgay +floor((153*$m + 2)/5)+365*$y+floor($y/4)-floor($y/100)+floor($y/400)-32045;	
	if ($jd < 2299161)
	{
		$jd = $intNgay + floor((153 * $m + 2)/5) + 365 * $y +floor($y / 4)-32083;
	}
	return $jd;
}
// Tinh ngay Soc
function getNewMoonDay($k){
	$PI = pi();
	// $T, $T2, $T3, $dr, $jd1, $M, $Mpr, $F, $C1, $deltat, $jdNew;
	$T = $k / 1236.85;
	$T2 = $T * $T;
	$T3 = $T2 * $T;
	$dr = $PI / 180;
	$timeZone = 7.0;
	$jd1 = 2415020.75933 + 29.53058868 * $k + 0.0001178 * $T2 - 0.000000155 * $T3;
	// Mean new moon
	$jd1 = $jd1 + 0.00033 * sin((166.56 + 132.87 * $T - 0.009173 * $T2) * $dr);
	// Sun's mean anomaly
	$M = 359.2242 + 29.10535608 * $k - 0.0000333 * $T2 - 0.00000347 * $T3;
	// Moon's mean anomaly
	$Mpr = 306.0253 + 385.81691806 * $k + 0.0107306 * $T2 + 0.00001236 * $T3;
	// Moon's argument of latitude
	$F = 21.2964 + 390.67050646 * $k - 0.0016528 * $T2 - 0.00000239 * $T3;
	$C1 = (0.1734 - 0.000393 * $T) * sin($M * $dr) + 0.0021 * sin(2 * $dr * $M);
	$C1 = $C1 - 0.4068 * sin($Mpr * $dr) + 0.0161 * sin($dr * 2 * $Mpr);
	$C1 = $C1 - 0.0004 * sin($dr * 3 * $Mpr);
	$C1 = $C1 + 0.0104 * sin($dr * 2 * $F) - 0.0051 * sin($dr * ($M + $Mpr));
	$C1 = $C1 - 0.0074 * sin($dr * ($M - $Mpr)) + 0.0004 * sin($dr * (2 * $F + $M));
	$C1 = $C1 - 0.0004 * sin($dr * (2 * $F - $M)) - 0.0006 * sin($dr * (2 * $F + $Mpr));
	$C1 = $C1 + 0.0010 * sin($dr * (2 * $F - $Mpr)) + 0.0005 * sin($dr * (2 * $Mpr + $M));
	if ($T < -11)
	{
		$deltat = 0.001 + 0.000839 * $T + 0.0002261 * $T2 - 0.00000845 * $T3 - 0.000000081 * $T * $T3;
	}
	else
	{
		$deltat = -0.000278 + 0.000265 * $T + 0.000262 * $T2;
	}
	$jdNew = $jd1 + $C1 - $deltat;
	return floor($jdNew + 0.5 + $timeZone / 24);
}

function getSunLongitude($jdn){
	// $timeZone = 7.0;
	$PI = 3.14;
	// $T, $T2, $dr, $M, $L0, $DL, $L;
	// Time in Julian centuries from 2000-01-01 12:00:00 GMT
	$T = ($jdn - 2451545.5 - $timeZone / 24) / 36525;
	$T2 = $T * $T;
	// degree to radian
	$dr = $PI / 180;
	// mean anomaly, degree
	$M = 357.52910 + 35999.05030 * $T - 0.0001559 * $T2 - 0.00000048 * $T * $T2;
	// mean longitude, degree
	$L0 = 280.46645 + 36000.76983 * $T + 0.0003032 * $T2;
	$DL = (1.914600 - 0.004817 * $T - 0.000014 * $T2) * sin($dr * $M);
	$DL = $DL + (0.019993 - 0.000101 * $T) * sin($dr * 2 * $M) + 0.000290 * sin($dr * 3 * $M);
	$L = $L0 + $DL; // true longitude, degree
	$L = $L * $dr;
	// Normalize to (0, 2*$PI)
	$L = $L - $PI * 2 * floor($L / ($PI * 2));
	return floor($L / $PI * 6);
}
// Tìm ngày bat dau tháng 11 am lich
function getLunarMonthll($intNam){
	// $k, $off, $nm, $sunLong;
	$off = getJulius(31, 12, $intNam) - 2415021;
	$k = floor($off / 29.530588853);
	$nm = getNewMoonDay(floor($k));
	// sun longitude at local midnight
	$sunLong = getSunLongitude(floor($nm));
	if ($sunLong >= 9)
	{
		$nm = getNewMoonDay(floor($k) - 1);
	}
	return floor($nm);
}
//Xác dinh thang nhuan
function getLeapMonthOffset($a11){
// $last, $arc;
// $k, $i;
	$k = floor(($a11 - 2415021.076998695) / 29.530588853 + 0.5);
	$last = 0;
// We start with the month following lunar month 11
	$i = 1;
	$arc = getSunLongitude(floor(getNewMoonDay((floor($k + $i)))));
	do
	{
		$last = $arc;
		$i++;
		$arc = getSunLongitude(floor(getNewMoonDay((floor($k + $i)))));
	} while ($arc != $last && $i < 14);
	return $i - 1;
}
//Doi ra ngay am-duong
function convertSolar2Lunar($intNgay, $intThang, $intNam){
	// $dayNumber, $monthStart, $a11, $b11, $lunarDay, $lunarMonth, $lunarYear;
	//// lunarLeap;
	// $k, $diff;
	$dayNumber = getJulius($intNgay,$intThang,$intNam);
	$k = floor(($dayNumber - 2415021.076998695) / 29.530588853);
	$monthStart = getNewMoonDay($k + 1);
	if ($monthStart > $dayNumber)
	{
		$monthStart = getNewMoonDay($k);
	}
	$a11 = getLunarMonthll($intNam);
	$b11 = $a11;
	if ($a11 >= $monthStart)
	{
		$lunarYear = $intNam;
		$a11 = getLunarMonthll($intNam - 1);
	}
	else
	{
		$lunarYear = $intNam + 1;
		$b11 = getLunarMonthll($intNam + 1);
	}
	$lunarDay = $dayNumber - $monthStart + 1;
	$diff = floor(($monthStart - $a11) / 29);
	//lunarLeap = 0;
	$lunarMonth = $diff + 11;
	if ($b11 - $a11 > 365)
	{
		$leapMonthDiff = getLeapMonthOffset($a11);
		if ($diff >= $leapMonthDiff)
		{
			$lunarMonth = $diff + 10;
			if ($diff == $leapMonthDiff)
			{
		//lunarLeap = 1;
			}
		}
	}
	if ($lunarMonth > 12)
	{
		$lunarMonth = $lunarMonth - 12;
	}
	if ($lunarMonth >= 11 && $diff < 4)
	{
		$lunarYear -= 1;
	}

	return  floor($lunarDay)."-".$lunarMonth."-".$lunarYear; 
}
if($_POST["date"])
	list($year,$month,$date) = explode("-", $_POST["date"]);
else
	list($year,$month,$date) = explode("-", Date("Y-m-d"));
//echo $year;
$_SESSION["amlich"] = convertSolar2Lunar($date,$month,$year);

?>








<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Xem lịch âm hôm nay</title>

    <!-- Bootstrap -->
    <!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

	<!-- Latest compiled and minified JavaScript -->
	
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <h1>Chào bạn! chúc bạn một ngày mới tốt lành</h1>    
	<div class="container">
	
		<form action="lichamduong.php" method="POST" class="form-horizontal">
		<fieldset>
		<!-- Form Name -->
		<legend>Xem lịch âm</legend>

		<!-- Text input-->
		<div class="form-group form-group-lg">
		  <label class="col-md-4 control-label" for="date">Nhập ngày cần xem</label>  
		  <div class="col-md-8">
		  <input id="date" name="date" type="date" class="form-control input-md" aria-describedby="inputSuccess4Status">		  
    		<span class="glyphicon glyphicon-ok form-control-feedback" aria-hidden="true"></span>
    		<span id="inputSuccess4Status" class="sr-only">(success)</span>
		    
		  </div>
		</div>

		<!-- Button -->
		<div class="form-group">
		  <label class="col-md-4 control-label" for="submit"></label>
		  <div class="col-md-8">
		    <button id="submit" name="submit" class="btn btn-success"> Xem  </button>
		  </div>
		</div>

		</fieldset>
		</form>
	
	<H1>Ngày âm là: <?php if($_SESSION["amlich"]) echo $_SESSION["amlich"];?></H1>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    </div><!--end container-->
  </body>
</html>






