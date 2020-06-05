

Name:<?php echo "$name" ?><br/>
Sub:<?php echo "$sub" ?><br/>
Email:<?php echo "$email" ?><br/>
Token:<?php echo "$token" ?><br/>
<a href="/index.html?m=oidc">Try again</a>
<?php
foreach ($_REQUEST as $key=>$value) {
  echo "$key = " . urldecode($value) . "<br />\n";
  }
  ?>
<br/>

Log:<?php echo "$error" ?><br/>