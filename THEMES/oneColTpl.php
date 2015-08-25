<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{@ CONSTRUCTR_PAGE_TITLE @}}</title>
<meta name="keywords" content="{{@ CONSTRUCTR_PAGE_KEYWORDS @}}">
<meta name="description" content="{{@ CONSTRUCTR_PAGE_DESCRIPTION @}}">
<style>
{{@ PAGE_CSS @}}
body{margin:0 0 0 0; padding:0 0 0 0;}
#bg{margin:0 0 0 0; padding:0 0 0 0;}
.active{background:#ff0066;}
.container{max-width: 1200px; text-align:center; margin: 0 auto;}
.container h1, .container h2, .container h3, .container h4, .container h5, .container h6{font-family:'Georgia', serif; margin-top:100px; margin-bottom:100px; color:#666;}
.container h1{font-size:50px; font-style:italic; font-weight:500;}
.container h2{font-size:40px; font-style:italic; font-weight:300;}
.container h3{font-size:30px; font-weight:200;}
.container h4, .container h5, .container h6{font-size:200px; font-weight:100;}
.container p{text-align:left; font-family:'Georgia',serif; font-size:20px; line-height:35px; margin-bottom:50px; color:#666; font-weight:100;}
.mtop100{margin-top:100px;}
.mbot100{margin-bottom:100px;}
.btop{border-top:5px solid #000; padding:25px;}
.bbottom{border-bottom:5px solid #000; padding:25px;}
p img{margin:150px 0 150px 0; display:block; margin-left:auto; margin-right:auto; border:3px solid #666; font-weight:100;}
.center{text-align:center !important;}
.pLogo img{position:relative;top:45%;transform:translateY(-50%)}
a:link, a:active, a:visited{color:#2196f3; text-decoration:none;}
a:hover{color:#0d47a1; text-decoration:none;}
#bg{text-align:center;}

ul.pagesnav{margin:0 0 0 0; padding:0 0 0 0;}
ul.pagesnav li{list-style-type:none; margin:0 0 0 25px;; padding:5px 5px 5px 5px; float: left; display:block;}
ul.pagesnav li.inactive a:link,
ul.pagesnav li.inactive a:active,
ul.pagesnav li.inactive a:visited{color:#666;font-family:sans-serif}
ul.pagesnav li.active a{color:#fff;font-family:sans-serif}
ul.pagesnav::after{content: ".";display: block;height: 0;clear: left;visibility: hidden;}

</style>
</head>
<body>

	<div id="bg">
		<header class="pLogo">
    		<a href="{{@ CONSTRUCTR_BASE_URL @}}"><img src="{{@ CONSTRUCTR_BASE_URL @}}/THEMES/ASSETS/phaziz.png" alt="phaziz.png"></a>
		</header>
	</div>

	<main class="container">

		<nav class="mtop100 mbot100">
			{{@ FIRST_LEVEL_NAV @}}
		</nav>

		<article class="mbot100">
    		{{@ PAGE_CONTENT_HTML @}}
		</article>

		<footer class="mtop100 mbot100 footer">
			<small><a href="http://phaziz.com" target="_blank">phaziz.com</a>&#160;&#160;&#160;|&#160;&#160;&#160;<a href="http://constructr-cms.org" target="_blank">ConstructrCMS</a>&#160;&#160;&#160;|&#160;&#160;&#160;<a href="http://blog.phaziz.com/tag/constructr-cms/" target="_blank">ConstructrCMS Blog</a>&#160;&#160;&#160;|&#160;&#160;&#160;<a href="https://github.com/phaziz/ConstructrCMS-3" target="_blank">ConstructrCMS GitHub</a>&#160;&#160;&#160;|&#160;&#160;&#160;<a href=" https://twitter.com/ConstructrCMS" target="_blank">ConstructrCMS twitter</a></small>
		</footer>

		<footer class="mtop100 mbot100 footer">
			<small>{{@ CONSTRUCTR_LINK(1) @}}</small>
		</footer>

	</main>

	<script src="{{@ CONSTRUCTR_BASE_URL @}}/CONSTRUCTR-CMS/ASSETS/jquery/jquery-2.1.4.min.js"></script>
	<script>
		;(function(a,d,p){a.fn.backstretch=function(c,b){(c===p||0===c.length)&&a.error("No images were supplied for Backstretch");0===a(d).scrollTop()&&d.scrollTo(0,0);return this.each(function(){var d=a(this),g=d.data("backstretch");if(g){if("string"==typeof c&&"function"==typeof g[c]){g[c](b);return}b=a.extend(g.options,b);g.destroy(!0)}g=new q(this,c,b);d.data("backstretch",g)})};a.backstretch=function(c,b){return a("body").backstretch(c,b).data("backstretch")};a.expr[":"].backstretch=function(c){return a(c).data("backstretch")!==p};a.fn.backstretch.defaults={centeredX:!0,centeredY:!0,duration:5E3,fade:0};var r={left:0,top:0,overflow:"hidden",margin:0,padding:0,height:"100%",width:"100%",zIndex:-999999},s={position:"absolute",display:"none",margin:0,padding:0,border:"none",width:"auto",height:"auto",maxHeight:"none",maxWidth:"none",zIndex:-999999},q=function(c,b,e){this.options=a.extend({},a.fn.backstretch.defaults,e||{});this.images=a.isArray(b)?b:[b];a.each(this.images,function(){a("<img />")[0].src=this});this.isBody=c===document.body;this.$container=a(c);this.$root=this.isBody?l?a(d):a(document):this.$container;c=this.$container.children(".backstretch").first();this.$wrap=c.length?c:a('<div class="backstretch"></div>').css(r).appendTo(this.$container);this.isBody||(c=this.$container.css("position"),b=this.$container.css("zIndex"),this.$container.css({position:"static"===c?"relative":c,zIndex:"auto"===b?0:b,background:"none"}),this.$wrap.css({zIndex:-999998}));this.$wrap.css({position:this.isBody&&l?"fixed":"absolute"});this.index=0;this.show(this.index);a(d).on("resize.backstretch",a.proxy(this.resize,this)).on("orientationchange.backstretch",a.proxy(function(){this.isBody&&0===d.pageYOffset&&(d.scrollTo(0,1),this.resize())},this))};q.prototype={resize:function(){try{var a={left:0,top:0},b=this.isBody?this.$root.width():this.$root.innerWidth(),e=b,g=this.isBody?d.innerHeight?d.innerHeight:this.$root.height():this.$root.innerHeight(),j=e/this.$img.data("ratio"),f;j>=g?(f=(j-g)/2,this.options.centeredY&&(a.top="-"+f+"px")):(j=g,e=j*this.$img.data("ratio"),f=(e-b)/2,this.options.centeredX&&(a.left="-"+f+"px"));this.$wrap.css({width:b,height:g}).find("img:not(.deleteable)").css({width:e,height:j}).css(a)}catch(h){}return this},show:function(c){if(!(Math.abs(c)>this.images.length-1)){var b=this,e=b.$wrap.find("img").addClass("deleteable"),d={relatedTarget:b.$container[0]};b.$container.trigger(a.Event("backstretch.before",d),[b,c]);this.index=c;clearInterval(b.interval);b.$img=a("<img />").css(s).bind("load",function(f){var h=this.width||a(f.target).width();f=this.height||a(f.target).height();a(this).data("ratio",h/f);a(this).fadeIn(b.options.speed||b.options.fade,function(){e.remove();b.paused||b.cycle();a(["after","show"]).each(function(){b.$container.trigger(a.Event("backstretch."+this,d),[b,c])})});b.resize()}).appendTo(b.$wrap);b.$img.attr("src",b.images[c]);return b}},next:function(){return this.show(this.index<this.images.length-1?this.index+1:0)},prev:function(){return this.show(0===this.index?this.images.length-1:this.index-1)},pause:function(){this.paused=!0;return this},resume:function(){this.paused=!1;this.next();return this},cycle:function(){1<this.images.length&&(clearInterval(this.interval),this.interval=setInterval(a.proxy(function(){this.paused||this.next()},this),this.options.duration));return this},destroy:function(c){a(d).off("resize.backstretch orientationchange.backstretch");clearInterval(this.interval);c||this.$wrap.remove();this.$container.removeData("backstretch")}};var l,f=navigator.userAgent,m=navigator.platform,e=f.match(/AppleWebKit\/([0-9]+)/),e=!!e&&e[1],h=f.match(/Fennec\/([0-9]+)/),h=!!h&&h[1],n=f.match(/Opera Mobi\/([0-9]+)/),t=!!n&&n[1],k=f.match(/MSIE ([0-9]+)/),k=!!k&&k[1];l=!((-1<m.indexOf("iPhone")||-1<m.indexOf("iPad")||-1<m.indexOf("iPod"))&&e&&534>e||d.operamini&&"[object OperaMini]"==={}.toString.call(d.operamini)||n&&7458>t||-1<f.indexOf("Android")&&e&&533>e||h&&6>h||"palmGetResource"in d&&e&&534>e||-1<f.indexOf("MeeGo")&&-1<f.indexOf("NokiaBrowser/8.5.0")||k&&6>=k)})(jQuery,window);
		$(function(){
			$("#bg").backstretch("{{@ CONSTRUCTR_BASE_URL @}}/THEMES/ASSETS/hdbg3.jpg");
			{{@ PAGE_JS @}}
			var H=$(window).height();
			var W=$(window).width();
			$('.pLogo').height((H));
			$(window).resize(function(){var H=$(window).height();$('.pLogo').height((H));});
		});
	</script>

</body>
</html>