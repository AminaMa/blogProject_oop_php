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
				#********** CLASS Blog **********#
				#********************************#

				
#*******************************************************************************************#


				class Blog {
					
					
					#*******************************#
					#********** ATTRIBUTE **********#
					#*******************************#
					
					private $blogID;
					private $blogHeadline;
					private $blogImagePath;
					private $blogImageAlignment;
					private $blogContent;
					private $blogDate;
					
					// $Category und  $User ist ein eingebettetes Objekt
					private $Category;
					private $User;
					
										
					#***********************************************************#
					
					
					#*********************************#
					#********** CONSTRUCTOR **********#
					#*********************************#
					
					public function __construct( 	$Category=new Category(), $User=new User(),
															$blogID=NULL, $blogHeadline=NULL, $blogImagePath=NULL,
															$blogImageAlignment=NULL, $blogContent=NULL, $blogDate=NULL 
														 )
					{
//if(DEBUG_CC)		echo "<p class='debug class'>🛠 <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "()  (<i>" . basename(__FILE__) . "</i>)</p>\n";						
						
						$this->setCategory($Category);
						$this->setUser($User);
						
						// Setter nur aufrufen, wenn der jeweilige Parameter keinen Leerstring und nicht NULL enthält
						if( $blogID 				!== '' 	AND $blogID 				!== NULL )		$this->setBlogID($blogID);
						if( $blogHeadline 		!== '' 	AND $blogHeadline 		!== NULL )		$this->setBlogHeadline($blogHeadline);
						if( $blogImagePath 		!== '' 	AND $blogImagePath 		!== NULL )		$this->setBlogImagePath($blogImagePath);
						if( $blogImageAlignment !== '' 	AND $blogImageAlignment !== NULL )		$this->setBlogImageAlignment($blogImageAlignment);
						if( $blogContent 			!== '' 	AND $blogContent 			!== NULL )		$this->setBlogContent($blogContent);
						if( $blogDate 				!== '' 	AND $blogDate 				!== NULL )		$this->setBlogDate($blogDate);
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
				
					#********** BLOG ID **********#
					public function getBlogID() : ?int {
						return $this->blogID;
					}
					public function setBlogID(NULL|int|string $value) : void {
						// Vor dem Schreiben auf korrektes Format prüfen
						if( filter_var($value, FILTER_VALIDATE_INT) === false ) {
							// Fehlerfall
if(DEBUG_C)				echo "<p class='debug class err'><b>Line " . __LINE__ .  "</b> | " . __METHOD__ . "(): Datenformat muss einem Integer entsprechen! (<i>" . basename(__FILE__) . "</i>)</p>\n";
							
						} else {
							// Erfolgsfall
						$this->blogID = intval(sanitizeString($value));
						}
					}
					
					
					#********** blogHeadline **********#
					public function getBlogHeadline() :?string {
						return $this->blogHeadline;
					}
					public function setBlogHeadline(NULL|string $value) :void  {
						$this->blogHeadline = sanitizeString($value);
					}
					
					#********** blogImagePath **********#
					public function getBlogImagePath() :?string {
						return $this->blogImagePath;
					}
					public function setBlogImagePath(NULL|string $value)  :void {
						$this->blogImagePath = sanitizeString($value);
					}
					
					#********** blogImageAlignment **********#
					public function getBlogImageAlignment() :?string{
						return $this->blogImageAlignment;
					}
					public function setBlogImageAlignment(NULL|string $value)  :void{
						$this->blogImageAlignment = sanitizeString($value);
					}
					
					
					#********** blogContent **********#
					public function getBlogContent() :?string{
						return $this->blogContent;
					}
					public function setBlogContent(NULL|string $value) :void{
						$this->blogContent = sanitizeString($value);
					}
					
					#********** blogDate **********#
					public function getBlogDate() :?string{
						return $this->blogDate;
					}
					public function setBlogDate(string $value) :void{
						$this->blogDate = sanitizeString($value);
					}
					
	
					#********** Category **********#
					public function getCategory():Category {
						return $this->Category;
					}
					public function setCategory(Category $value){
						$this->Category = $value;
					}
					
					#********** User **********#
					public function getUser():User {
						return $this->User;
					}
					public function setUser(User $value){
						$this->User = $value;
					}
					
					
					#***********************************************************#
					

										#******************************#
										#********** METHODEN **********#
										#******************************#
					
							
					#*********************** FETCH ALL BLOGS ***********************#
					/*
						Um ohne existierendes Objekt auf eine Objekt-Methode zugreifen zu können, 
						muss diese Methode in der Klasse als static deklariert werden. Das bedeutet, 
						dass eben ohne Objekt, dafür aber direkt über den Klassennamen auf sie zugegriffen 
						werden kann.
						Die Syntax für den Aufruf lautet Klassenname::methodenName()
					*/

					
					public static function fetchAllBlogsFromDB(PDO $PDO, ?int $categoryFilterID = NULL) : Array {
if(DEBUG_C)			echo "<p class='debug class'>🌀 <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";
						
						$allBlogArticlesObjectsArray = array();
						
						$sql 		= 'SELECT * FROM Blog
										INNER JOIN User USING(userID)
										INNER JOIN Category USING(catID)';
						
						$params 	= array();						
						
						if( $categoryFilterID === NULL ) {
if(DEBUG)				echo "<p class='debug'>📑 Line <b>" . __LINE__ . "</b>: Lade alle Blog-Einträge... <i>(" . basename(__FILE__) . ")</i></p>";

							#********** B) FILTER BLOG ENTRIES BY CATEGORY ID **********#				
						} else {
if(DEBUG)				echo "<p class='debug'>📑 Line <b>" . __LINE__ . "</b>: Filtere Blog-Einträge nach Kategorie-ID$categoryFilterID... <i>(" . basename(__FILE__) . ")</i></p>";					
					
							// add condition for category filter to sql statement
							$sql		.=	' WHERE catID = ?';
					
							$params[] = $categoryFilterID;
						}	
				
						#************************************************************#
								
						// for both cases add 'order by' condition for the letztes Date for Blogs
						$sql		.= ' ORDER BY blogDate DESC';		
					
						#************************************************************#
					
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
/*							
if(DEBUG_V)					echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$row <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)					print_r($row);					
if(DEBUG_V)					echo "</pre>";										
*/			
															/*
															//Blog Variable
															$Category=new Category(), $User=new User(),
															$blogID=NULL, $blogHeadline=NULL, $blogImagePath=NULL,
															$blogImageAlignment=NULL, $blogContent=NULL, $blogDate=NULL
															*/
															
															
															//User Variable
															/*$userID=NULL, $userFirstName=NULL, $userLasttName=NULL,
															$userEmail=NULL, $userPassword=NULL, $userCity=NULL  
															*/
							$blog = new Blog( 
													new Category( $row['catID'], $row['catLabel']), 
													new User( 
																$row['userID'], $row['userFirstName'], $row['userLastName'], 
																$row['userEmail'], $row['userPassword'], $row['userCity']
																),
													$row['blogID'], $row['blogHeadline'],
													$row['blogImagePath'], $row['blogImageAlignment'],
													$row['blogContent'], $row['blogDate']
													);


							  

							  // Add the Blog object to the array
							  $allBlogArticlesObjectsArray[$row['blogID']] = $blog;
						 }

						 return $allBlogArticlesObjectsArray;
						
					}
					
					
										
					#***********************************************************#
					
					
					
					public function saveBlogToDB(PDO $PDO) {
if(DEBUG_C)			echo "<p class='debug class'>🌀 <b>Line " . __LINE__ .  "</b>: Aufruf " . __METHOD__ . "() (<i>" . basename(__FILE__) . "</i>)</p>\n";
						
						// Schritt 2 DB: SQL-Statement und Placeholder-Array erstellen
						$sql 		= 'INSERT INTO Blog (catID, userID, blogHeadline, blogImagePath, blogImageAlignment, blogContent)
										VALUES (?,?,?,?,?,?) ';
							
						$params 	= array( 
												$this->getCategory()->getCatID(),
												$this->getUser()->getUserID(),
												$this->getBlogHeadline(),
												$this->getBlogImagePath(),
												$this->getBlogImageAlignment(),
												$this->getBlogContent()												
											 );
						
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
if(DEBUG_C)			echo "<p class='debug class value'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						if( $rowCount !== 1 ) {
							// Fehlerfall
							return false;
														
						} else {
							// Erfolgsfall
						
							$this->setBlogID($PDO->lastInsertID());	
							
							return true;
						}
					}
					
					
					
					
					
					#***********************************************************#
					
					
					
				}
				
				
#*******************************************************************************************#