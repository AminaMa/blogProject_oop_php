<?php
#***************************************************************************************#


				#***********************************#
				#********** CONFIGURATION **********#
				#***********************************#
				
				require_once('./include/config.inc.php');
				require_once('./include/db.inc.php');
				require_once('./include/form.inc.php');
				include_once('./include/dateTime.inc.php');
				
				
				#********** INCLUDE CLASSES **********#
				require_once('Class/Blog.class.php');
				require_once('Class/User.class.php');
				require_once('Class/Category.class.php');
			


#***************************************************************************************#


				#****************************************#
				#********** SECURE PAGE ACCESS **********#
				#****************************************#
				

				session_name('blogproject_oop');
				
				#********** START|CONTINUE SESSION	**********#
				if( session_start() === false ) {
					// Fehlerfall
if(DEBUG)		echo "<p class='debug auth err'><b>Line " . __LINE__ . "</b>: FEHLER beim Starten der Session! <i>(" . basename(__FILE__) . ")</i></p>\n";				
									
				} else {
					// Erfolgsfall
if(DEBUG)		echo "<p class='debug auth ok'><b>Line " . __LINE__ . "</b>: Session erfolgreich gestartet. <i>(" . basename(__FILE__) . ")</i></p>\n";				
/*				
if(DEBUG)		echo "<pre class='debugAuth value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG)		print_r($_SESSION);					
if(DEBUG)		echo "</pre>";
*/					
					
					#*******************************************#
					#********** CHECK FOR VALID LOGIN **********#
					#*******************************************#
					

					#********** A) NO VALID LOGIN **********#
					if( isset($_SESSION['ID']) === false OR $_SESSION['IPAddress'] !== $_SERVER['REMOTE_ADDR'] ) {
						// Fehlerfall (User ist nicht eingeloggt)
if(DEBUG)			echo "<p class='debug auth err'><b>Line " . __LINE__ . "</b>: Login konnte nicht validiert werden! <i>(" . basename(__FILE__) . ")</i></p>\n";				
							
							
						#********** DENY PAGE ACCESS **********#
						// 1. Session löschen
						session_destroy();
						
						// 2. User auf öffentliche Seite umleiten
						header('LOCATION: index.php');
						
						// 3. Fallback, falls die Umleitung per HTTP-Header ausgehebelt werden sollte
						exit;
					
					
					#********** B) VALID LOGIN **********#
					} else {
						// Erfolgsfall (User ist eingeloggt)
if(DEBUG)			echo "<p class='debug auth ok'><b>Line " . __LINE__ . "</b>: Login wurde erfolgreich validiert. <i>(" . basename(__FILE__) . ")</i></p>\n";				

						session_regenerate_id(true);						
						
						// fetch user ID from session
						$User = new User( userID:$_SESSION['ID'] );
						
					} // CHECK FOR VALID LOGIN END
						
				} // SECURE PAGE ACCESS END				


#***************************************************************************************#	

			
				#******************************************#
				#********** INITIALIZE VARIABLES **********#
				#******************************************#
				
				$Blog						= NULL;
				$Category				= NULL;
				
				$errorCatLabel			= NULL;
				$errorHeadline 		= NULL;
				$errorImageUpload 	= NULL;
				$errorContent 			= NULL;
				
				$dbError					= NULL;
				$dbSuccess				= NULL;
				



#*******************************************************************************************#


							#*********************************************#
							#********** FETCH USER DATA FROM DB **********#
							#*********************************************#
				
//if(DEBUG)	echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Lese Userdaten aus DB aus... <i>(" . basename(__FILE__) . ")</i></p>\n";
				
				// Schritt 1 DB: DB-Verbindung herstellen
				$PDO = dbConnect('blogproject_oop');
				
				$User->fetchFromDB($PDO);
				
				// DB-Verbindung schließen
//if(DEBUG)	echo "<p class='debug DB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";		
				unset($PDO);

