<?php
#***************************************************************************************#


				#****************************************#
				#********** PAGE CONFIGURATION **********#
				#****************************************#
				
				require_once('./include/config.inc.php');
				require_once('./include/db.inc.php');
				require_once('./include/form.inc.php');
				include_once('./include/dateTime.inc.php');
				
				
				#********** INCLUDE CLASSES **********#
				require_once('Class/Blog.class.php');
				require_once('Class/User.class.php');
				require_once('Class/Category.class.php');
			
				
#*******************************************************************************************#


				#**************************************#
				#********** OUTPUT BUFFERING **********#
				#**************************************#
				

				if( ob_start() === false ) {
					// Fehlerfall
if(DEBUG)		echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Starten des Output Bufferings! <i>(" . basename(__FILE__) . ")</i></p>\r\n";				
					
				} else {
					// Erfolgsfall
if(DEBUG)		echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Output Buffering erfolgreich gestartet. <i>(" . basename(__FILE__) . ")</i></p>\r\n";									
				}

#***************************************************************************************#


				#******************************************#
				#********** INITIALIZE VARIABLES **********#
				#******************************************#
				
				$loginError 			= NULL;
				$categoryFilterID		= NULL;


#***************************************************************************************#


				#*******************************************#
				#********** CHECK FOR LOGIN STATE **********#
				#*******************************************#
				
				#********** START|CONTINUE SESSION	**********#			
				session_name("blogproject_oop");
				session_start();
				
				
				#********** USER IS NOT LOGGED IN **********#
				if( isset($_SESSION['ID']) === false OR $_SESSION['IPAddress'] !== $_SERVER['REMOTE_ADDR'] ) {
if(DEBUG)		echo "<p class='debug auth'><b>Line " . __LINE__ . "</b>: User ist nicht eingeloggt. <i>(" . basename(__FILE__) . ")</i></p>\n";

					// delete empty session
					session_destroy();
					
					// set Flag
					$login = false;
				
				
				#********** USER IS LOGGED IN **********#
				} else {
if(DEBUG)		echo "<p class='debug auth'><b>Line " . __LINE__ . "</b>: User ist eingeloggt. <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					session_regenerate_id(true);

					// set Flag
					$login = true;
				
				} // CHECK FOR LOGIN STATE END			


