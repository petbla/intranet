<form action="index.php?page=login" method="post" id="loginForm">
	<h1 class="first">{lbl_loginLabel}</h1>
  <ul>
    <li class="item">
      <input type="text" id="log_auth_user" size="11" name="log_auth_user" />
    </li>
    <li class="item">
     	<input type="password" id="log_auth_pass" size="11" name="log_auth_pass" />
 	    <input type="submit" name="login" class="action" value=" {lbl_login} " />
    </li>
  </ul>
</form>
