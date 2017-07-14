<?php
	

	/*

		GGN SETUP 0.2
		Mise à jour 170703.0939

		Installer les "Boot.GGN" dans le dossier de votre choix sur votre serveur.
		Préconfigurez le GGN Frameworks. 

	*/
	

	$Do = (isset($_GET['do'])) ? $_GET['do'] : false;

	$_CACHES_FILE = "ggn.caches.php?path=";

	$_TMP_SETUP_SCRIPT = '~ggn.setup/';



	// echo '<textarea name="" id="" cols="30" rows="10">'.base64_encode( file_get_contents('ggn.copryght.logo.png') ).'</textarea>';exit;



	/*
	
		Require : Classe des Reponses / DEBUT ////////////////////////////////

	*/

		Class Responses{

			var $Node = [];

			public function __construct(){



			}

			public function add($nodename = false, $value = false){

				if(is_string($nodename)){

					$this->Node[$nodename] = $value;

				}

				return $this;

			}

			public function close(){

				echo json_encode($this->Node, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

				exit;

			}


		}

		$Responses = new Responses();


	/*
	
		Require : Classe des Reponses / FIN ////////////////////////////////

	*/





	/*
	
		Require : Util / DEBUT ////////////////////////////////

	*/

		Class Util{



			static public function MergeArray($old = false, $new = false){ 

				if(is_array($old) && is_array($new)){

					$o = $old;

					$e = $new;

					$mode = (isset($args[2]))?$args[2]: false;
					
					if($mode===true){foreach($e as $k => $v){ $o[$k] = $v; }}

					else{foreach($e as $k => $v){ array_push($o, $v); }}
					
					return $o;
					
				}

				return false;

			}



			static public function iScanDir($dir = false){ 

				if(is_dir($dir)){

					$data = self::ScanDir($dir);

					$toScan = $data;

					$content = [];

					$lim=count($toScan);
					
					
					foreach($data as $D){

						if(is_dir($D)){$content = self::MergeArray($content, self::iScanDir($D), false); }

						if(is_file($D)){array_push($content, $D);}

					}
							
					return $content;
					
				}

				return false;

			}



			static public function ScanDir($dir = false){ 

				if(is_dir($dir)){

					$f = $dir; 

					$content = [];

					$f = ((substr($f,-1)=='/')? $f: $f.'/'); 

					$Dir = opendir($f);
						
					while ($rF = readdir($Dir)){

						$e = $f . $rF;

						$bn = basename($e);
						
						if($bn!='.' && $bn!='..'){

							array_push($content, $e);

						}
						
					}
					
					return $content;
					
				}

				return false;

			}


			static public function FDir($dir = false){ 

				if(is_string($dir)){

					$ddir = ltrim(rtrim($dir));

					return $dir . ((substr($ddir, -1) == '/') ? '' : '/');

				}

				return $dir;

			}

			static public function iFDir($dir = false){ 

				if(is_string($dir)){

					$ddir = ltrim(rtrim($dir));

					return $dir . ((substr($ddir, 0, 1) == '/') ? '' : '/');

				}

				return $dir;

			}



			static public function CreateFile($filename = false, $content = ''){ 

				if(is_string($filename)){

					$open = fopen($filename, "w+");

					if(is_writable($filename)){

						if(fwrite($open, $content)){return true;}

						else{return false;}

					}

					else{return false;}
					
				}

				return false;

			}



			static public function GetScriptHost($dir = false){ 

				if(is_string($dir)){

					$U = $_SERVER['HTTP_HOST'];

					$R = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);

					$D = str_replace('\\', '/', $dir);

					return 'http://' . ($U) . self::iFDir(self::FDir(substr($D, strlen($R))));
					
				}

				return false;

			}



		}



	/*
	
		Require : Util / FIN ////////////////////////////////

	*/










	/*
	
		Traitement : Vérification de la base de donnée / DEBUT ////////////////////////////////

	*/

		if($Do == 'check.sql.db'){

			$host = (isset($_POST['host'])) ? $_POST['host'] : false;

			$username = (isset($_POST['username'])) ? $_POST['username'] : false;

			$password = (isset($_POST['password'])) ? $_POST['password'] : false;

			$dbName = (isset($_POST['dbName'])) ? $_POST['dbName'] : false;

			try {

			    $Connexion = new PDO('mysql:host='.$host.';dbname='.$dbName, $username, $password);

			    $Responses->add('sql-db-check', true);

			}

			catch (Exception $e) {

			    $Responses->add('sql-db-check', $e->getMessage());

			}


			$Responses->close();

		}

	/*
	
		Traitement : Vérification de la base de donnée / FIN ////////////////////////////////

	*/
		









	/*
	
		Traitement : Vérification de la base de donnée / DEBUT ////////////////////////////////

	*/

		if($Do == 'exec.db.queries'){

			$host = (isset($_POST['host'])) ? $_POST['host'] : false;

			$username = (isset($_POST['username'])) ? $_POST['username'] : false;

			$password = (isset($_POST['password'])) ? $_POST['password'] : false;

			$dbName = (isset($_POST['dbName'])) ? $_POST['dbName'] : false;

			$query = (isset($_POST['query'])) ? $_POST['query'] : '';


			try {

			    $Connexion = new PDO('mysql:host='.$host.';dbname='.$dbName, $username, $password);

			    $Connexion->exec($query);

			    $Responses->add('query', true);

			}

			catch (Exception $e) {

			    $Responses->add('query', false);

			}


			$Responses->close();

		}

	/*
	
		Traitement : Vérification de la base de donnée / FIN ////////////////////////////////

	*/






	/*
	
		Traitement : Construction de l liste des requetes SQL / DEBUT ////////////////////////////////

	*/

		if($Do == 'build.db.queries'){


			$dbPrefix = (isset($_POST['dbPrefix'])) ? $_POST['dbPrefix'] : false;

			$dir = (isset($_POST['dir'])) ? Util::FDir($_POST['dir']) : false;


			$SetupDir = $dir . $_TMP_SETUP_SCRIPT;

			$ManifestFile = $SetupDir . 'manifest.php';


			if(is_file($ManifestFile)){

				$Manifest = [];

				include $ManifestFile;

				if(

					isset($Manifest['DB.Queries.File']) 

					&& is_string($Manifest['DB.Queries.File']) 

					&& is_file($SetupDir . $Manifest['DB.Queries.File'])

				){

					$QueriesData = file_get_contents($SetupDir. $Manifest['DB.Queries.File']);

					$QueriesData = str_replace('%DBPREFIX%', $dbPrefix, $QueriesData);

					$Queries = explode(";\n", $QueriesData);

					$Out = [];


					foreach ($Queries as $query) {

						if(strlen(trim($query)) > 0){

							array_push($Out, $query);

						}
						
					}

					$Responses->add('SQLQueries', $Out);

				}

				else{$Responses->add('SQLQueries', 'sql.queries.not.found'); } 

			}

			else{$Responses->add('SQLQueries', 'manifest.not.found'); }


			$Responses->close();

		}

	/*
	
		Traitement : Construction de l liste des requetes SQL / FIN ////////////////////////////////

	*/





	/*
	
		Traitement : Construction du fichier de paramètres de la base de donnée donnée / DEBUT ////////////////////////////////

	*/

		if($Do == 'build.db.settings'){

			$host = (isset($_POST['host'])) ? $_POST['host'] : false;

			$username = (isset($_POST['username'])) ? $_POST['username'] : false;

			$password = (isset($_POST['password'])) ? $_POST['password'] : false;

			$dbName = (isset($_POST['dbName'])) ? $_POST['dbName'] : false;

			$dbPrefix = (isset($_POST['dbPrefix'])) ? $_POST['dbPrefix'] : false;


			$dir = (isset($_POST['dir'])) ? Util::FDir($_POST['dir']) : false;

			$SetupDir = $dir . $_TMP_SETUP_SCRIPT;

			$ManifestFile = $SetupDir . 'manifest.php';


			if(is_file($ManifestFile)){

				$Manifest = [];

				include $ManifestFile;

				if(

					isset($Manifest['DB.Settings.InPut']) 

					&& is_string($Manifest['DB.Settings.InPut']) 

					&& is_file($SetupDir . $Manifest['DB.Settings.InPut'])

					&& isset($Manifest['DB.Settings.OutPut'])

					&& is_string($Manifest['DB.Settings.OutPut'])

				){

					$Distributor = file_get_contents($SetupDir. $Manifest['DB.Settings.InPut']);


					$Distributor = str_replace('%HOST%', $host, $Distributor);

					$Distributor = str_replace('%USER%', $username, $Distributor);

					$Distributor = str_replace('%PASS%', $password, $Distributor);

					$Distributor = str_replace('%DBNAME%', $dbName, $Distributor);

					$Distributor = str_replace('%DBPREFIX%', $dbPrefix, $Distributor);


					$Responses->add('DBSettings', Util::CreateFile($dir . $Manifest['DB.Settings.OutPut'], $Distributor));

					$Responses->add('AdminCreator', (isset($Manifest['URL.Admin.Create'])) ? $_TMP_SETUP_SCRIPT . $Manifest['URL.Admin.Create'] : false );

					$Responses->add('URLDone', (isset($Manifest['URL.Done'])) ? $Manifest['URL.Done'] : false );

				}

				else{$Responses->add('DBSettings', 'distributor.not.found'); } 

			}

			else{$Responses->add('DBSettings', 'manifest.not.found'); }


			$Responses->close();

		}

	/*
	
		Traitement : Construction du fichier de paramètres de la base de donnée donnée / FIN ////////////////////////////////

	*/






	/*
	
		Traitement : Nettoyage du script / DEBUT ////////////////////////////////

	*/

		if($Do == 'clear.script.setup'){


			$dir = (isset($_POST['dir'])) ? Util::FDir($_POST['dir']) : false;

			$SetupDir = $dir . $_TMP_SETUP_SCRIPT;



			if(is_dir($SetupDir)){

				$Scan = Util::iScanDir($SetupDir);

				foreach ($Scan as $File) {

					if(is_file($File)){unlink($File); }
					
					if(is_dir(dirname($File))){rmdir(dirname($File)); }
					
				}

			}

			$Responses->add('Cleaner', true);

			$Responses->close();

		}

	/*
	
		Traitement : Nettoyage du script / FIN ////////////////////////////////

	*/





	/*
	
		Traitement : Déploiement du Boot.GGN / DEBUT ////////////////////////////////

	*/

		if($Do == 'deployment'){

			$dir = (isset($_POST['dir'])) ? $_POST['dir'] : false;

			$BootGGN = (isset($_POST['boot-ggn'])) ? $_POST['boot-ggn'] : 'Boot.GGN';

			$Responses->add('deployed', false);
			
			
			if(is_string($dir)){

				if(is_file($BootGGN)){

					$Boot = new ZipArchive;

					$Test = $Boot->open($BootGGN);

					if($Test === true){

						$Data = $Boot->extractTo($dir);


						$Responses->add('ScriptURL', Util::GetScriptHost($dir));

						$Responses->add('deployed', true);

					}

					if($Test !== true){

						$Responses->add('deployed', 'boot.not.found');

					}

				}

				else{

					$Responses->add('deployed', 'boot.not.found');

				}

			}


			$Responses->close();

		}

	/*
	
		Traitement : Déploiement du Boot.GGN / FIN ////////////////////////////////

	*/




		
	/*
	
		Interface : Renseignement des informations / DEBUT ////////////////////////////////

	*/
		


	
	
