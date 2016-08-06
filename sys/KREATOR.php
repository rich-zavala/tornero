<?php
include("KREATOR-USUARIOS.php");
if(!isset($o)) $o = array(); //???
if(!isset($_GET['small']))
{
  echo "<table border='0' class='centrado'><tr><td class='text-center'>";
	foreach($o AS $s)
	{
		if($s[3]>-1)
		{
			$goto1 = null;
			if($s[3]=="1")
				$goto1 = "onclick=\"return hs.htmlExpand(this, { objectType: 'ajax', width:{$s[4]}, heigth:{$s[5]}, headingText:'{$s[1]}'})\"";
			
			echo "
			<a class='kreator_element' href=\"{$s[0]}\" id=\"link{$s[2]}\" {$goto1}>
				<table width=\"100%\" height=\"100%\" >
					<tr>
						<td valign=\"middle\">
							<img src=\"iconos/{$s[2]}.png\" />
						</td>
					</tr>
					<tr>
						<td height='30px' valign=\"middle\">
							<b>{$s[1]}</b>
						</td>
					</tr>
				</table>
			</a>\n\t";
		}
	}
  echo "</tr></td></table>";
}
//Panel Express
else
{
	$o[] = $panel;
?>
<div class="text-center">
<?php
	foreach($o as $s){
		$goto1 = null;
		if($s[3]=="1")
			$goto1 = "onclick=\"return hs.htmlExpand(this, { objectType: 'ajax', width:{$s[4]}, heigth:{$s[5]}, headingText:'{$s[1]}'})\"";
?>
	<a class="kreator_small" href="<?=$s[0]?>" id="link<?=$s[2]?>" <?=$goto1?>>
			<img src="iconos-mini/<?=$s[2]?>.png" />
			<br />
			<?=$s[1]?>
  </a>
<?php	
	}
?>
</div>
<?php
}
?>