/*
if(DEBUG_V)	echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$User <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)	print_r($User);					
if(DEBUG_V)	echo "</pre>";

*/
#***************************************************************************************#

				
							#*******************************************************#
							#********** PROCESS URL PARAMETERS --LOGOUT --**********#
							#*******************************************************#
				
				// Schritt 1 URL: Prüfen, ob Parameter übergeben wurde
				if( isset($_GET['action']) ) {
if(DEBUG)		echo "<p class='debug'>🧻 Line <b>" . __LINE__ . "</b>: URL-Parameter 'action' wurde übergeben... <i>(" . basename(__FILE__) . ")</i></p>";	
			
					// Schritt 2 URL: Werte auslesen, entschärfen, DEBUG-Ausgabe
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Werte werden ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";
					$action = sanitizeString($_GET['action']);
if(DEBUG_V)		echo "<p class='debug value'>Line <b>" . __LINE__ . "</b>: \$action = $action <i>(" . basename(__FILE__) . ")</i></p>";
		
					// Schritt 3 URL: ggf. Verzweigung
					
					
										#********** LOGOUT **********#
					if( $_GET['action'] === 'logout' ) {
if(DEBUG)			echo "<p class='debug'>📑 Line <b>" . __LINE__ . "</b>: 'Logout' wird durchgeführt... <i>(" . basename(__FILE__) . ")</i></p>";	
						
						session_destroy();
						header("Location: index.php");
						exit();
					}
					
				} // PROCESS URL PARAMETERS END

		
#***************************************************************************************#			

						
									#*************************************************#
									#********** PROCESS FORM 'NEW CATEGORY' **********#
									#*************************************************#
				
				// Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
				if( isset($_POST['formNewCategory']) === true ) {
//if(DEBUG)		echo "<p class='debug'>🧻 Line <b>" . __LINE__ . "</b>: Formular 'New Category' wurde abgeschickt... <i>(" . basename(__FILE__) . ")</i></p>";	
		
					// Schritt 2 FORM: Werte auslesen, entschärfen, DEBUG-Ausgabe
//if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Werte werden ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";
				
					$Category = new Category( catLabel:$_POST['catLabel'] );
				
					// Schritt 3 FORM: Werte ggf. validieren
//if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldwerte werden validiert... <i>(" . basename(__FILE__) . ")</i></p>\n";
					$errorCatLabel = validateInputString($Category->getCatLabel());
					
					
					#********** FINAL FORM VALIDATION **********#
					if( $errorCatLabel !== NULL ) {
						// Fehlerfall
if(DEBUG)			echo "<p class='debug err'>Line <b>" . __LINE__ . "</b>: Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>";						
						
					} else {
						// Erfolgsfall
if(DEBUG)			echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: Das Formular ist formal fehlerfrei. <i>(" . basename(__FILE__) . ")</i></p>";						
						
						// Schritt 4 FORM: Daten weiterverarbeiten

						
						#********** CHECK IF CATEGORY NAME ALREADY EXISTS **********#
						
						$PDO = dbConnect('blogproject_oop');
												
//if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Prüfe, ob CATEGORY bereits in DB ist... <i>(" . basename(__FILE__) . ")</i></p>\n";

						if( $Category->checkIfCategoryNameExistsInDB($PDO) !== 0 ) {
							// Fehlerfall
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>:  CATEGORY '{$Category->getCatLabel()}' ist bereits in DB! <i>(" . basename(__FILE__) . ")</i></p>\n";				
							$errorCatLabel = 'Dieser CATEGORY ist bereits in DB!';	
							
						} else {
							// Erfolgsfall
if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: CATEGORY '{$Category->getCatLabel()}' ist nicht in DB!. <i>(" . basename(__FILE__) . ")</i></p>\n";				
							
												

							#********** SAVE CATEGORY INTO DB **********#
							
							if( $Category->saveCategoryToDB($PDO) === false ) {
									// Fehlerfall
if(DEBUG)						echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Speichern der CATEGORY in die DB! <i>(" . basename(__FILE__) . ")</i></p>\n";				
									$dbError = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';
																
								} else {
									// Erfolgsfall
if(DEBUG)						echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Neuer CATEGORY erfolgreich in die DB gespeichert. <i>(" . basename(__FILE__) . ")</i></p>\n";				
									$dbSuccess = "Die neue Kategorie wurde erfolgreich gespeichert.";
									
									// Felder aus Formular wieder leeren
									$Category->setCatLabel(NULL);
			
			
								}//SAVE CATEGORY INTO DB END
							 
						} // CHECK IF CATEGORY NAME ALREADY EXISTS END
						
						// DB-Verbindung schließen
//if(DEBUG_DB)		echo "<p class='debug db'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";
						unset($PDO);
						
					} // FINAL FORM VALIDATION END

				} // PROCESS FORM 'NEW CATEGORY' END

			