?>

<!DOCTYPE html>

<html>

<head>

	<title>GGN Frameworks Senju - Setup</title>

	<link rel="stylesheet" href="<?php echo $_CACHES_FILE; ?>css:framework">

	<link rel="stylesheet" href="<?php echo $_CACHES_FILE; ?>css:font.family">

	<style type="text/css">
		
		<!--

			body{

				letter-spacing:-0.03em;

			}

			div, button, body{

				font-family: RobotoCondensedLight, arial;

			}

			a:hover, a:focus{text-decoration:none;}


			.page{

				position: relative;

				display: none;

				opacity: 0.0001;

				overflow-x:hidden;

				overflow-y:auto;

				-webkit-transform:translateY(20%); 

				transform:translateY(20%);

				-webkit-transition:all 300ms ease-in-out; 

				transition:all 300ms ease-in-out;

			}

			.page.show{

				opacity: 1;

				-webkit-transform:translateY(0px); 

				transform:translateY(0px);

			}


			.page-title{

				letter-spacing:-0.09em;

			}
			

			.page-content{}

			.page-content input{

				margin-top:10px;

				margin-bottom:10px;

			}
			

			.stage-buttons{
	

			}

				.stage-buttons .go{
		
					cursor: pointer;

					background-color: transparent;

					border-radius: 0px;
	
					width: 48px;

					height: 48px;
		
					background-repeat: no-repeat;

					background-position: center;

					background-size: 100% auto;

				}

				.stage-buttons .go:hover{
		
					background-color: transparent;

					border-radius: 0px;

				}

				.stage-buttons.prev .go{
		
					background-image: url(<?php echo $_CACHES_FILE; ?>png:button.prev);

					-webkit-transform:scale(0.7); 

					transform:scale(0.7);

				}

				.stage-buttons.next .go{
		
					background-image: url(<?php echo $_CACHES_FILE; ?>png:button.next);

				}


			.progress-bar{

				height: 7px;

			}

				.progress-bar > .cache

				, .progress-bar > .track{

					top: 0px;

					left: 0px;

				}

				.progress-bar > .track > .micro-percent{

					top: -12px;

					left: 12px;

				}


			input:not(.button):not([type="reset"]):not([type="submit"]):not([type="button"]) {

				font-family:RobotoCondensedLight, arial;

				font-size:18px;

				padding:12px 20px;

				background-color:transparent;

				border-radius:0px;

				border-color:#333;

				border-bottom:3px solid #fff;
				
			}
			


		-->

	</style>

