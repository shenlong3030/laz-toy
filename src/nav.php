<style type="text/css">
	.nav{
      display: inline-block;
      padding-right: 10px;
      float: right;
      color: red;
    }
</style>

<div class="nav" style="width: 100%;padding-bottom: 10px;border-bottom: 1px solid lightgrey">
    <div class="nav logout-link"><a onclick="logout()" href="#">Logout</a><br/></div>
    <div class="nav token-link"><a onclick="showToken()" href="#">Show token</a></div>
    <div class="nav account"><?php echo $GLOBALS["account"]?></div>
</div>