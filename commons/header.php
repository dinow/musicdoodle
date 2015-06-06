<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/musicdoodle.css" rel="stylesheet">
    <style>
  		body {
    		padding-top: 60px;
  		}
	</style>
</head>
<body>
<div class="container">
	<nav id="navbar" class="navbar navbar-default navbar-fixed-top" role = "naviation">
		<div class="container">
		   <div class="navbar-header">
		        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
		            <span class="sr-only">Toggle navigation</span>
		            <span class="icon-bar"></span>
		            <span class="icon-bar"></span>
		            <span class="icon-bar"></span>
		        </button>
		        <a class="navbar-brand" href="#">MusicDoodle, V0.01beta</a>
		    </div>
    		<div class="navbar-collapse collapse">
		        <ul class="nav navbar-nav">
	              <li><a href="#vote">Voter</a></li>
	              <li><a href="#viewRanking">Voir classement</a></li>
	              <li><a href="#viewPlayList">Voir playlist&nbsp;<span class="badge">4</span></a></li>
	              <li id="scanDir"><a href="#">Actualiser BDD</a></li>
	            </ul>
    		</div>
    	</div>
    </nav>