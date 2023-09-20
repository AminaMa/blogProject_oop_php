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
				#********** CLASS USER **********#
				#********************************#

				
#*******************************************************************************************#


				class User {
					
					
					#*******************************#
					#********** ATTRIBUTE **********#
					#*******************************#
					
					private $userID;
					private $userFirstName;
					private $userLastName;
					private $userEmail;
					private $userPassword;
					private $userCity;
					
										
					#***********************************************************#
					
					
					#*********************************#
					#********** CONSTRUCTOR **********#
					#*********************************#
					
					public function __construct( 	$userID=NULL, $userFirstName=NULL, $userLastName=NULL,
															$userEmail=NULL, $userPassword=NULL, $userCity=NULL  
														 )
					{
//if(DEBUG_CC)		echo "<p class='debug class'>🛠 <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "()  (<i>" . basename(__FILE__) . "</i>)</p>\n";						
												
						// Setter nur aufrufen, wenn der jeweilige Parameter keinen Leerstring und nicht NULL enthält
						if( $userID 				!== '' 	AND $userID 				!== NULL )		$this->setUserID($userID);
						if( $userFirstName 		!== '' 	AND $userFirstName 		!== NULL )		$this->setUserFirstName($userFirstName);
						if( $userLastName 		!== '' 	AND $userLastName 		!== NULL )		$this->setUserLastName($userLastName);
						if( $userEmail 			!== '' 	AND $userEmail 			!== NULL )		$this->setUserEmail($userEmail);
						if( $userPassword 		!== '' 	AND $userPassword 		!== NULL )		$this->setUserPassword($userPassword);
						if( $userCity 				!== '' 	AND $userCity 				!== NULL )		$this->setUserCity($userCity);
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
				
					#********** USER ID **********#
					public function getUserID() : ?int {
						return $this->userID;
					}
					public function setUserID(NULL|int|string $value) : void{
						// Vor dem Schreiben auf korrektes Format prüfen
						if( filter_var($value, FILTER_VALIDATE_INT) === false ) {
							// Fehlerfall
if(DEBUG_C)				echo "<p class='debug class err'><b>Line " . __LINE__ .  "</b> | " . __METHOD__ . "(): Datenformat muss einem Integer entsprechen! (<i>" . basename(__FILE__) . "</i>)</p>\n";
							
						} else {
							// Erfolgsfall
						$this->userID = intval(sanitizeString($value));
						}
					}
					
					
					#********** USER FIRSTNAME **********#
					public function getUserFirstName() :?string{
						return $this->userFirstName;
					}
					public function setUserFirstName(NULL|string $value) :void{
						$this->userFirstName = sanitizeString($value);
					}
					
					#********** USER LASTNAME **********#
					public function getUserLastName() :?string{
						return $this->userLastName;
					}
					public function setUserLastName(NULL|string $value) :void{
						$this->userLastName = sanitizeString($value);
					}
					
					#********** USER EMAIL **********#
					public function getUserEmail() :?string{
						return $this->userEmail;
					}
					public function setUserEmail(NULL|string $value) :void{
						$this->userEmail = sanitizeString($value);
					}
					
					
					#********** USER PASSWORD **********#
					public function getUserPassword() :?string{
						return $this->userPassword;
					}
					public function setUserPassword(NULL|string $value) :void{
						$this->userPassword = sanitizeString($value);
					}
					
					
					#********** USER CITY **********#
					public function getUserCity() :?string{
						return $this->userCity;
					}
					public function setUserCity(NULL|string $value) :void{
						$this->userCity = sanitizeString($value);
					}
					
					
					
					#***********************************************************#
					

									#******************************#
									#********** METHODEN **********#
									#******************************#
					
					
					public function fetchFromDB(PDO $PDO) {
//if(DEBUG_C)			echo "<p class='debug class'>🌀 <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";
						
						// Schritt 2 DB: SQL-Statement und Placeholder-Array erstellen
						$sql 		= 'SELECT * FROM User
										WHERE userEmail 	= ?
										OR 	userID		= ?';
						
						$params 	= array( $this->getUserEmail(),
												 $this->getUserID() );
						
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
							Bei lesenden Operationen wie SELECT und SELECT COUNT:
							Abholen der Datensätze bzw. auslesen des Ergebnisses
						*/
						$row = $PDOStatement->fetch(PDO::FETCH_ASSOC);
/*					
if(DEBUG_V)			echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$row <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)			print_r($row);					
if(DEBUG_V)			echo "</pre>";
*/
						if( $row === false ) {
							// Fehlerfall
							return false;
							
						} else {
							// Erfolgsfall
							
							#********** WRITE DB VALUES INTO CALLING OBJECT **********#
							#** USER OBJECT **#
							if( $row['userID'] 				!== '' 	AND $row['userID'] 				!== NULL )	$this->setUserID( $row['userID'] );
							if( $row['userFirstName'] 		!== '' 	AND $row['userFirstName'] 		!== NULL )	$this->setUserFirstName( $row['userFirstName'] );
							if( $row['userLastName'] 		!== '' 	AND $row['userLastName'] 		!== NULL )	$this->setUserLastName( $row['userLastName'] );
							if( $row['userEmail'] 			!== '' 	AND $row['userEmail'] 			!== NULL )	$this->setUserEmail( $row['userEmail'] );
							if( $row['userPassword'] 		!== '' 	AND $row['userPassword'] 		!== NULL )	$this->setUserPassword( $row['userPassword'] );
							if( $row['userCity'] 			!== '' 	AND $row['userCity'] 			!== NULL )	$this->setUserCity( $row['userCity'] );
							
							
							return true;
						}
					}
					
										
					
					#***********************************************************#
			
				}
				
				
#*******************************************************************************************#