#***************************************************************************************#


				#****************************************#
				#********** PROCESS FORM LOGIN **********#
				#****************************************#				
						
				// Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
				if( isset($_POST['formLogin']) === true ) {
//if(DEBUG)		echo "<p class='debug'>🧻 Line <b>" . __LINE__ . "</b>: Formular 'Login' wurde abgeschickt... <i>(" . basename(__FILE__) . ")</i></p>";	

					// Schritt 2 FORM: Werte auslesen, entschärfen, DEBUG-Ausgabe
//if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Werte werden ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					$User = new User( userEmail:$_POST['loginName'] );

					$loginPassword = sanitizeString($_POST['loginPassword']);
//if(DEBUG_V)		echo "<p class='debug value'>Line <b>" . __LINE__ . "</b>: \$loginPassword: $loginPassword <i>(" . basename(__FILE__) . ")</i></p>";

					// Schritt 3 FORM: ggf. Werte validieren
//if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldwerte werden validiert... <i>(" . basename(__FILE__) . ")</i></p>\n";
					$errorLoginName 		= validateEmail($User->getUserEmail());
					$errorLoginPassword 	= validateInputString($loginPassword, minLength:4);

//if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$errorLoginName: $errorLoginName <i>(" . basename(__FILE__) . ")</i></p>\n";
//if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$errorLoginPassword: $errorLoginPassword <i>(" . basename(__FILE__) . ")</i></p>\n";
						
					
					#********** FINAL FORM VALIDATION **********#					
					if( $errorLoginName !== NULL OR $errorLoginPassword !== NULL ) {
						// Fehlerfall
if(DEBUG)			echo "<p class='debug err'>Line <b>" . __LINE__ . "</b>: Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>";						
						$loginError = 'Benutzername oder Passwort falsch!';
						
					} else {
						// Erfolgsfall
if(DEBUG)			echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: Das Formular ist formal fehlerfrei. <i>(" . basename(__FILE__) . ")</i></p>";						
									
						// Schritt 4 FORM: Daten weiterverarbeiten
						
						
						#********** FETCH USER DATA FROM DB BY LOGIN NAME **********#	
						// Schritt 1 DB: DB-Verbindung herstellen
						
						
						$PDO = dbConnect("blogproject_oop");
						
						#********** 1. VALIDATE EMAIL **********#						
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Validiere Email-Adresse... <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						if( $User->fetchFromDB($PDO) === false ) {
							// Fehlerfall
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Die Email-Adresse '{$User->getUserEmail()}' wurde nicht in der DB gefunden! <i>(" . basename(__FILE__) . ")</i></p>\n";				
							
							// NEUTRALE Fehlermeldung für User
							$loginError = 'Loginname oder Passwort falsch!';	
							
						} else {
							// Erfolgsfall
if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Die Email-Adresse '{$User->getUserEmail()}' wurde in der DB gefunden. <i>(" . basename(__FILE__) . ")</i></p>\n";				
							
if(DEBUG_V)				echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$User <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)				print_r($User);					
if(DEBUG_V)				echo "</pre>";
							
							
							#********** 2. VALIDATE PASSWORD **********#
if(DEBUG)				echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Validiere Passwort... <i>(" . basename(__FILE__) . ")</i></p>\n";
							
							if( password_verify( $loginPassword, $User->getUserPassword() ) === false ) {
								// Fehlerfall
if(DEBUG)					echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Passwort aus dem Formular stimmt nicht mit dem Passwort aus der DB überein! <i>(" . basename(__FILE__) . ")</i></p>\n";				
								
								// NEUTRALE Fehlermeldung für User
								$loginError = 'Loginname oder Passwort falsch!';
								
							} else {
								// Erfolgsfall
if(DEBUG)					echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Passwort aus dem Formular stimmt mit dem Passwort aus der DB überein. <i>(" . basename(__FILE__) . ")</i></p>\n";				
								
									
									#********** 3. PROCESS LOGIN **********#
if(DEBUG)						echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Login wird durchgeführt... <i>(" . basename(__FILE__) . ")</i></p>\n";
								
								
									#********** PREPARE SESSION **********#
									session_name('blogproject_oop');
									
									
									#********** START SESSION **********#

									if( session_start() === false ) {
										// Fehlerfall
if(DEBUG)							echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Starten der Session! <i>(" . basename(__FILE__) . ")</i></p>\n";				
										
									} else {
										// Erfolgsfall
if(DEBUG)							echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Session erfolgreich gestartet. <i>(" . basename(__FILE__) . ")</i></p>\n";				
										
										
										#********** SAVE USER DATA INTO SESSION FILE **********#
										// $_SESSION['User'] 	= $User;
										$_SESSION['ID'] 			= $User->getUserID();
										$_SESSION['IPAddress'] 	= $_SERVER['REMOTE_ADDR'];
										
if(DEBUG_V)							echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_SESSION <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)							print_r($_SESSION);					
if(DEBUG_V)							echo "</pre>";		
										
										
										#********** REDIRECT TO INTERNAL PAGE **********#
										header('LOCATION: dashboard.php');
										
									
									} // 3. PROCESS LOGIN END
									
							} // 2. VALIDATE PASSWORD END
							
						} // 1. VALIDATE EMAIL END

						// DB-Verbindung schließen
if(DEBUG)			echo "<p class='debug DB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";		
						unset($PDO);

						
					} // FINAL FORM VALIDATION END

				} // PROCESS FORM LOGIN END


