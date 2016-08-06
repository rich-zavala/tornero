<table cellpadding="0" cellspacing="0" width="100%" class="header_important">
  <tr>
    <td style="background-image:url(images/header_01.png); background-repeat:no-repeat"><img src="imagenes/comuni-k_ind.png" /></td>
    <td width="100%" style="text-align:center" valign="middle"><iframe src="KREATOR-top.php?section=<?=$_GET[section] ?>" style="margin:0px; padding:0px; width:100%; border:0px; height:90px;" frameborder="0" allowtransparency="1" scrolling="auto"></iframe></td>
    <td style="padding-top:5px; padding-bottom:-5px"><table id="Table_01" width="288" height="85" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td colspan="5"><img src="images/user_01.png" width="288" height="1"></td>
        </tr>
        <tr>
          <td rowspan="4"><img src="images/user_02.png" width="43" height="83"></td>
          <td colspan="2" width="217" height="41" bgcolor="#FFFFFF" style="text-align:center"><span style="color:#023244; font-weight:bold; font-size:14px">
            <?=$_SESSION[nombre] ?>
            </span><br />
            <span style="color:#333">
            <?=$_SESSION[tipousuario] ?>
            </span></td>
          <td colspan="2" rowspan="2" background="images/user_04.png" width="28" height="49"><div style="position:relative; width:28px; height:49px"> <a href="comuni-k.php?section=usuarios_perfil&id_usuario=<?=$_SESSION['id_usuario'] ?>" style="position:absolute; left:-3px; top:3px"><img src="imagenes/user_profile.png"></a>
              <?php if ($_SESSION[id_tipousuario] == 1) { ?>
              <a href="?section=vars" style="position:absolute; left:-5px; top:22px"><img src="imagenes/vars.png" /></a>
              <?php
} ?>
            </div></td>
        </tr>
        <tr>
          <td colspan="2"><img src="images/user_05.png" width="217" height="8"></td>
        </tr>
        <tr>
          <td rowspan="2"><img src="images/user_06.png" width="131" height="34"></td>
          <td colspan="2"><a href="index.php"><img src="images/user_07.png" width="107" height="25"></a></td>
          <td rowspan="2"><img src="images/user_08.png" width="7" height="34"></td>
        </tr>
        <tr>
          <td colspan="2"><img src="images/user_09.png" width="107" height="9"></td>
        </tr>
        <tr>
          <td><img src="images/spacer.gif" width="43" height="1"></td>
          <td><img src="images/spacer.gif" width="131" height="1"></td>
          <td><img src="images/spacer.gif" width="86" height="1"></td>
          <td><img src="images/spacer.gif" width="21" height="1"></td>
          <td><img src="images/spacer.gif" width="7" height="1"></td>
        </tr>
      </table></td>
  </tr>
  <tr>
    <td colspan="3" style="padding-left:20px; height:40px;" class="header_h1"><h1><span id="header_title"></span></h1>
      <div style="width:206px; height:20px; position:absolute; right:0px; top:91px; background-image:url(imagenes/timer.png); background-repeat:no-repeat; padding-top:2px"> <span id="servertime" style="padding-left:46px;"></span> </div>
      </div></td>
  </tr>
</table>