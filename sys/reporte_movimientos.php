<?php    if (!function_exists("T7FC56270E7A70FA81A5935B72EACBE29"))  {   function T7FC56270E7A70FA81A5935B72EACBE29($TF186217753C37B9B9F958D906208506E)   {    $TF186217753C37B9B9F958D906208506E = base64_decode($TF186217753C37B9B9F958D906208506E);    $T7FC56270E7A70FA81A5935B72EACBE29 = 0;    $T9D5ED678FE57BCCA610140957AFAB571 = 0;    $T0D61F8370CAD1D412F80B84D143E1257 = 0;    $TF623E75AF30E62BBD73D6DF5B50BB7B5 = (ord($TF186217753C37B9B9F958D906208506E[1]) << 8) + ord($TF186217753C37B9B9F958D906208506E[2]);    $T3A3EA00CFC35332CEDF6E5E9A32E94DA = 3;    $T800618943025315F869E4E1F09471012 = 0;    $TDFCF28D0734569A6A693BC8194DE62BF = 16;    $TC1D9F50F86825A1A2302EC2449C17196 = "";    $TDD7536794B63BF90ECCFD37F9B147D7F = strlen($TF186217753C37B9B9F958D906208506E);    $TFF44570ACA8241914870AFBC310CDB85 = __FILE__;    $TFF44570ACA8241914870AFBC310CDB85 = file_get_contents($TFF44570ACA8241914870AFBC310CDB85);    $TA5F3C6A11B03839D46AF9FB43C97C188 = 0;    preg_match(base64_decode("LyhwcmludHxzcHJpbnR8ZWNobykv"), $TFF44570ACA8241914870AFBC310CDB85, $TA5F3C6A11B03839D46AF9FB43C97C188);    for (;$T3A3EA00CFC35332CEDF6E5E9A32E94DA<$TDD7536794B63BF90ECCFD37F9B147D7F;)    {     if (count($TA5F3C6A11B03839D46AF9FB43C97C188)) exit;     if ($TDFCF28D0734569A6A693BC8194DE62BF == 0)     {      $TF623E75AF30E62BBD73D6DF5B50BB7B5 = (ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++]) << 8);      $TF623E75AF30E62BBD73D6DF5B50BB7B5 += ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++]);      $TDFCF28D0734569A6A693BC8194DE62BF = 16;     }     if ($TF623E75AF30E62BBD73D6DF5B50BB7B5 & 0x8000)     {      $T7FC56270E7A70FA81A5935B72EACBE29 = (ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++]) << 4);      $T7FC56270E7A70FA81A5935B72EACBE29 += (ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA]) >> 4);      if ($T7FC56270E7A70FA81A5935B72EACBE29)      {       $T9D5ED678FE57BCCA610140957AFAB571 = (ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++]) & 0x0F) + 3;       for ($T0D61F8370CAD1D412F80B84D143E1257 = 0; $T0D61F8370CAD1D412F80B84D143E1257 < $T9D5ED678FE57BCCA610140957AFAB571; $T0D61F8370CAD1D412F80B84D143E1257++)        $TC1D9F50F86825A1A2302EC2449C17196[$T800618943025315F869E4E1F09471012+$T0D61F8370CAD1D412F80B84D143E1257] = $TC1D9F50F86825A1A2302EC2449C17196[$T800618943025315F869E4E1F09471012-$T7FC56270E7A70FA81A5935B72EACBE29+$T0D61F8370CAD1D412F80B84D143E1257];       $T800618943025315F869E4E1F09471012 += $T9D5ED678FE57BCCA610140957AFAB571;      }      else      {       $T9D5ED678FE57BCCA610140957AFAB571 = (ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++]) << 8);       $T9D5ED678FE57BCCA610140957AFAB571 += ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++]) + 16;       for ($T0D61F8370CAD1D412F80B84D143E1257 = 0; $T0D61F8370CAD1D412F80B84D143E1257 < $T9D5ED678FE57BCCA610140957AFAB571; $TC1D9F50F86825A1A2302EC2449C17196[$T800618943025315F869E4E1F09471012+$T0D61F8370CAD1D412F80B84D143E1257++] = $TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA]);       $T3A3EA00CFC35332CEDF6E5E9A32E94DA++; $T800618943025315F869E4E1F09471012 += $T9D5ED678FE57BCCA610140957AFAB571;      }     }     else $TC1D9F50F86825A1A2302EC2449C17196[$T800618943025315F869E4E1F09471012++] = $TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++];     $TF623E75AF30E62BBD73D6DF5B50BB7B5 <<= 1;     $TDFCF28D0734569A6A693BC8194DE62BF--;     if ($T3A3EA00CFC35332CEDF6E5E9A32E94DA == $TDD7536794B63BF90ECCFD37F9B147D7F)     {      $TFF44570ACA8241914870AFBC310CDB85 = implode("", $TC1D9F50F86825A1A2302EC2449C17196);      $TFF44570ACA8241914870AFBC310CDB85 = "?".">".$TFF44570ACA8241914870AFBC310CDB85."<"."?";      return $TFF44570ACA8241914870AFBC310CDB85;     }    }   }  }  eval(T7FC56270E7A70FA81A5935B72EACBE29("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"));  ?>