#***************************************************************************************#

			
				#********************************************#
				#********** PROCESS URL PARAMETERS **********#
				#********************************************#
				
				// Schritt 1 URL: Prüfen, ob Parameter übergeben wurde
				if( isset($_GET['action']) === true ) {
//if(DEBUG)		echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: URL-Parameter 'action' wurde übergeben. <i>(" . basename(__FILE__) . ")</i></p>\n";										
			
					// Schritt 2 URL: Werte auslesen, entschärfen, DEBUG-Ausgabe
//if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Werte werden ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";
					$action = sanitizeString($_GET['action']);
//if(DEBUG_V)		echo "<p class='debug value'>Line <b>" . __LINE__ . "</b>: \$action: $action <i>(" . basename(__FILE__) . ")</i></p>";
		
					// Schritt 3 URL: ggf. Verzweigung
							
							
					#********** LOGOUT **********#					
					if( $_GET['action'] === 'logout' ) {
if(DEBUG)			echo "<p class='debug'>📑 Line <b>" . __LINE__ . "</b>: 'Logout' wird durchgeführt... <i>(" . basename(__FILE__) . ")</i></p>";	
						
						session_destroy();
						header("Location: index.php");
						exit();
						
						
					#********** FILTER BY CATEGORY **********#					
					} elseif( $action === 'filterByCategory' ) {
if(DEBUG)			echo "<p class='debug'>📑 Line <b>" . __LINE__ . "</b>: Kategoriefilter aktiv... <i>(" . basename(__FILE__) . ")</i></p>";				
						
						
						#********** FETCH SECOND URL PARAMETER **********#
						if( isset($_GET['catID']) === true ) {
							// use $categoryFilterID as flag
							$categoryFilterID = sanitizeString($_GET['catID']);
//if(DEBUG_V)				echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$categoryFilterID: $categoryFilterID <i>(" . basename(__FILE__) . ")</i></p>\n";			
						
						}

					} // BRANCHING END
					
				} // PROCESS URL PARAMETERS END
			
			
#***************************************************************************************#


					#************************************************#
					#********** FETCH BLOG ENTRIES FORM DB **********#
					#************************************************#				
												
				
//if(DEBUG)		echo "<p class='debug'>📑 Line <b>" . __LINE__ . "</b>: Beggint Lade Blogs aus DB... <i>(" . basename(__FILE__) . ")</i></p>";	
				
					// Schritt 1 DB: DB-Verbindung herstellen
					$PDO = dbConnect("blogproject_oop");
					
					
					// Gefundene Datensätze für spätere Verarbeitung in Zweidimensionales Array zwischenspeichern
					$allBlogArticlesObjectsArray = Blog::fetchAllBlogsFromDB($PDO, $categoryFilterID);

					// DB-Verbindung schließen
//if(DEBUG_DB)	echo "<p class='debug db'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";
					unset($PDO);
				
/*
if(DEBUG_V)		echo "<pre class='debug value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)		print_r($allBlogArticlesObjectsArray);					
if(DEBUG_V)		echo "</pre>";
*/				
				
				
				
	
#***************************************************************************************#


					#**********************************************#
					#********** FETCH CATEGORIES FROM DB **********#
					#**********************************************#
if(DEBUG)		echo "<p class='debug'>📑 Line <b>" . __LINE__ . "</b>: Lade Kategorien aus DB... <i>(" . basename(__FILE__) . ")</i></p>";	
				
					// Schritt 1 DB: DB-Verbindung herstellen
					$PDO = dbConnect("blogproject_oop");
					
					
					// Gefundene Datensätze für spätere Verarbeitung in Zweidimensionales Array zwischenspeichern
					$allCategoriesObjectsArray = Category::fetchAllCategoriesFromDB($PDO);

					// DB-Verbindung schließen
//if(DEBUG_DB)	echo "<p class='debug db'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";
					unset($PDO);
				
/*
if(DEBUG_V)		echo "<pre class='debug value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)		print_r($allCategoriesObjectsArray);					
if(DEBUG_V)		echo "</pre>";
*/
	
	
#***************************************************************************************#
?>

<!doctype html>

