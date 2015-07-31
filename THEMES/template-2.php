<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{@ CONSTRUCTR_PAGE_TITLE @}}</title>
<meta name="keywords" content="{{@ CONSTRUCTR_PAGE_KEYWORDS @}}">
<meta name="description" content="{{@ CONSTRUCTR_PAGE_DESCRIPTION @}}">
<link rel="stylesheet" href="{{@ CONSTRUCTR_BASE_URL @}}/CONSTRUCTR-CMS/ASSETS/materialize/css/materialize.min.css">
<style>
div#menu ul ul {display: none;}
div#menu ul li:hover > ul {display: block;}
div#menu ul {padding: 0 10px;  list-style: none;position: relative;display: inline-table;}
div#menu ul:after {content: ""; clear: both; display: block;}
div#menu ul li {float: left;}
div#menu ul li:hover {background: #4b545f;background: linear-gradient(top, #4f5964 0%, #5f6975 40%);background: -moz-linear-gradient(top, #4f5964 0%, #5f6975 40%);background: -webkit-linear-gradient(top, #4f5964 0%,#5f6975 40%);}
div#menu ul li:hover a {color: #fff;}
div#menu ul li a {display: block; padding: 25px 40px;color: #757575; text-decoration: none;}
div#menu ul ul {background: #5f6975; border-radius: 0px; padding: 0;position: absolute; top: 100%;}
div#menu ul ul li {float: none;position: relative;}
div#menu ul ul li a {padding: 15px 40px;color: #fff;}	
div#menu ul ul li a:hover {background: #4b545f;}
div#menu ul ul ul {position: absolute; left: 100%; top:0;}
{{@ PAGE_CSS @}}
</style>
</head>
<body>

	<div id="menu">
		{{@ PAGE_NAVIGATION_UL_LI @}}
	</div>

	<div class="row">
		<div class="col s12">
    		{{@ PAGE_CONTENT_HTML @}}
		</div>
	</div>

	<div class="row center-align"><div class="col s12"><p><small><a href="http://phaziz.com" target="_blank">ConstructrCMS</small></a></p></div></div>

	<script src="{{@ CONSTRUCTR_BASE_URL @}}/CONSTRUCTR-CMS/ASSETS/jquery/jquery-2.1.4.min.js"></script>
	<script src="{{@ CONSTRUCTR_BASE_URL @}}/CONSTRUCTR-CMS/ASSETS/materialize/js/materialize.min.js"></script>

	<script>
		$(function() {
			{{@ PAGE_JS @}}
		});
	</script>

</body>
</html>