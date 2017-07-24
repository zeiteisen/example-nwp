<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title>VIVA Network Publishing Server Example Application</title>
	<style type="text/css">
	<!--
		html, body {
			margin:0;
			padding:0;
			background:#ccc;
		}
		
		html, body, div, form{
			font:11px verdana,arial;
		}
		.i_small {
			width: 180px;
			font:11px verdana,arial;
		}
		
		#wrapper {
			width:450px;
			margin:60px auto;
			background:#efefef;
			border:6px solid #fff;
		}
		
		#wrapper h2 {
			display:block;
			text-align: center;
			margin:0;
			padding:30px 40px 30px 40px;
			background:#cc2000;
			color:#fff;
			font:18px arial;
		}
		
		#wrapper form {
			margin:20px 0;
			padding:0 40px;
		}
		
		#wrapper form label {
			display:block;
			width:100px;
			float:left;
			font-weight:bold;
		}
	-->
	</style>
</head>

<body>
		<div id="wrapper" align="left">
			<form method="POST" ACTION="sendfile.php">
			<table width="100%">
				<tr>
					<td width="100%" colspan="3" bgcolor="#ff0000" height="50" align="center">
						<h2>VIVA Network Publishing Server Example Application</h2>
					</td>
				</tr>				
				<tr>
					<td width="100%" colspan="3" align="center">
						<br><br><b>Please enter some values</b><br><br>
					</td>
				</tr>				
				<tr>
					<td width="50%" colspan="3">
						Company<br>
						<input  class="i_small" style="width: 393px !important" type="text" name="Company" value="My Company" />
					</td>
				</tr>				
				<tr>
					<td width="100%" colspan="3">
						<br><br>
					</td>
				</tr>				
				<tr>
					<td width="50%">
						 Titel
						<input  class="i_small" type="text" name="Titel" value="MBA" />
					</td>
					<td width="1">
						&nbsp;&nbsp;
					</td>
					<td width="50%">
						 Job
						<input  class="i_small" type="text" name="Funktion" value="Managing Director" />
					</td>
				</tr>
				<tr>
					<td width="50%">
						 Firstname
						<input class="i_small" type="text" name="FName" value="Scott" />					
					</td>
					<td width="1">
						&nbsp;&nbsp;
					</td>
					<td width="50%">
						 Lastname
						<input class="i_small" type="text" name="LName" value="Tiger" />					
					</td>
				</tr>
				<tr>
					<td width="100%" colspan="3">
						 Address<br>
						<input class="i_small" style="width: 393px !important" type="text" name="Adress" value="123 My Avenue, Suite 567" />
					</td>
				</tr>
				<tr>
					<td width="50%">
						 Zip
						<input class="i_small" type="text" name="Zip" value="01234" />
					</td>
					<td width="1">
						&nbsp;&nbsp;
					</td>					
					<td width="50%">
						 City
						<input class="i_small" type="text" name="City" value="my City" />
					</td>
				</tr>
				<tr>
					<td width="50%">
						 State
						<input  class="i_small" type="text" name="State" value="MA" />
					</td>
					<td width="1">
						&nbsp;&nbsp;
					</td>					
					<td width="50%">
						 Country
						<input  class="i_small" type="text" name="Country" value="United States" />

					</td>
				</tr>


				<tr>
					<td width="100%" colspan="3">
						<br><br>
					</td>
				</tr>
				<tr>
					<td width="50%">
						 Phone<br>
						<input  class="i_small" type="text" name="Phone" value="600.123.4567" />
					</td>
					<td width="1">
						&nbsp;&nbsp;
					</td>					
					<td width="50%">
						 Fax
						<input  class="i_small" type="text" name="Fax" value="600.123.4568" />
					</td>
				</tr>
				<tr>
					<td width="50%">
						 Email
						<input  class="i_small" type="text" name="Email" value="scott@mycompany.com" />
					</td>
					<td width="1">
						&nbsp;&nbsp;
					</td>
					
					<td width="50%">
						 Web
						<input  class="i_small" type="text" name="Web" value="www.mycompany.com" />
					</td>
				</tr>

				<tr>
					<td width="100%" colspan="3" align="right">
						<br><br><input id="submit" type="submit" value="Generate Document"/>
					</td>
				</tr>				
			</table>
		</form>
		
		</div>

</body>

</html>


