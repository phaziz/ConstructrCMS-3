
<html>
<head>
<style>
/* setup */
* {
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  transition: all 60ms ease;
}

/* variables */
.menu ul li, .menu ul a {
  display: block;
  position: relative;
}

.menu li, .menu a {
  display: inline-block;
  position: relative;
}

/* 
Essential Styles 
- - - - - - - - - - - -
I like to keep the specificity low by using tags to make it easy to override the basic strucuture, if needed.  Adding an id to the root element would offer more isolation.

*/
.menu {
  postion: relative;
  list-style: none;
  float: left;
  padding: 0;
  margin: 0;
  *zoom: 1;
}
.menu li {
  float: left;
}
.menu a {
  height: 3rem;
  line-height: 3rem;
  width: 100%;
}
.menu ul {
  display: none;
  position: absolute;
  top: 100%;
  left: 0px;
  width: 180px;
  min-width: 180px;
  padding: 0;
  margin: 0;
}
.menu ul ul {
  top: -0.35rem;
  left: 100%;
}
.menu ul li {
  float: left;
  width: 100%;
}
.menu ul a {
  height: 2rem;
  line-height: 2rem;
}
.menu li:hover > .sub-menu {
  display: block;
}
.menu:before, .menu:after {
  content: " ";
  display: table;
}
.menu:after {
  clear: both;
}

/* THEME  */
.navbar {
  background: #D2D7D3;
  *zoom: 1;
}
.navbar:before, .navbar:after {
  content: " ";
  display: table;
}
.navbar:after {
  clear: both;
}

.menu {
  width: 100%;
  /* Change this in order to change the Dropdown symbol */
}
.menu li {
  border-right: 1px dotted #ECECEC;
  width: 25%;
}
.menu li:last-child {
  border-right: none;
}
.menu a {
  color: #6C7A89;
  font-size: 0.75rem;
  text-decoration: none;
  text-transform: uppercase;
  padding: 0 0.75rem;
}
.menu a:hover, .menu a.active {
  color: #F2784B;
  /* border-bottom: 1px solid; */
}
.menu .sub-menu li > a:after, .menu li > a:after {
  line-height: 1em;
  font-size: 6px;
  padding-left: 6px;
  position: relative;
  top: -2px;
}
.menu .sub-menu li > a:after {
  content: ' \25B6';
}
.menu li > a:after {
  content: ' \25BC';
}
.menu li > a:only-child:after,
.menu .sub-menu li > a:only-child:after {
  content: '';
}
.menu .sub-menu {
  background: #34495e;
  padding: 0.35rem 0.25rem 0.25rem;
}
.menu .sub-menu li {
  border-right: none;
}
.menu .sub-menu a {
  color: #F2F1EF;
  /* padding: 0.35rem ($menuHeight / 2); */
}
.menu .sub-menu a:hover {
  background: #2C3E50;
  border-bottom: none;
}
.menu .sub-menu:before {
  content: '';
  border-bottom: 14px solid #34495e;
  border-right: 14px solid transparent;
  position: absolute;
  top: -14px;
  left: 0;
}
.menu .sub-menu .sub-menu {
  background: #2C3E50;
}
.menu .sub-menu .sub-menu:before {
  content: ' ';
  border-right: 14px solid #2C3E50;
  border-bottom: 14px solid transparent;
  position: absolute;
  top: 0;
  left: -14px;
}
.menu .sub-menu .sub-menu li a:hover,
.menu .sub-menu .sub-menu li a:active {
  background: #34495e;
}
.menu .active,
.menu .current_page_item a,
.menu .current-menu-item a {
  color: #6C7A89;
}

/* DEMO PAGE STYLE */
html {
  padding: 40px;
  background: #ECF0F1;
}

h1 {
  color: #2c3e50;
  text-transform: uppercase;
  text-align: center;
  font-size: 14px;
  line-height: 21px;
  letter-spacing: 10px;
  margin: 48px auto 0;
}

h1 + p {
  font-size: 14px;
  font-family: serif;
  font-style: italic;
  color: #b5bfc1;
  text-align: center;
  border-top: 1px dotted #ccc;
  width: 360px;
  padding: 12px;
  margin: 12px auto 48px;
}


</style>
</head>
<body>
  
  <h1>Simple CSS Menu</h1>
  <p>clean standard horizontal drop down menu, in plain SCSS & WordPress ready.</p>
  
<nav class="navbar" role='navigation'>
    
  <ul class="menu no-js">
    <li class="menu-item"><a href="#" class="active">Menu Item</a></li>
    <li class="menu-item"><a href="#">Sub Menu</a>
      <ul class="sub-menu">
        <li class="menu-item"><a href="#">Menu Item</a></li>
        <li class="menu-item"><a href="#">Menu Item</a></li>
        <li class="menu-item">
            <a href="#">Menu Item</a>
            <ul class="sub-menu">
              <li class="menu-item"><a href="#">Menu Item</a></li>
              <li class="menu-item"><a href="#">Menu Item</a></li>
              <li class="menu-item"><a href="#">Menu Item</a></li>
              <li class="menu-item"><a href="#">Menu Item</a></li>
              <li class="menu-item"><a href="#">Menu Item</a>
                <ul class="sub-menu">
                  <li class="menu-item"><a href="#">Menu Item</a></li>
                  <li class="menu-item"><a href="#">Menu Item</a></li>
                  <li class="menu-item"><a href="#">Menu Item</a></li>
                  <li class="menu-item"><a href="#">Menu Item</a></li>
                </ul>  
              </li>
              <li class="menu-item"><a href="#">Menu Item</a></li>
              <li class="menu-item"><a href="#">Menu Item</a></li>
            </ul>
        </li>
        <li class="menu-item"><a href="#">Menu Item</a></li>
        <li class="menu-item"><a href="#">Menu Item</a></li>
        <li class="menu-item"><a href="#">Menu Item</a></li>
        <li class="menu-item"><a href="#">Menu Item</a></li>
      </ul>
    </li>

     <li class="menu-item"><a href="#">Menu Item</a> 
      <ul class="sub-menu">
        <li class="menu-item"><a href="#">Menu Item</a></li>
        <li class="menu-item"><a href="#">Menu Item</a></li>
        <li class="menu-item"><a href="#">Menu Item</a></li>
        <li class="menu-item"><a href="#">Menu Item</a></li>
      </ul>  
    </li>

     <li class="menu-item"><a href="#">Menu Item</a></li>

  </ul>
</nav>  

</body>

</html>