#***************************************************************************************#




									#***************************************************#
									#********** PROCESS FORM 'NEW BLOG ENTRY' **********#
									#***************************************************#
				
				// Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
				if( isset($_POST['formNewBlogEntry']) === true ) {			
//if(DEBUG)		echo "<p class='debug'>🧻 Line <b>" . __LINE__ . "</b>: Formular 'New Blog Entry' wurde abgeschickt... <i>(" . basename(__FILE__) . ")</i></p>";	

					// Schritt 2 FORM: Daten auslesen, entschärfen, DEBUG-Ausgabe
//if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Werte werden ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";
					
											/*
											$Category=new Category(), $User=new User(),
											$blogID=NULL, $blogHeadline=NULL, $blogImagePath=NULL,
											$blogImageAlignment=NULL, $blogContent=NULL, $blogDate=NULL 
											*/
															
															
					$Blog = new Blog(  new Category($_POST['catID']), $User, 
											blogHeadline:$_POST['blogHeadline'],blogImageAlignment:$_POST['blogImageAlignment'],
											blogContent:$_POST['blogContent'] 
										 );
										 
					
					// Schritt 3 FORM: ggf. Werte validieren
//if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldwerte werden validiert... <i>(" . basename(__FILE__) . ")</i></p>\n";
					$errorCategory	= validateInputString($Blog->getCategory()->getCatID(), minLength:1);
					$errorHeadline = validateInputString($Blog->getBlogHeadline());
					$errorContent 	= validateInputString($Blog->getBlogContent(), minLength:5, maxLength:20000);


					#********** FINAL FORM VALIDATION PART I (FIELDS VALIDATION) **********#					
					if( $errorHeadline !== NULL OR $errorContent !== NULL OR $errorCategory !== NULL ) {
						// Fehlerfall
if(DEBUG)			echo "<p class='debug err'>Line <b>" . __LINE__ . "</b>: FINAL FORM VALIDATION PART I: Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>";
						
					} else {
						// Erfolgsfall
if(DEBUG)			echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: FINAL FORM VALIDATION PART I: Das Formular ist formal fehlerfrei. <i>(" . basename(__FILE__) . ")</i></p>";




									#********** OPTIONAL: FILE UPLOAD **********#
									
//if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Prüfe auf Bildupload... <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						// Prüfen, ob eine Datei hochgeladen wurde
						if( $_FILES['blogImagePath']['tmp_name'] === '' ) {
if(DEBUG)				echo "<p class='debug hint'>Line <b>" . __LINE__ . "</b>: Bildupload ist nicht aktive. <i>(" . basename(__FILE__) . ")</i></p>";
						
						} else {
if(DEBUG)				echo "<p class='debug hint'>Line <b>" . __LINE__ . "</b>: Bild Upload ist aktive... <i>(" . basename(__FILE__) . ")</i></p>";

							// imageUpload() liefert ein Array zurück, das eine Fehlermeldung (String oder NULL) enthält
							// sowie den Pfad zum gespeicherten Bild (String oder NULL)
							$validateImageUploadResultArray = validateImageUpload($_FILES['blogImagePath']['tmp_name']);
					
							
							#********** VALIDATE IMAGE UPLOAD RESULTS **********#
							if( $validateImageUploadResultArray['imageError'] !== NULL ) {
								// Fehlerfall
								$errorImageUpload = $validateImageUploadResultArray['imageError'];
								
							} else {
								// Erfolgsfall
//if(DEBUG)					echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: Bild wurde erfolgreich unter <i>'" . $validateImageUploadResultArray['imagePath'] . "'</i> gespeichert. <i>(" . basename(__FILE__) . ")</i></p>";
								
								// SAVE IMAGE PATH 
								$Blog->setBlogImagePath( $validateImageUploadResultArray['imagePath'] );
							
							
							}//VALIDATE IMAGE UPLOAD RESULTS END
							
						} // OPTIONAL: FILE UPLOAD END
						
										#******************************************#
						
																
						#********** FINAL FORM VALIDATION PART II (IMAGE UPLOAD) **********#					
						if( $errorImageUpload !== NULL ) {
							// Fehlerfall
if(DEBUG)				echo "<p class='debug err'>Line <b>" . __LINE__ . "</b>: FINAL FORM VALIDATION PART II: Bilduploadfehler: $validateImageUploadResultArray[imageError] <i>(" . basename(__FILE__) . ")</i></p>";
							
						} else {
							// Erfolgsfall
if(DEBUG)				echo "<p class='debug ok'>Line <b>" . __LINE__ . "</b>: FINAL FORM VALIDATION PART II: Kein Bilduploadfehler. <i>(" . basename(__FILE__) . ")</i></p>";


							#********** SAVE BLOG ENTRY DATA INTO DB **********#
//if(DEBUG)				echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Speichere Blogeintrag in DB... <i>(" . basename(__FILE__) . ")</i></p>\n";
							
							// Schritt 1 DB: DB-Verbindung herstellen
							$PDO = dbConnect('blogproject_oop');
							
							if( $Blog->saveBlogToDB($PDO) === false ) {
									// Fehlerfall
if(DEBUG)						echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Speichern Blogeintrag in die DB! <i>(" . basename(__FILE__) . ")</i></p>\n";				
									$dbError = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';
																
								} else {
									// Erfolgsfall
if(DEBUG)						echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Blogbeitrag erfolgreich in DB gespeichert. <i>(" . basename(__FILE__) . ")</i></p>\n";				

									$dbSuccess = 'Der Blogbeitrag wurde erfolgreich gespeichert.';

/*									
if(DEBUG_V)						echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$Blog <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)						print_r($Blog);					
if(DEBUG_V)						echo "</pre>";										
*/							

									// Felder aus Formular wieder leeren
									$Blog =NULL;
									


								}//SAVE BLOG ENTRY DATA INTO DB END
							
							// DB-Verbindung schließen
//if(DEBUG_DB)			echo "<p class='debug db'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";
							unset($PDO);
							
						} // FINAL FORM VALIDATION PART II (IMAGE UPLOAD) END
							
					} // FINAL FORM VALIDATION PART I (FIELDS VALIDATION) END
					
				} // PROCESS FORM 'NEW BLOG ENTRY' END
			

