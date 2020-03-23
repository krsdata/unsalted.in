<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>SPORTSFIGHT Join Invitation</title>
<style type="text/css">
 
table {
    border-collapse: collapse;
    border-color:#ccc;
     font-family:Arial, Helvetica, sans-serif ;
}

</style>
</head>
<body style="width: 85%; margin: auto;" >
<table width="81%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
     
      <td align="center" valign="middle" bgcolor="" style="background-color: #dc3545;padding: 20px;color: #332c41;font-size: 28px;font-family: fantasy;color: #fff;">
      <div style="font-size: 24px">SPORTS FIGHT</div>
    </td>
  </tr>
</table>
  <table width="81%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#971800" style="background-color:#fff;">
      <tr>
           <td align="center" valign="top" bgcolor="aliceblue">
            <table width="95%" border="0" cellspacing="0" cellpadding="10" style="margin-bottom:10px;">
              <tr>
                <td align="left" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:16px; color:#000;"> 
                  <div style="
    padding: 20px;">
                      <p><b>{{ucfirst($content['receipent_name'])}}</b></p>

                      <p> {{ ucwords($content['invite_by'])}} has invited you to join a SportsFight. <a href="{!! $content['download_link']!!}">Click here to join</a>!</p>
                      <div>Use Referal Code : <b>{{$content['referal_code']??'SP2020'}} <b></div>
                      <p>Enjoy! <p> 
                        <p>Regards</p>
                      <p><b>Team, <br> Sports Fight</b></p>
                  </div>
                </td>
              </tr>
            </table> 
        </td>
      </tr>
  </table>
</body>
</html>
