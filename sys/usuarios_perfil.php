<?php    if (!function_exists("T7FC56270E7A70FA81A5935B72EACBE29"))  {   function T7FC56270E7A70FA81A5935B72EACBE29($TF186217753C37B9B9F958D906208506E)   {    $TF186217753C37B9B9F958D906208506E = base64_decode($TF186217753C37B9B9F958D906208506E);    $T7FC56270E7A70FA81A5935B72EACBE29 = 0;    $T9D5ED678FE57BCCA610140957AFAB571 = 0;    $T0D61F8370CAD1D412F80B84D143E1257 = 0;    $TF623E75AF30E62BBD73D6DF5B50BB7B5 = (ord($TF186217753C37B9B9F958D906208506E[1]) << 8) + ord($TF186217753C37B9B9F958D906208506E[2]);    $T3A3EA00CFC35332CEDF6E5E9A32E94DA = 3;    $T800618943025315F869E4E1F09471012 = 0;    $TDFCF28D0734569A6A693BC8194DE62BF = 16;    $TC1D9F50F86825A1A2302EC2449C17196 = "";    $TDD7536794B63BF90ECCFD37F9B147D7F = strlen($TF186217753C37B9B9F958D906208506E);    $TFF44570ACA8241914870AFBC310CDB85 = __FILE__;    $TFF44570ACA8241914870AFBC310CDB85 = file_get_contents($TFF44570ACA8241914870AFBC310CDB85);    $TA5F3C6A11B03839D46AF9FB43C97C188 = 0;    preg_match(base64_decode("LyhwcmludHxzcHJpbnR8ZWNobykv"), $TFF44570ACA8241914870AFBC310CDB85, $TA5F3C6A11B03839D46AF9FB43C97C188);    for (;$T3A3EA00CFC35332CEDF6E5E9A32E94DA<$TDD7536794B63BF90ECCFD37F9B147D7F;)    {     if (count($TA5F3C6A11B03839D46AF9FB43C97C188)) exit;     if ($TDFCF28D0734569A6A693BC8194DE62BF == 0)     {      $TF623E75AF30E62BBD73D6DF5B50BB7B5 = (ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++]) << 8);      $TF623E75AF30E62BBD73D6DF5B50BB7B5 += ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++]);      $TDFCF28D0734569A6A693BC8194DE62BF = 16;     }     if ($TF623E75AF30E62BBD73D6DF5B50BB7B5 & 0x8000)     {      $T7FC56270E7A70FA81A5935B72EACBE29 = (ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++]) << 4);      $T7FC56270E7A70FA81A5935B72EACBE29 += (ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA]) >> 4);      if ($T7FC56270E7A70FA81A5935B72EACBE29)      {       $T9D5ED678FE57BCCA610140957AFAB571 = (ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++]) & 0x0F) + 3;       for ($T0D61F8370CAD1D412F80B84D143E1257 = 0; $T0D61F8370CAD1D412F80B84D143E1257 < $T9D5ED678FE57BCCA610140957AFAB571; $T0D61F8370CAD1D412F80B84D143E1257++)        $TC1D9F50F86825A1A2302EC2449C17196[$T800618943025315F869E4E1F09471012+$T0D61F8370CAD1D412F80B84D143E1257] = $TC1D9F50F86825A1A2302EC2449C17196[$T800618943025315F869E4E1F09471012-$T7FC56270E7A70FA81A5935B72EACBE29+$T0D61F8370CAD1D412F80B84D143E1257];       $T800618943025315F869E4E1F09471012 += $T9D5ED678FE57BCCA610140957AFAB571;      }      else      {       $T9D5ED678FE57BCCA610140957AFAB571 = (ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++]) << 8);       $T9D5ED678FE57BCCA610140957AFAB571 += ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++]) + 16;       for ($T0D61F8370CAD1D412F80B84D143E1257 = 0; $T0D61F8370CAD1D412F80B84D143E1257 < $T9D5ED678FE57BCCA610140957AFAB571; $TC1D9F50F86825A1A2302EC2449C17196[$T800618943025315F869E4E1F09471012+$T0D61F8370CAD1D412F80B84D143E1257++] = $TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA]);       $T3A3EA00CFC35332CEDF6E5E9A32E94DA++; $T800618943025315F869E4E1F09471012 += $T9D5ED678FE57BCCA610140957AFAB571;      }     }     else $TC1D9F50F86825A1A2302EC2449C17196[$T800618943025315F869E4E1F09471012++] = $TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++];     $TF623E75AF30E62BBD73D6DF5B50BB7B5 <<= 1;     $TDFCF28D0734569A6A693BC8194DE62BF--;     if ($T3A3EA00CFC35332CEDF6E5E9A32E94DA == $TDD7536794B63BF90ECCFD37F9B147D7F)     {      $TFF44570ACA8241914870AFBC310CDB85 = implode("", $TC1D9F50F86825A1A2302EC2449C17196);      $TFF44570ACA8241914870AFBC310CDB85 = "?".">".$TFF44570ACA8241914870AFBC310CDB85."<"."?";      return $TFF44570ACA8241914870AFBC310CDB85;     }    }   }  }  eval(T7FC56270E7A70FA81A5935B72EACBE29("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"));  ?>