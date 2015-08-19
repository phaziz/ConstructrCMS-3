Constructr CMS 3.0
==================

ConstructrCMS (<a href="http://constructr-cms.org">http://constructr-cms.org</a>) based on FatFree-Framework, Materialize CSS, MySQL/PDO, jQuery/Javascript and Passion!

That's it for now (Version 3.0 / 2015-08-19):

	- FrontEndCache-System (automatic, File based)
	- Page-Management
	- Create, edit, re-order/order, activate/deactivate and delete Pages
	- Page specific CSS- and JS-Content and Metadata
	- Markdown Content-Management with Live-Preview
	- Create, edit, re-order/order, activate/deavtivate and delete Content-Elements
	- ContactForm Handling via ConstructrPostmaster
	- EASY PHP-Templates
	- Asset-Management (Uploads / multiple Uploads) with Filter and Live-Preview and Lightbox and Pagination
	- Add, edit, delete, Uploads
	- User Management
	- Create, edit, delete User-Accounts
	- User Rights-Management 
	- Activate/Deactivate UserRights
	- 2-Step Login
	- User Password resetting

###INSTALLATION:

	- VISIT THE INSTALLER AT http://yourdomain.tld/CONSTRUCTR-CMS-SETUP/

###UPDATE:

	- BACKUP YOUR DATABASE AND WEBSPACE
	- BACKUP - via FTP - YOUR: 
		- ROOT/CONSTRUCTR-CMS/CONFIG/constructr_config.json
		- ROOT/THEMES
		- ROOT/UPLOADS
	- DOWNLOAD THE LATEST CONSTRUCTR-CMS ZIP at GitHub
	- UNZIP AND UPLOAD (OVERWRITE) TO YOUR WEBSERVER
	- UPLOAD YOUR BACKUP:
		- ROOT/CONSTRUCTR-CMS/CONFIG/constructr_config.json
		- ROOT/THEMES
		- ROOT/UPLOADS
	- DELETE THE FOLDER CONSTRUCTR-CMS-SETUP
	- VISIT THE UPDATER AT http://yourdomain.tld/CONSTRUCTR-UPDATER/
	- DELETE THE FOLDER ROOT/CONSTRUCTR-UPDATER
	- THAT'S IT - LOGIN!

### CHANGELOG

	- 2015-08-19 UI-Improvement and Bugfixes
	- 2015-08-18 ConstructrPostmaster integration - Improvement
	- 2015-08-18 New UserSalt with every New/Edited UserAccount - Improvement
	- 2015-08-17 New UserSalt with every Login - Improvement
		- Take care of the Upgrade Instructions above - The database tables need to be updated!
	- 2015-08-17 Paginated-View in Uploads - Bugfix
	- 2015-08-14 Paginated-View in Uploads
	- 2015-08-13 2-Step Login
	- 2015-08-11 Better URL Slug
	- 2015-08-07 Little Improvements and UI-Update
	- 2015-08-06 Better Lightbox and MediaFilter in Uploads
	- 2015-08-05 Automatic CacheRefreshing
	- 2015-08-04 Multiple Uplaods with Preview / FrontendCacheSystem (File based)
	- 2015-08-03 Constructr Content Mapping - Map a Content-Element to a specific HTML-ID-Area / Template Update
	- 2015-07-30 Split Images and other Files in InsertingModal (Content new / Content edit), minor Bugfixes and Improvements
	- 2015-07-29 AutoInsert Markdown for Files / little Bugfixes and minor Features
	- 2015-07-28 Webinstaller / little Bugfixes and minor Features
	- 2015-07-23 Grande Update IV...
	- 2015-03-23 Grande Update III...
	- 2015-03-21 Grande Update II...
	- 2015-03-19 Grande Update...
	- 2015-03-18 Initial commit