<?php

namespace UserTable;


class UserTable {
private static $usersHtml;



  public static function returnMarkup() {
    return '
    <!-- Table goes in the document BODY -->
<center>
  <h1>Users registered during smoketest</h1> <br />
<table class="gradienttable">
<tr>
	<th><p>Email address</p></th><th><p>Name of test</p></th><th><p>Time-Date</p></th>
</tr>
<tr><td><p>rmltestuser1@example.com</p></td><td><p>Created by default</p></td><td><p>N/A</p></td></tr>
<tr><td><p>rmltestuser2@example.com</p></td><td><p>Created by default</p></td><td><p>N/A</p></td></tr>
<tr><td><p>rmltestuser3@example.com</p></td><td><p>Created by default</p></td><td><p>N/A</p></td></tr>
<tr><td><p>rmltestuser4@example.com</p></td><td><p>Created by default</p></td><td><p>N/A</p></td></tr>

'.self::$usersHtml.'

</table>

</center>
    ';


  }

 public static function addRow($email,$testName,$date) {
$html = '<tr><td><p>'.$email.'</p></td><td><p>'.$testName.'</p></td><td><p>'.$date.'</p></td></tr>';
self::$usersHtml .= $html;
$file = '../reports/CreatedUsers.html';
$current = self::returnHTML();
file_put_contents($file, $current);
 }

  public static function returnHTML() {
    $html = self::returnHtmlStyle() . self::returnMarkup();
    return $html;
  }


  public static function returnHtmlStyle() {
    return '
<!-- CSS goes in the document HEAD or added to your external stylesheet -->
<style type="text/css">
table.gradienttable {
	font-family: verdana,arial,sans-serif;
	font-size:11px;
	color:#333333;
	border-width: 1px;
	border-color: #999999;
	border-collapse: collapse;
}
table.gradienttable th {
	padding: px;
	background: #d5e3e4;
	background: -moz-linear-gradient(top,  #d5e3e4 0%, #ccdee0 40%, #b3c8cc 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#d5e3e4), color-stop(40%,#ccdee0), color-stop(100%,#b3c8cc));
	background: -webkit-linear-gradient(top,  #d5e3e4 0%,#ccdee0 40%,#b3c8cc 100%);
	background: -o-linear-gradient(top,  #d5e3e4 0%,#ccdee0 40%,#b3c8cc 100%);
	background: -ms-linear-gradient(top,  #d5e3e4 0%,#ccdee0 40%,#b3c8cc 100%);
	background: linear-gradient(to bottom,  #d5e3e4 0%,#ccdee0 40%,#b3c8cc 100%);
	border: 1px solid #999999;
}
table.gradienttable td {
	padding: px;
	background: #ebecda;
	background: -moz-linear-gradient(top,  #ebecda 0%, #e0e0c6 40%, #ceceb7 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ebecda), color-stop(40%,#e0e0c6), color-stop(100%,#ceceb7));
	background: -webkit-linear-gradient(top,  #ebecda 0%,#e0e0c6 40%,#ceceb7 100%);
	background: -o-linear-gradient(top,  #ebecda 0%,#e0e0c6 40%,#ceceb7 100%);
	background: -ms-linear-gradient(top,  #ebecda 0%,#e0e0c6 40%,#ceceb7 100%);
	background: linear-gradient(to bottom,  #ebecda 0%,#e0e0c6 40%,#ceceb7 100%);
	border: 1px solid #999999;
}
table.gradienttable th p{
	margin:0px;
	padding:20px;
	border-top: 1px solid #eefafc;
	border-bottom:0px;
	border-left: 1px solid #eefafc;
	border-right:0px;
}
table.gradienttable td p{
	margin:0px;
	padding:20px;
	border-top: 1px solid #fcfdec;
	border-bottom:0px;
	border-left: 1px solid #fcfdec;;
	border-right:0px;
}
</style>
';
  }

}