</head>

<body class="disable-scrollbar bg-dark-d">
	

	<!-- Feuille GGN / DEBUT -->

		<div class="gui sheet">




			<!-- Splash Screen / DEBUT -->
				
				<div class="page show vw10 vh10 gui flex column" id="splash-screen">

					<div class="col-0 gui flex center">

						<img src="<?php echo $_CACHES_FILE; ?>svg:ggn.framework.logo" alt="" style="width:192px;height:192px;">

						
					</div>

					<div class="x128-h padding-b-x32 gui flex center">

						<img src="<?php echo $_CACHES_FILE; ?>png:ggn.copryght.logo" alt="" style="width:331px;height:auto;" class="">
						
					</div>
			
					
				</div>

			<!-- Splash Screen / FIN -->










			<!-- Licence / DEBUT -->
				
				<div class="page vw10 vh10 gui column" id="setup-license">

						<div class="x92-h gui flex start column padding-x32">

							<div class="page-title xh1 text-ellipsis">License</div>
							
						</div>

					<div class="col-0 vw10 gui flex start enable-y-auto-scrollbar">
							
						<div class="margin-x32 mi-col-14 li-col-14 s-col-14">
							

							<?php echo nl2br("Copyright © 2013-2014 GOBOU Y. Yannick, http://gougnon.com

		Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the \"Software\"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:
	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED \"AS IS\", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE."); ?>

						</div>
						
					</div>

					<div class="x100-h padding-x32 gui flex start">

						<button class="accept-license text-x32" handler-click="Stage.Next" setup-check-point="license">J'accepte</button>
						
					</div>
			
					
				</div>

			<!-- Licence / FIN -->




			






			<!-- Choix du Boot.GGN / DEBUT -->

				<form action="#" method="post" onsubmit="return false;" class="page vw10 vh10 gui column" id="setup-choosePkg">

					<div class="x92-h gui flex start column padding-x32">

						<div class="page-title xh1 text-ellipsis">Boot.GGN</div>

						<div class="page-about padding-l-x12 text-x16">Sélectionner le Package à installer.</div>
						
					</div>

					<div class="col-0 padding-x32 gui flex column page-content">
							
						<?php 

							$ScanBoot = Util::ScanDir(dirname(__FILE__));

							foreach($ScanBoot as $bFile){

								$bFile = basename($bFile);

								if(strtolower(substr($bFile, -1 * strlen('boot.ggn') )) == 'boot.ggn'){

									$BootName = basename($bFile);

						?>

							<div class="text-x16">
								
								<input type="radio" name="boot" value="<?php echo $bFile; ?>"> <?php echo $BootName; ?>
							
							</div>
								
						<?php

								}

							}

						?>
						
					</div>
			
					<div class="x80-h  padding-x32 gui flex start row wrap">

						<div class="stage-buttons prev"><input type="button" value="&nbsp;" class="go" handler-click="Stage.Prev"></div>

						<div class="stage-buttons next"><input type="submit" value="&nbsp;" class="go" handler-click="Stage.Next" setup-check-point="choosePkg"></div>
						
					</div>
				

				</form>

			<!-- Choix du Boot.GGN / FIN -->


			






			<!-- Dossier d'installation / DEBUT -->

				<form action="#" method="post" onsubmit="return false;" class="page vw10 vh10 gui column" id="setup-installDir">

					<div class="x92-h gui flex start column padding-x32">

						<div class="page-title xh1 text-ellipsis">Dossier d’installation</div>

						<div class="page-about padding-l-x12 text-x16">Choisissez le dossier où vous souhaitez installer GGN Frameworks</div>
						
					</div>

					<div class="col-0 padding-x32 gui flex column page-content">

							
						<input class="x608-w mi-col-14 li-col-14 s-col-14" type="text" name="installDir" required placeholder="Indiquez un dossier de puis la racine : <?php echo $_SERVER['DOCUMENT_ROOT'] . '/'; ?>" value="<?php echo $_SERVER['DOCUMENT_ROOT'] . '/'; ?>">

						<!-- <textarea name="" id="" cols="30" rows="10"><?php echo base64_encode(file_get_contents('ggn.copryght.logo.png')); ?></textarea> -->
						
						
					</div>
			
					<div class="x80-h  padding-x32 gui flex start row wrap">

						<div class="stage-buttons prev"><input type="button" value="&nbsp;" class="go license" handler-click="Stage.Prev"></div>

						<div class="stage-buttons next"><input type="submit" value="&nbsp;" class="go" handler-click="Stage.Next" setup-check-point="install.dir"></div>
						
					</div>
				

				</form>

			<!-- Dossier d'installation / FIN -->



			






			<!-- Base de donnée / DEBUT -->

				<form action="#" method="post" onsubmit="return false;" class="page vw10 vh10 gui column" id="setup-db">

					<div class="x92-h gui flex start column padding-x32">

						<div class="page-title xh1 text-ellipsis">Base de donnée</div>

						<div class="page-about padding-l-x12 text-x16">Indiquez les paramètres de la base de donnée que vous utiliserez pour GGN Frameworks</div>
						
					</div>

					<div class="col-0 padding-x32 gui flex column page-content">

							
						<input class="x608-w mi-col-14 li-col-14 s-col-14" type="text" name="host" placeholder="L’ adresse du hote" value="">

						<input class="x608-w mi-col-14 li-col-14 s-col-14" type="text" name="username" placeholder="Nom d’utilisateur" value="">

						<input class="x608-w mi-col-14 li-col-14 s-col-14" type="password" name="password" placeholder="Mot de passe" value="">

						<input class="x608-w mi-col-14 li-col-14 s-col-14" type="text" name="dbName" placeholder="Nom de la base de donnée" value="">

						<input class="x608-w mi-col-14 li-col-14 s-col-14" type="text" name="dbPrefix" placeholder="Préfixe des tables" value="">
						
						
					</div>
			
					<div class="x80-h  padding-x32 gui flex start row wrap">

						<div class="stage-buttons prev"><input type="button" value="&nbsp;" class="go" handler-click="Stage.Prev"></div>

						<div class="stage-buttons next"><input type="submit" value="&nbsp;" class="go" handler-click="Stage.Next" setup-check-point="db"></div>

						<!-- <div class="stage-buttons next align-right"><input type="button" value="Ignorer" handler-click="Stage.Next" setup-check-point="skip:DataBase"></div> -->
						
					</div>
				

				</form>

			<!-- Base de donnée / FIN -->




			






			<!-- Administrateur / DEBUT -->

				<form action="#" method="post" onsubmit="return false;" class="page vw10 vh10 gui column" id="setup-admin">

					<div class="x92-h gui flex start column padding-x32">

						<div class="page-title xh1 text-ellipsis">Administrateur</div>

						<div class="page-about padding-l-x12 text-x16">Créez un super administrateur pour la gestion de GGN Frameworks.</div>
						
					</div>

					<div class="col-0 padding-x32 gui flex column page-content">
							
						<input class="x608-w mi-col-14 li-col-14 s-col-14" type="text" name="username" placeholder="Nom d’utilisateur" value="">

						<input class="x608-w mi-col-14 li-col-14 s-col-14" type="password" name="password" placeholder="Mot de passe" value="">

						
					</div>
			
					<div class="x80-h  padding-x32 gui flex start row wrap">

						<div class="stage-buttons prev"><input type="button" value="&nbsp;" class="go" handler-click="Stage.Prev"></div>

						<div class="stage-buttons next"><input type="submit" value="&nbsp;" class="go" handler-click="Stage.Next" setup-check-point="admin"></div>

						<!-- <div class="stage-buttons next align-right"><input type="button" value="Ignorer" handler-click="Stage.Next" setup-check-point="skip:Admin"></div> -->
						
					</div>
				

				</form>

			<!-- Administrateur / FIN -->




			






			<!-- Initialisation / DEBUT -->
					
				<div class="page vw10 vh10 gui column" id="setup-install">


					<div class="col-0 gui flex center column">
						
						<div class="col-0 gui flex center">

							<img src="<?php echo $_CACHES_FILE; ?>svg:ggn.framework.logo" alt="" style="width:192px;height:192px;">
							
						</div>

						<div class="progress-box x720-w mi-col-14 li-col-14 s-col-12">


							<div class="labels gui flex row end padding-b-x4">

								<div class="stage col-0 text-x20 gui flex end column">Initialisation...</div>

								<div class="percent x64-w text-x32 gui flex end row"></div>
								
							</div>

							
							<div class="progress-bar gui pos-relative">

								<div class="cache gui pos-absolute _w0 _h10 bg-dark gui-fx"></div>

								<div class="track gui pos-absolute _w0 _h10 bg-primary gui flex row end gui-fx"></div>
								
							</div>

							
							<div class="status gui flex row start padding-t-x4">

								<div class="color-light-d info">Patientez...</div>
								
							</div>

							
						</div>
						
						
					</div>

					<div class="x128-h padding-b-x32 gui flex center">

						<img src="<?php echo $_CACHES_FILE; ?>png:ggn.copryght.logo" alt="" style="width:331px;height:auto;" class="">
						
					</div>
			
					
					
				</div>

			<!-- Initialisation / FIN -->




			






			<!-- Fin de l'installation / DEBUT -->
					
				<div class="page vw10 vh10 gui column" id="setup-finish">


					<div class="col-0 gui flex center column">
						
						<div class="col-0 gui flex center">

							<img src="<?php echo $_CACHES_FILE; ?>svg:ggn.framework.logo" alt="" style="width:192px;height:192px;">
							
						</div>

						<div class="install-result-label text-x32">

							Installé

						</div>
						
						
					</div>


					<div class="x80-h  padding-x32 gui flex center row wrap">

						<div class="stage-buttons prev gui flex center"><span class="text-x24 cursor-pointer" handler-click="App.Reload">Nouvelle installation</span><input type="button" value="&nbsp;" class="go cursor-pointer" handler-click="App.Reload"></div>

						<div class="stage-buttons next gui flex center"><input type="submit" value="&nbsp;" class="go cursor-pointer" handler-click="Open.New"><span class="text-x32 cursor-pointer" handler-click="Open.New">Ouvrir</span></div>
						
					</div>

					
					
				</div>

			<!-- Fin de l'installation / FIN -->






			



		</div>

	<!-- Feuille GGN / FIN -->




	<script type="text/javascript" src="<?php echo $_CACHES_FILE; ?>js:framework"></script>

	<script type="text/javascript">

		(function(G){

			window.GGNSetup = {

				varsion : '160910.1726'

				, Stages : 'license choosePkg installDir db admin install finish'

				, Bx : {}

				, Save : {}

				, Current : -1

				, Last : false

				, GetKey : function(p){

					var o = this, out=false;

					G.foreach(o.Stages.split(' '), function(part, k){

						if(part==p){out = k * 1;}

					});

					return out;

				}

				, Next : function(){

					var o = this

						, stages = o.Stages.split(' ')

						, k = (o.Current||0) + 1

						, p = stages[k]

					;

					o.Open(p)

				}

				, Prev : function(){

					var o = this

						, stages = o.Stages.split(' ')

						, k = (o.Current||0) - 1

						, p = stages[k]

					;

					o.Open(p)

				}

				, Open : function(p){

					var o = this

						, sid = '#setup-', Sd

						, last = o.Last

						, p = p||'undefined'

					;

					sid += p;

					Sd = G(sid);


					if(isObj(Sd)){


						G(function(){

							Sd.addClass('flex');
						
							G(function(){

								Sd.addClass('show');

								o.Last = Sd;

								o.Current = o.GetKey(p);

							}).timeout(305);

						}).timeout(100);


						if(isObj(last)){

							last.removeClass('show');

							G(function(){

								last.removeClass('flex');

							}).timeout(300);

						}




					}

					else{

						var h = 'Impossible de trouver la partie ';

						h+=p;

						GToast(h).error();

					}

				}

				, Init : function(){

					var o = this;



					/* Les elements HTML / DEBUT */

						o.Bx.Body = G.getDocElement();

						o.Bx.SSn = G('#splash-screen');

						o.Bx.Stg = G('.progress-box .labels .stage');

						o.Bx.lper = G('.progress-box .labels .percent');

						o.Bx.cache = G('.progress-bar .cache');

						o.Bx.track = G('.progress-bar .track');

						o.Bx.stu = G('.status .info');

					/* Les elements HTML / FIN */




					/* Actions des boutons des étapes / DEBUT */

						GAction('handler:App.Reload').listen('click', function(){

							history.go(0);

						});

						GAction('handler:Open.New').listen('click', function(){

							var U = o.ScriptURL;

							U += o.URLDone;

							location.href = U;

						});

						GAction('handler:Stage.Prev').listen('click', function(){

							o.Prev();

						});

						GAction('handler:Stage.Next').listen('click', function(e){

							var check = e.attrib('setup-check-point')||false

								, stages = o.Stages.split(' ')

								, k = (o.Current||0)

								, p = stages[k]

								, sid = '#setup-'

								, part, form

								, icheck = (check||'').split(':')

								, lim = stages.length - 1

								, triggerSetUp = (k+2) == lim;

							;


							sid += p;

							part = G(sid);

							form = (isObj(part) && part.tagName) ? ((part.tagName.lower()=='form') ? part : false) : false;



							if(icheck[0] == 'skip'){

								if(icheck[1]){

									if(o.Save[icheck[1]]){

										o.Save[icheck[1]] = undefined;

									}

								}

								if(triggerSetUp){

									G(function(){o.SetUp();}).timeout(1000);

								}

								o.Next();

								return false;

							}



							if(check == 'install.dir'){

								if(form){

									o.Save.InstallDir = form.installDir.value||false;

								}

								else{

									GToast('Aucun formulaire détecté').warning();

								}

							}



							if(check == 'db'){

								if(form){

									var dat = form.strToSend()

										, jx

									;


									if(

										(form.host.value||'').isEmpty()

										|| (form.username.value||'').isEmpty()

										|| (form.dbName.value||'').isEmpty()

									){

										GToast('Veuillez remplir les champs vident').warning();

										return false;

									}



									jx = GAjax({

										URI : '?do=check.sql.db'

										, mode : 'POST'

										, data : dat

										, success : function(){

											var res = this.xhr.responseText, obj = false;

											try{obj = JSON.parse(res); } catch(e){}

											if(!isObj(obj)){
												
												GToast('Impossible de lire la reponse du serveur').error();

												return false;

											}

											if(!obj['sql-db-check']){
												
												GToast('Reponse introuvable').warning();

												return false;

											}

											if(obj['sql-db-check'] !== true){

												var r = 'Reponse du serveur : ';

													r+=obj['sql-db-check'];
												
												GToast(r).warning();

												return false;

											}

											if(obj['sql-db-check'] === true){

												o.Save.DataBase = {

													host : form.host.value||false

													, username : form.username.value||false

													, password : form.password.value||''

													, dbName : form.dbName.value||false

													, dbPrefix : form.dbPrefix.value||false

												};

												o.Next();

											}


										}

										, fail : function(){GToast('Script de vérification introuvable').warning();}

										, error : function(){GToast('Erreur lors du chargement').error();}

									})

										.XHR()

										.send()

									;


									return false;

								}

								else{

									GToast('Aucun formulaire détecté').warning();

								}

							}




							if(check == 'choosePkg'){

								if(form){


									if(

										(form.boot.value||'').isEmpty()

									){

										GToast('Veuillez remplir les champs vident').warning();

										return false;

									}

									o.Save.BootGGN = form.boot.value || 'Boot.GGN';

								}

								else{

									GToast('Aucun formulaire détecté').warning();

								}

							}



							if(check == 'admin'){

								if(form){


									if(

										(form.username.value||'').isEmpty()

										|| (form.password.value||'').isEmpty()

									){

										GToast('Veuillez remplir les champs vident').warning();

										return false;

									}

									o.Save.Admin = {

										'username' : form.username.value||false

										, 'password' : form.password.value||false

									};

									G(function(){o.SetUp();}).timeout(1000);

								}

								else{

									GToast('Aucun formulaire détecté').warning();

								}

							}



							o.Next();

						});


					/* Actions des boutons des étapes / FIN */


					GEvent(window).listen('load', function(){

						G(function(){

							o.Next();

							o.Bx.SSn.removeClass('show');

							G(function(){o.Bx.SSn.hide();}).timeout(300);

						}).timeout(1000);

					});

					o.Bx.Body.scrollTop = 0;

				}

				, CurrentPercentSteps : 0

				, PercentSteps : 5

				, PercentStep : function(){

					var o = this,p = o.CurrentPercentSteps;

					p++;

					p/=o.PercentSteps;

					p*=100;

					p=(p||0).virgule(1);


					p+='%';


					o.Bx.lper.html(p);

					o.Bx.track.css({width:p});

					o.CurrentPercentSteps++;


				}

				, SetUp : function(){

					var o = this;

					// o.Bx.Stg, o.Bx.lper, o.Bx.cache, o.Bx.track, o.Bx.mper, o.Bx.stu;

					o.Bx.cache.css({width:'100%'});

					G(function(){

						o.Bx.Stg.html('Installation...');

						o.Bx.stu.html('Copie des fichiers...');


						o.Deployment(

							function(res){

							o.PercentStep();

								var h = '';

								if(res['deployed']===true){

									o.ScriptURL = res['ScriptURL']||false;

									o.Bx.Stg.html('Paramètres de la base de donnée...');

									o.Bx.stu.html('Patientez...');

									o.DBSettings(

										function(res){

											o.PercentStep();

											if(res['DBSettings']===true){

												o.AdminCreator = res['AdminCreator'];

												o.URLDone = res['URLDone'];

												o.Bx.Stg.html('Construction des requetes SQL...');

												o.Bx.stu.html('En cours...');

												o.BuildSQLQueries(

													function(res){

														o.PercentStep();

														if(isObj(res['SQLQueries']||false)){

															o.Bx.Stg.html('Execution des requetes SQL...');

															o.SQLQuery(

																res['SQLQueries']

																, 0

																, function(){

																	o.PercentStep();

																	o.Bx.Stg.html('Création du Super-Administrateur');

																	o.Bx.stu.html('Un instant...');


																	o.CreateAdmin(

																		function(res){

																			if(res['response']===true){

																				o.PercentStep();

																				o.Bx.Stg.html('Nettoyage du Setup du script');

																				o.Bx.stu.html('Une dernière étape');

																				o.ClearSetup(

																					function(){

																						o.Bx.Stg.html('Fin de l\'installation');

																						o.Bx.stu.html('Installation terminé');
																						
																						G(function(){o.Next();}).timeout(500);

																					}

																					, function(){GToast('Erreur lors du nettoyage').error();}

																				);

																				

																			}

																			else{GToast('Erreur lors de la création de compte').error();}

																		}

																		, function(){GToast('Erreur lors de l\'execution').error();}

																	);

																}

																, function(){GToast('Erreur lors de l\'execution').error();}

															);

														}

														else{GToast('Impossible de monter les requetes SQL').error();}


													}

													, function(){GToast('Erreur lors de l\'execution').error();}

												);


											}

											else{

												h='une Erreur est survenue lors du deploiement';

												if(res['DBSettings'] == 'distributor.not.found'){h='le distributeur SQL est introuvable';}

												if(res['DBSettings'] == 'manifest.not.found'){h='le manifest SQL est introuvable';}

												GToast(h).error();

											}
											
										}

										, function(){GToast('Erreur lors de l\'execution').error();}

									);

								}

								else{

									h='une Erreur est survenue lors du deploiement';

									if(res['deployed'] == 'boot.not.found'){h='le Boot.GGN est introuvable';}

									GToast(h).error();

								}

							}

							, function(){GToast('Erreur lors de l\'execution').error();}

						);

					}).timeout(500);

				}

				, Deployment : function(success, error){

					var o = this

						, jx

						, dat='dir='

						, success = (isFunction(success||'')) ? success : G.F()

						, error = (isFunction(error||'')) ? error : G.F() 

					;

					dat+=o.Save.InstallDir;

					dat+='&boot-ggn='; dat+=o.Save.BootGGN;

					o.Get('?do=deployment', dat, success, error);

					return o;

				}


				, DBSettings : function(success, error){

					var o = this

						, jx

						, dat=''

						, success = (isFunction(success||'')) ? success : G.F()

						, error = (isFunction(error||'')) ? error : G.F()

					;


					if(o.Save['DataBase']){

						dat+='host='; dat+=o.Save.DataBase.host||'';

						dat+='&username='; dat+=o.Save.DataBase.username||'';

						dat+='&password='; dat+=o.Save.DataBase.password||'';

						dat+='&dbName='; dat+=o.Save.DataBase.dbName||'';

						dat+='&dbPrefix='; dat+=o.Save.DataBase.dbPrefix||'';

						dat+='&dir='; dat+=o.Save.InstallDir||'';


						o.Get('?do=build.db.settings', dat, success, error);

					}

					else{

						success({'DBSettings':true});

					}

					return o;

				}


				, ClearSetup : function(success, error){

					var o = this

						, jx

						, dat=''

						, success = (isFunction(success||'')) ? success : G.F()

						, error = (isFunction(error||'')) ? error : G.F()

					;

					dat+='&dir='; dat+=o.Save.InstallDir||'';

					o.Get('?do=clear.script.setup', dat, success, error);

					return o;

				}


				, CreateAdmin : function(success, error){

					var o = this

						, jx

						, dat=''

						, success = (isFunction(success||'')) ? success : G.F()

						, error = (isFunction(error||'')) ? error : G.F()

						, URI = o.ScriptURL

						, AC = o.AdminCreator||false

					;


					if(o.Save['Admin']){

						dat+='&username='; dat+=o.Save.Admin.username||'';

						dat+='&password='; dat+=o.Save.Admin.password||'';

						URI+=AC;


						o.Get(URI, dat, success, error);

					}

					else{

						success({'response':true});

					}


					return o;

				}


				, BuildSQLQueries : function(success, error){

					var o = this

						, jx

						, dat=''

						, success = (isFunction(success||'')) ? success : G.F()

						, error = (isFunction(error||'')) ? error : G.F()

					;


					if(o.Save['DataBase']){

						dat+='&dir='; dat+=o.Save.InstallDir||'';

						dat+='&dbPrefix='; dat+=o.Save.DataBase.dbPrefix||'';


						o.Get('?do=build.db.queries', dat, success, error);

					}

					else{

						success({'SQLQueries':[]});

					}

					return o;

				}



				, SQLQuery : function(queries, k, success, error){

					var o = this

						, jx

						, dat=''

						, success = (isFunction(success||'')) ? success : G.F()

						, error = (isFunction(error||'')) ? error : G.F()

						, query = queries[k]||false

					;


					if(o.Save['DataBase']){

						if(query){

							var h0 = '';

							h0 += (k + 1);

							h0 += ' /';

							h0 += queries.length;

							o.Bx.stu.html(h0);

						
							dat+='host='; dat+=o.Save.DataBase.host||'';

							dat+='&username='; dat+=o.Save.DataBase.username||'';

							dat+='&password='; dat+=o.Save.DataBase.password||'';

							dat+='&dbName='; dat+=o.Save.DataBase.dbName||'';

							dat+='&query='; dat+=query;


							o.Get('?do=exec.db.queries', dat, function(res){


								if(res['query']===true){

									G(function(){o.SQLQuery(queries, k+1, success, error); }).timeout(10);

								}

								else{

									GToast('Erreur : Impossible d\'executer cette requette')
										
								}



							}, error);

						}

						else{

							success();

						}


					}

					else{

						success({'SQLQueries':true});

					}

					return o;

				}



				, Get : function(URI, Data, success, error){

					var o = this

						, jx

						, dat=''

						, success = (isFunction(success||'')) ? success : G.F()

						, error = (isFunction(error||'')) ? error : G.F()

					;


					jx = GAjax({

						URI : URI

						, mode : 'POST'

						, data : Data

						, success : function(){

							var res = this.xhr.responseText, obj = false;

							console.log('//////////////////\n','GGN.Setup xHR Response : \n', URI, '\n', this.xhr.responseText, '\n//////////////////');

							try{obj = JSON.parse(res); } catch(e){console.log('//////////////////\n','GGN.Setup xHR Error : \n', URI, '\n', e, '\n//////////////////');}

							G(function(){success(obj);}).timeout(100);

						}

						, fail : function(){error('fail');GToast('Script introuvable').warning();}

						, error : function(){error('error');GToast('Erreur lors du chargement').error();}

					})

						.XHR()

						.send()

					;

				}


			};


			GGNSetup.Init();

		})(G);
		
	</script>


</body>

</html>


<?php
	
	
		
	/*
	
		Interface : Renseignement des informations / FIN ////////////////////////////////

	*/
	
	
?>
