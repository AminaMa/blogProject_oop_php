<?php
#*******************************************************************************************#
				
				
				#******************************************#
				#********** ENABLE STRICT TYPING **********#
				#******************************************#
				
				/*
					Erklärung zu 'strict types' in Projekt '01a_klassen_und_instanzen'
				*/
				declare(strict_types=1);
				
				
#*******************************************************************************************#


				#********************************#
				#********** CLASS Category **********#
				#********************************#

				
#*******************************************************************************************#


				class Category {
					
					
					#*******************************#
					#********** ATTRIBUTE **********#
					#*******************************#
					
					private $catID;
					private $catLabel;
									
										
					#***********************************************************#
					
					
					#*********************************#
					#********** CONSTRUCTOR **********#
					#*********************************#
					
					public function __construct( $catID=NULL, $catLabel=NULL )
					{
//if(DEBUG_CC)		echo "<p class='debug class'>🛠 <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "()  (<i>" . basename(__FILE__) . "</i>)</p>\n";						
						
						
						// Setter nur aufrufen, wenn der jeweilige Parameter keinen Leerstring und nicht NULL enthält
						if( $catID 			!== '' 	AND $catID 			!== NULL )		$this->setCatID($catID);
						if( $catLabel 		!== '' 	AND $catLabel 		!== NULL )		$this->setCatLabel($catLabel);
/*												
if(DEBUG_CC)		echo "<pre class='debug class value'><b>Line " . __LINE__ .  "</b> | " . __METHOD__ . "(): <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_CC)		print_r($this);					
if(DEBUG_CC)		echo "</pre>";	
*/					}
					
					
					#********** DESTRUCTOR **********#
					public function __destruct() {
if(DEBUG_CC)		echo "<p class='debug class'>☠️  <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "()  (<i>" . basename(__FILE__) . "</i>)</p>\n";						
					}
					
					
					#***********************************************************#

					
					#*************************************#
					#********** GETTER & SETTER **********#
					#*************************************#
				
					#********** CAT ID **********#
					public function getCatID() : ?int {
						return $this->catID;
					}
					public function setCatID(NULL|int|string $value) : void {
						// Vor dem Schreiben auf korrektes Format prüfen
						if( filter_var($value, FILTER_VALIDATE_INT) === false ) {
							// Fehlerfall
if(DEBUG_C)				echo "<p class='debug class err'><b>Line " . __LINE__ .  "</b> | " . __METHOD__ . "(): Datenformat muss einem Integer entsprechen! (<i>" . basename(__FILE__) . "</i>)</p>\n";
							
						} else {
							// Erfolgsfall
						$this->catID = intval(sanitizeString($value));
						}
					}
					
					
					#********** CAT LABEL  **********#
					public function getCatLabel() :?string {
						return $this->catLabel;
					}
					public function setCatLabel(NULL|string $value) :void{
						$this->catLabel = sanitizeString($value);
					}
					
					
					
					
					#***********************************************************#
					

										#******************************#
										#********** METHODEN **********#
										#******************************#
										
										
					
					public function saveCategoryToDB(PDO $PDO) {
//if(DEBUG_C)			echo "<p class='debug class'>🌀 <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";
						
						// Schritt 2 DB: SQL-Statement und Placeholder-Array erstellen
						$sql 		= 'INSERT INTO Category (catLabel) 
														VALUES 		(?)';
						
						$params 	= array( $this->getCatLabel() );
						
						// Schritt 3 DB: Prepared Statements
						try {
							// Prepare: SQL-Statement vorbereiten
							$PDOStatement = $PDO->prepare($sql);
					
							// Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
							$PDOStatement->execute($params);
					
						} catch(PDOException $error) {
if(DEBUG_C) 			echo "<p class='debug class db err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
							$dbError = 'Fehler beim Zugriff auf die Datenbank!';
						}
						
						// Schritt 4 DB: Datenbankoperation auswerten und DB-Verbindung schließen
						
						$rowCount = $PDOStatement->rowCount();
//if(DEBUG_C)			echo "<p class='debug class value'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						if( $rowCount !== 1 ) {
							// Fehlerfall
							return false;
														
						} else {
							// Erfolgsfall
							/*
								Bei einem INSERT die Last-Insert-ID nur nach geprüftem Schreiberfolg auslesen. 
								Im Zweifelsfall wird hier sonst die zuletzt vergebene ID aus einem älteren 
								Schreibvorgang zurückgeliefert.
							*/
							$this->setCatID($PDO->lastInsertID());	
							
							return true;
						}
					}

					
					#**************************************************************************************************#
					
					#*********************** FETCH ALL CATEGORIES ***********************#

					
					public static function fetchAllCategoriesFromDB(PDO $PDO) : Array {
//if(DEBUG_C)			echo "<p class='debug class'>🌀 <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";
						
						$allCategoriesObjectsArray = array();
						
						$sql 		= 'SELECT * FROM Category';
															
						$params 	= array();						
					
						try {
							// Schritt 2 DB: SQL-Statement vorbereiten
							$PDOStatement = $PDO->prepare($sql);
							
							// Schritt 3 DB: SQL-Statement ausführen und ggf. Platzhalter füllen
							$PDOStatement->execute($params);						
							
						} catch(PDOException $error) {
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
							$dbError = 'Fehler beim Zugriff auf die Datenbank!';
						}
						// Schritt 4 DB: Daten weiterverarbeiten
						// Je Datensatz ein Objekt der jeweiligen Klasse erstellen und in ein Array speichern
						
						while( $row = $PDOStatement->fetch(PDO::FETCH_ASSOC) ) {
							
							$allCategoriesObjectsArray[] = new Category( $row['catID'], $row['catLabel'] );

						}
						
						return $allCategoriesObjectsArray;						
					}
					

					
					
					#***********************************************************#
					
					
					public function checkIfCategoryNameExistsInDB(PDO $PDO) {
//if(DEBUG_C)			echo "<p class='debug class'>🌀 <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";
						
						// Schritt 2 DB: SQL-Statement und Placeholder-Array erstellen
						$sql 		= 'SELECT COUNT(catLabel) FROM Category 
										WHERE catLabel = ?';
						
						$params 	= array( $this->getCatLabel() );
						
						// Schritt 3 DB: Prepared Statements
						try {
							// Prepare: SQL-Statement vorbereiten
							$PDOStatement = $PDO->prepare($sql);
					
							// Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
							$PDOStatement->execute($params);
					
						} catch(PDOException $error) {
if(DEBUG_C) 			echo "<p class='debug class db err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
							$dbError = 'Fehler beim Zugriff auf die Datenbank!';
						}
						
						// Schritt 4 DB: Datenbankoperation auswerten und DB-Verbindung schließen
						/*
							Bei SELECT COUNT(): Rückgabewert von COUNT() über $PDOStatement->fetchColumn() auslesen
						*/
						$count = $PDOStatement->fetchColumn();
//if(DEBUG_C)			echo "<p class='debug class value'><b>Line " . __LINE__ . "</b>: \$count: $count <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						return $count;
					}

					
					#***********************************************************#
					
				}
				
				
#*******************************************************************************************#