#***************************************************************************************#
			
					
					#**********************************************#
					#********** FETCH CATEGORIES FROM DB **********#
					#**********************************************#
//if(DEBUG)		echo "<p class='debug'>📑 Line <b>" . __LINE__ . "</b>: Lade Kategorien aus DB... <i>(" . basename(__FILE__) . ")</i></p>";	
				
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
		<title>PHP-Projekt Blog</title>
		<link rel="stylesheet" href="./css/main.css">
		<link rel="stylesheet" href="./css/debug.css">
	</head>

	<body class="dashboard">

		<!-- ---------- PAGE HEADER START ---------- -->
	
		<header class="fright">
			<a href="?action=logout">Logout</a><br>
			<a href="index.php"><< zum Frontend</a>
		</header>
		<div class="clearer"></div>

		<br>
		<hr>
		<br>
		
		<!-- ---------- PAGE HEADER END ---------- -->
		
		<h1 class="dashboard">PHP-Projekt Blog - Dashboard</h1>
		<p class="name">Aktiver Benutzer: <?= $User->getUserFirstName() ?> <?= $User->getUserLastName() ?></p>
		
		
		<!-- ---------- POPUP MESSAGE START ---------- -->
		<?php if( $dbError OR $dbSuccess ): ?>
		<popupBox>
			<?php if($dbError): ?>
			<h3 class="error"><?= $dbError ?></h3>
			<?php elseif($dbSuccess): ?>
			<h3 class="success"><?= $dbSuccess ?></h3>
			<?php endif ?>
			<a class="button" onclick="document.getElementsByTagName('popupBox')[0].style.display = 'none'">Schließen</a>
		</popupBox>		
		<?php endif ?>
		<!-- ---------- POPUP MESSAGE END ---------- -->
		
		
		<!-- ---------- LEFT PAGE COLUMN START ---------- -->
		<main class="forms fleft">			
						
			<h2 class="dashboard">Neuen Blog-Eintrag verfassen</h2>
			<p class="small">
				Um einen Blogeintrag zu verfassen, muss dieser einer Kategorie zugeordnet werden.<br>
				Sollte noch keine Kategorie vorhanden sein, erstellen Sie diese bitte zunächst.
			</p> 
			
			
			<!-- ---------- FORM 'NEW BLOG ENTRY' START ---------- -->
			<form action="" method="POST" enctype="multipart/form-data">
				<input class="dashboard" type="hidden" name="formNewBlogEntry">
				
				<br>
				<label>Kategorie:</label>
				<select class="dashboard bold" name="catID">	
					<?php if( $allCategoriesObjectsArray !== false ): ?>				
						<?php foreach($allCategoriesObjectsArray AS $categorySingleObject): ?>
							<option value='<?= $categorySingleObject->getCatID() ?>' <?php if($Blog?->getCategory()->getCatID() == $categorySingleObject->getCatID() ) echo 'selected'?> ><?= $categorySingleObject->getCatLabel() ?></option>
						<?php endforeach ?>
					<?php else: ?>
						<option value='' style='color: darkred'>Bitte zuerst eine Kategorie anlegen!</option>			
					<?php endif ?>
				</select>
				
		
				<br>
				<br>
				
				<label>Überschrift:</label>
				<span class="error"><?= $errorHeadline ?></span><br>
				<input class="dashboard" type="text" name="blogHeadline" placeholder="..." value="<?= $Blog?->getBlogHeadline() ?>"><br>
				
				
				
				
									<!-- ---------- IMAGE UPLOAD START ---------- -->
									
									
									
				<label>[Optional] Bild veröffentlichen:</label>
				<span class="error"><?= $errorImageUpload ?></span>
				<imageUpload>					
					
					<!-- -------- INFOTEXT FOR IMAGE UPLOAD START -------- -->
					<p class="small">
						Erlaubt sind Bilder des Typs 
						<?php $allowedMimetypes = implode( ', ', array_keys(IMAGE_ALLOWED_MIME_TYPES) ) ?>
						<?= strtoupper( str_replace( array(', image/jpeg', 'image/'), '', $allowedMimetypes) ) ?>.
						<br>
						Die Bildbreite darf 	<?= IMAGE_MAX_WIDTH ?> Pixel nicht übersteigen.<br>
						Die Bildhöhe darf 	<?= IMAGE_MAX_HEIGHT ?> Pixel nicht übersteigen.<br>
						Die Dateigröße darf 	<?= IMAGE_MAX_SIZE/1024 ?>kB nicht übersteigen.
					</p>
					<!-- -------- INFOTEXT FOR IMAGE UPLOAD END -------- -->
					
					<input type="file" name="blogImagePath">
					<select class="alignment fright" name="blogImageAlignment">
						<option value="fleft" 	<?php if($Blog?->getBlogImageAlignment() == 'fleft') echo 'selected'?>>align left</option>
						<option value="fright" 	<?php if($Blog?->getBlogImageAlignment() == 'fright') echo 'selected'?>>align right</option>
					</select>
				</imageUpload>
				<br>	
				<br>	
								
								
								
									<!-- ---------- IMAGE UPLOAD END ---------- -->
				
				
				
				
				<label>Inhalt des Blogeintrags:</label>
				<span class="error"><?= $errorContent ?></span><br>
				<textarea class="dashboard" name="blogContent" placeholder="..."><?= $Blog?->getBlogContent() ?></textarea><br>
				
				<div class="clearer"></div>
				
				<input class="dashboard" type="submit" value="Veröffentlichen">
			</form>
								
								<!-- ---------- FORM 'NEW BLOG ENTRY' END ---------- -->
			
		</main>
		<!-- ---------- LEFT PAGE COLUMN END ---------- -->
		
		
		
		
		
		
		
		
		<!-- ---------- RIGHT PAGE COLUMN START ---------- -->
		<aside class="forms fright">
		
			<h2 class="dashboard">Neue Kategorie anlegen</h2>
			
			
											<!-- ---------- FORM 'NEW CATEGORY' START ---------- -->			
			<form class="dashboard" action="" method="POST">
			
				<input class="dashboard" type="hidden" name="formNewCategory">
				
				<label>Name der neuen Kategorie:</label>
				<span class="error"><?= $errorCatLabel ?></span><br>
				<input class="dashboard" type="text" name="catLabel" placeholder="..." value="<?= $Category?->getCatLabel()?>"><br>

				<input class="dashboard" type="submit" value="Neue Kategorie anlegen">
			</form>
												<!-- ---------- FORM 'NEW CATEGORY' END ---------- -->
			
		
		</aside>

		<div class="clearer"></div>
		<!-- ---------- RIGHT PAGE COLUMN END ---------- -->
		
		
	</body>
</html>






