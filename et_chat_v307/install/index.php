<?php

// Check Server param
if (version_compare(phpversion(), '5.1.2', '<')) echo "<div style=\"color:red\">FEHLER!!!<br><br>PHP Version = ".phpversion()." (sollte jedoch >= 5.1.2 sein!)</div>";
else		
// redirect
header('Location: ../?InstallIndex');