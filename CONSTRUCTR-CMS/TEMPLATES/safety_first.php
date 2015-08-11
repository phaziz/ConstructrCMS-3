<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Constructr Administration</title>
<link rel="stylesheet" href="{{ @CONSTRUCTR_BASE_URL}}/CONSTRUCTR-CMS/ASSETS/css/constructr.css">
<link rel="stylesheet" href="{{ @CONSTRUCTR_BASE_URL}}/CONSTRUCTR-CMS/ASSETS/materialize/css/materialize.min.css">
</head>
<body>
	<include href="{{ @NAVIGATION }}" />
    <div class="row">
        <div class="col s12 pagegen">
            <check if="{{ @NAVIGATION }} != ''">
                <true>
					{{ html_entity_decode(@NAVIGATION) }}
                </true>
                <false>
                    <p><strong>0 Seiten vorhanden&#160;&#160;&#160;<a href="{{ @CONSTRUCTR_BASE_URL}}/constructr/pagemanagement/drag-n-drop" class="waves-effect waves-light btn"><i class="mdi-av-shuffle right"></i>Drag'N'Drop</a>&#160;&#160;&#160;<a href="{{ @CONSTRUCTR_BASE_URL}}/constructr/pagemanagement/new" class="waves-effect waves-light btn"><i class="mdi-content-add-circle right"></i>Neue Seite</a></strong></p>
                </false>
            </check>
        </div>
    </div>

	<div class="row"><div class="col s12 center-align"><p><small><a href="http://phaziz.com" target="_blank">ConstructrCMS Version {{ @CONSTRUCTR_VERSION }}</small></a></p></div></div>
    <script src="{{ @CONSTRUCTR_BASE_URL}}/CONSTRUCTR-CMS/ASSETS/jquery/jquery-2.1.4.min.js"></script>
    <script src="{{ @CONSTRUCTR_BASE_URL}}/CONSTRUCTR-CMS/ASSETS/materialize/js/materialize.min.js"></script>
    <script>

        $(function() {
        	$(".button-collapse").sideNav();
        });
    </script>
</body>
</html>