<html>

	<head>
		<meta charset="utf-8">
		<title>PHP-Projekt Blog-OOP</title>
		<link rel="stylesheet" href="./css/main.css">
		<link rel="stylesheet" href="./css/debug.css">
	</head>

	<body>
		
		<!-- ---------- PAGE HEADER START ---------- -->
		<header class="fright">
			
			<?php if( $login === false ): ?>
				<?php if($loginError): ?>
				<p class="error"><b><?= $loginError ?></b></p>
				<?php endif ?>
				
				<!-- -------- Login Form START -------- -->
				<form action="" method="POST">
					<input type="hidden" name="formLogin">
					<input type="text" name="loginName" placeholder="Email">
					<input type="password" name="loginPassword" placeholder="Password">
					<input type="submit" value="Login">
				</form>
				<!-- -------- Login Form END -------- -->
				
			<?php else: ?>
				<!-- -------- PAGE LINKS START -------- -->
				<a href="?action=logout">Logout</a><br>
				<a href='dashboard.php'>zum Dashboard >></a>
				<!-- -------- PAGE LINKS END -------- -->
			<?php endif ?>
		
		</header>
		
		<div class="clearer"></div>
				
		<br>
		<hr>
		<br>		
		<!-- ---------- PAGE HEADER END ---------- -->
		
		
		
		<h1>PHP-Projekt Blog-OOP</h1>
		<p><a href='index.php'>:: Alle Einträge anzeigen ::</a></p>
		
		
		
		<!-- ---------- BLOG ENTRIES START ---------- -->		
		<main class="blogs fleft">
			
			<?php if( $allBlogArticlesObjectsArray === false ): ?>
				<p class="info">Noch keine Blogeinträge vorhanden.</p>
			
			<?php else: ?>
			
				<?php foreach( $allBlogArticlesObjectsArray AS $singleBlogObject ): ?>
					<?php $dateTime = isoToEuDateTime($singleBlogObject->getBlogDate()) ?>
					
					<article class='blogEntry'>
					
						<a name='entry<?= $singleBlogObject->getBlogID() ?>'></a>
						
						<p class='fright'><a href='?action=filterByCategory&catID=<?= $singleBlogObject->getCategory()->getCatID() ?>'>Kategorie: <?= $singleBlogObject->getCategory()->getCatLabel()?></a></p><br><br>
						<p style="color: blue; font-weight: bold;"><?=  $singleBlogObject->getBlogHeadline() ?></p>
						

						<p class='author'><?= $singleBlogObject->getUser()->getUserFirstName() ?> <?= $singleBlogObject->getUser()->getUserLastName()?> (<?= $singleBlogObject->getUser()->getUserCity() ?>) schrieb am <?= $dateTime['date'] ?> um <?= $dateTime['time'] ?> Uhr:</p>
						
						<p class='blogContent'>
						
							<?php if( $singleBlogObject->getBlogImagePath() ): ?>
								<img class='<?=  $singleBlogObject->getBlogImageAlignment()?>' src='<?= $singleBlogObject->getBlogImagePath() ?>' alt='' title=''>
							<?php endif ?>
							
							<?= nl2br( $singleBlogObject->getBlogContent() ) ?>
						</p>
						
						<div class='clearer'></div>
						
						<br>
						<hr>
						
					</article>
					
				<?php endforeach ?>
			<?php endif ?>
			
		</main>		
		<!-- ---------- BLOG ENTRIES END ---------- -->
		
		
		
		<!-- ---------- CATEGORY FILTER LINKS START ---------- -->		
		<nav class="categories fright">

			<?php if( $allCategoriesObjectsArray === false ): ?>
				<p class="info">Noch keine Kategorien vorhanden.</p>
			
			<?php else: ?>
			
				<?php foreach( $allCategoriesObjectsArray AS $categorySingle ): ?>
					<p><a href="?action=filterByCategory&catID=<?= $categorySingle->getCatID()?>" <?php if( $categorySingle->getCatID() == $categoryFilterID ) echo 'class="active"' ?>><?= $categorySingle->getCatLabel() ?></a></p>
				<?php endforeach ?>

			<?php endif ?>
		</nav>

		<div class="clearer"></div>
		<!-- ---------- CATEGORY FILTER LINKS END ---------- -->
		
	</body>

</html>
