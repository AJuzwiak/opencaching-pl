<?php 

## do zrobienia:
## - walidacja czy z posta są przekazywane wartości numeryczne (malo istotne, najwyzej zwroci ze wynik zly)
## - lista keszy w opensprawdzaczu - przewijanie do dalszych (na razie obsluguje 100 keszynek)
## layout jak mycache.php


//prepare the templates and include all neccessary
require_once('./lib/common.inc.php');
require_once 'pagination_class.php';
require_once 'opensprawdzacz_classes.php';

//Preprocessing
if ($error == false)
{
	// czy user zalogowany ?
	if ($usr == false)
	{
		// nie zalogowany wiec przekierowanie na strone z logowaniem
		$target = urlencode(tpl_get_current_page());
		tpl_redirect('login.php?target='.$target);
	}
	else
	{
		// wskazanie pliku z kodem html ktory jest w tpl/stdstyle/
		$tplname = 'opensprawdzacz';
		
        $OpensprawdzaczSetup = New OpensprawdzaczSetup();
        tpl_set_var('os_script', $OpensprawdzaczSetup->scriptname);
		
		tpl_set_var("sekcja_5_start",'<!--');
		tpl_set_var("sekcja_5_stop",'-->');

		// jeśli istnieje $_POST['stopnie_N'] znaczy że użytkownik wpisał współrzędne
		// sekcja 3 - sprawdzająca poprawność wpisanych współrzędnych

		if (isset($_POST['stopnie_N']))
		{
			tpl_set_var("sekcja_3_start",'');
			tpl_set_var("sekcja_3_stop",'');
			tpl_set_var("sekcja_2_start",'<!--');
			tpl_set_var("sekcja_2_stop",'-->');
			tpl_set_var("sekcja_1_start",'<!--');
			tpl_set_var("sekcja_1_stop",'-->');

			// check how many times user tried to guess answer
			// sprawdzamy czy user nie używa brutal force

			tpl_set_var("ile_prob", $OpensprawdzaczSetup->ile_prob);
			tpl_set_var("ile_czasu", $OpensprawdzaczSetup->limit_czasu);

			if (isset ($_SESSION['opensprawdzacz_licznik']))
			{
				if ($_SESSION['opensprawdzacz_licznik'] >= $OpensprawdzaczSetup->ile_prob)
				{
					$czas_ostatniej_proby = $_SESSION['opensprawdzacz_czas'];
					$czas_teraz = date('U');
					$czas_jaki_uplynal = $czas_teraz - $czas_ostatniej_proby;
					tpl_set_var("czasss1", $czas_jaki_uplynal);
					if ($czas_jaki_uplynal > $OpensprawdzaczSetup->limit_czasu*60)
					{
						$_SESSION['opensprawdzacz_licznik'] = 1;
						$_SESSION['opensprawdzacz_czas'] = $czas_teraz;
					}
					else
					{
						// $_SESSION['opensprawdzacz_czas'] = date('U');
						$czas_jaki_uplynal = round (60 - ($czas_jaki_uplynal / 60));
						tpl_set_var("licznik_zgadywan", $_SESSION["opensprawdzacz_licznik"]);
						tpl_set_var("test1", tr(os_zgad));
						tpl_set_var("wynik", '');
						tpl_set_var("ikonka_yesno", '<image src="tpl/stdstyle/images/blue/opensprawdzacz_stop.png" />');
						tpl_set_var("sekcja_4_start", '');
						tpl_set_var("sekcja_4_stop", '');
						tpl_set_var("twoje_ws", tr('os_ma_max').' '.$ile_prob.' '. tr('os_ma_na') .' '.$limit_czasu.' '.tr('os_godzine') .'<br /> '.tr('os_mus').' '. $czas_jaki_uplynal .' '. tr('os_minut_end'));
						goto endzik;
					}
				}
				else
				{
					tpl_set_var("sekcja_4_start", '<!--');
					tpl_set_var("sekcja_4_stop", '-->');
					$czasss = $_SESSION['opensprawdzacz_czas'];
					// tpl_set_var("czasss1", $czasss);
					$_SESSION['opensprawdzacz_licznik'] = $_SESSION['opensprawdzacz_licznik'] + 1;
					$_SESSION['opensprawdzacz_czas'] = date('U');
					$czasss = ($_SESSION['opensprawdzacz_czas'] - $czasss);
					tpl_set_var("licznik_zgadywan", $_SESSION["opensprawdzacz_licznik"]);
					tpl_set_var("czasss1", $czasss);
					// tpl_set_var("czasss2", $_SESSION['opensprawdzacz_czas']);
				}
			}
			else
			{
				$_SESSION['opensprawdzacz_licznik'] = 1;
				tpl_set_var("licznik_zgadywan", $_SESSION["opensprawdzacz_licznik"]);
				tpl_set_var("sekcja_4_start",'<!--');
				tpl_set_var("sekcja_4_stop", '-->');
			}
			//koniec sekcji kontrolującej brutal force

			// get data from post.
			$stopnie_N = mysql_real_escape_string($_POST['stopnie_N']);
			$minuty_N  = mysql_real_escape_string($_POST['minuty_N']);
			$stopnie_E = mysql_real_escape_string($_POST['stopnie_E']);
			$minuty_E  = mysql_real_escape_string($_POST['minuty_E']);
			$cache_id  = mysql_real_escape_string($_POST['cacheid']);

            if ($stopnie_N == '') $stopnie_N = 0;
            if ($stopnie_E == '') $stopnie_E = 0;
            if ($minuty_N  == '') $minuty_N  = 0;
            if ($minuty_E  == '') $minuty_E  = 0;

			// converting from HH MM.MMM to DD.DDDDDD

			$wspolN = new convertLangLat($stopnie_N, $minuty_N);
			$wspolE = new convertLangLat($stopnie_E, $minuty_E);

			// geting data from database				
			$conn = new PDO("mysql:host=".$opt['db']['server'].";dbname=".$opt['db']['name'],$opt['db']['username'],$opt['db']['password']);
			$pyt = "SELECT `waypoints`.`wp_id`,
			               `waypoints`.`type`,
			               `waypoints`.`longitude`,
			               `waypoints`.`latitude`,
			               `waypoints`.`status`,
			               `waypoints`.`type`,
			               `waypoints`.`opensprawdzacz`,
			               `opensprawdzacz`.`proby`,
			               `opensprawdzacz`.`sukcesy`
			        FROM   `waypoints`, `opensprawdzacz`
			        WHERE  `waypoints`.`cache_id`='$cache_id'
			           AND `waypoints`.`opensprawdzacz` = 1
			           AND `waypoints`.`type` = 3
			           AND `waypoints`.`cache_id`= `opensprawdzacz`.`cache_id`
			       ";

			$dane   = $conn->query($pyt) or die("failed!");
			$dane = $dane->fetch(PDO::FETCH_ASSOC);
				
			$licznik_prob = $dane['proby']+1;

			$wspolrzedneN_wzorcowe = $dane['latitude'];
			$wspolrzedneE_wzorcowe = $dane['longitude'];


			// comparing data from post with data from database
			if (
					(($wspolrzedneN_wzorcowe - $wspolN->CoordsDecimal) < 0.00001) &&
					(($wspolrzedneN_wzorcowe - $wspolN->CoordsDecimal) > -0.00001)
					&&
					(($wspolrzedneE_wzorcowe - $wspolE->CoordsDecimal) < 0.00001) &&
					(($wspolrzedneE_wzorcowe - $wspolE->CoordsDecimal) > -0.00001)

					
			)
	  {
	  	//puzzle solved - resukt ok
	  	$licznik_sukcesow = $dane['sukcesy']+1;
	  	
	  	try 
	  	{
	  		$pdo = new PDO("mysql:host=".$opt['db']['server'].";dbname=".$opt['db']['name'],$opt['db']['username'],$opt['db']['password']);
	  		$pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	  		$updateCounters = $pdo -> exec("UPDATE `opensprawdzacz` SET `proby`=".$licznik_prob.",`sukcesy`=".$licznik_sukcesow."  WHERE `cache_id` = ".$cache_id);
	  	}
	  	catch(PDOException $e)
	  	{
	  		echo "Error PDO Library: ($OpensprawdzaczSetup->scriptname) " . $e -> getMessage();
	  		exit;
	  	}
	  	tpl_set_var("test1", tr('os_sukces'));
	  	tpl_set_var("ikonka_yesno", '<image src="tpl/stdstyle/images/blue/opensprawdzacz_tak.png" />');
	  }
	  else
	  {
	  	//puzzle not solved - restult wrong
	  	
	  	try
	  	{
	  		$pdo = new PDO("mysql:host=".$opt['db']['server'].";dbname=".$opt['db']['name'],$opt['db']['username'],$opt['db']['password']);
	  		$pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	  		$updateCounters = $pdo -> exec("UPDATE `opensprawdzacz` SET `proby`='$licznik_prob'  WHERE `cache_id` = $cache_id");
	  	}
	  	catch(PDOException $e)
	  	{
	  		echo "Error PDO Library: ($OpensprawdzaczSetup->scriptname) " . $e -> getMessage();
	  		exit;
	  	}
	  	
	  	
	  	// sql("UPDATE `opensprawdzacz` SET `proby`='$licznik_prob'  WHERE `cache_id` = $cache_id");
	  	tpl_set_var("test1", tr('os_fail'));
	  	tpl_set_var("ikonka_yesno", '<image src="tpl/stdstyle/images/blue/opensprawdzacz_nie.png" />');
	  }
	  //tpl_set_var("wynik", $wspolrzedneN.'/'.$wspolrzedneN_wzorcowe.'<br>'.$wspolrzedneE.'/'. $wspolrzedneE_wzorcowe);
	  tpl_set_var("wynik",'');



	  // tpl_set_var("wsp_NS", );
	  // tpl_set_var("wsp_EW", );
	  tpl_set_var("twoje_ws", tr('os_twojews') . '<b> N '. $stopnie_N.'°'.$minuty_N . '</b>/<b> E '. $stopnie_E.'°'.$minuty_E .'</b>');
	  tpl_set_var("cache_id",  $cache_id);


	  goto endzik;
		}


		// get cache waypoint from url
		if (isset ($_GET['op_keszynki']))
		{
			$cache_wp = mysql_real_escape_string($_GET['op_keszynki']);
			$cache_wp = strtoupper($cache_wp);
		}
		else
		{
			$formularz = '
					<form action="'. $OpensprawdzaczSetup->scriptname .  '" method="get">
					'.tr('os_podaj_waypoint').':
							<input type="text" name="op_keszynki" maxlength="6"/>
							<button type="submit" name="przeslanie_waypointa" value="'.tr('submit').'" style="font-size:14px;width:160px"><b>'.tr('submit').'</b></button>
									</form>
									';
			if (isset($_GET['sort']))
			{
				$sort_tmp = mysql_real_escape_string($_GET['sort']);
				switch ($sort_tmp) {
					case 'autor':
						$sortowanie = '`user`.`username`';
						break;
					case 'nazwa':
						$sortowanie = '`caches`.`name`';
						break;
					case 'wpt':
						$sortowanie = '`caches`.`wp_oc`';
						break;
					case 'szczaly':
						$sortowanie = '`opensprawdzacz`.`proby`';
						break;
					case 'sukcesy':
						$sortowanie = '`opensprawdzacz`.`sukcesy`';
						break;

					default:
						$sortowanie = '`caches`.`name`';
						break;
				}
			}
			else $sortowanie = '`caches`.`name`';
			 
			
			$zapytajka = "
					
			SELECT `waypoints`.`cache_id`,
			`waypoints`.`type`,
			`waypoints`.`stage`,
			`waypoints`.`desc`,
			`caches`.`name`,
			`caches`.`wp_oc`,
			`caches`.`user_id`,
			`caches`.`type`,
			`caches`.`status`,
			`user`.`username`,
			`cache_type`.`sort`,
			`cache_type`.`icon_small`,
			`opensprawdzacz`.`proby`,
			`opensprawdzacz`.`sukcesy`
			FROM   `waypoints`
			LEFT JOIN   `opensprawdzacz`
			ON   `waypoints`.`cache_id` = `opensprawdzacz`.`cache_id`,
			`caches`, `user`, `cache_type`
			WHERE   `waypoints`.`opensprawdzacz` = 1
			AND   `waypoints`.`type` = 3
			AND   `caches`.`type` = `cache_type`.`id`
			AND   `caches`.`user_id` = `user`.`user_id`
			AND   `waypoints`.`cache_id` = `caches`.`cache_id`
			ORDER BY   $sortowanie
			LIMIT   0, 100
					
			";
			

			$status = array (
					'1' => '<img src="tpl/stdstyle/images/log/16x16-found.png" border="0" alt="Gotowa do szukania">',
					'2' => '<img src="tpl/stdstyle/images/log/16x16-temporary.png" border="0" alt="Tymczasowo niedostępna">',
					'3' => '<img src="tpl/stdstyle/images/log/16x16-dnf.png" border="0" alt="zarchiwizowana">',
					'4' => '<img src="tpl/stdstyle/images/log/16x16-temporary.png" border="0" alt="Ukryta do czasu weryfikacji">',
					'5' => '<img src="tpl/stdstyle/images/log/16x16-temporary.png" border="0" alt="jeszcze niedostępna">',
					'6' => '<img src="tpl/stdstyle/images/log/16x16-dnf.png" border="0" alt="Zablokowana przez COG">'
			);

			$conn = new PDO("mysql:host=".$opt['db']['server'].";dbname=".$opt['db']['name'],$opt['db']['username'],$opt['db']['password']);
			$conn -> query( 'SET CHARSET utf8' );
			$keszynki_opensprawdzacza   = $conn->query($zapytajka)->fetchAll() or die("failed!");
			$ile_keszynek = count($keszynki_opensprawdzacza);
			
			
			$pag = new Pagination();
			// $dane = array("hej","dupa","laska", "scierwo");

			$numbers = $pag->Paginate($keszynki_opensprawdzacza,25);
			$result  = $pag->fetchResult();
			/*
			foreach ($result as $r)
			{
				echo "<div>aa$r</div>";
			}
			*/
			$paginacja = ' ';
			foreach ($numbers as $num)
			{
				if (isset ($_GET["sort"])) $sort = '&sort='.$_GET["sort"];
				else $sort = '';
			$paginacja .= '<a href="'.$OpensprawdzaczSetup->scriptname.'?page='.$num.$sort.'">['.$num.']</a> ';
            }
			
			
			$tabelka_keszynek = '';
			$proby = 0;
			$trafienia = 0;

			// foreach ($keszynki_opensprawdzacza as $dane_keszynek )
			foreach ($result as $dane_keszynek )
			{
				// $dane_keszynek = mysql_fetch_array($keszynki_opensprawdzacza);
				$proby = $proby + $dane_keszynek['proby'];
				$trafienia  = $trafienia + $dane_keszynek['sukcesy'];

				if (($dane_keszynek['status'] == 1) || ($dane_keszynek['status'] == 2))

					$tabelka_keszynek .= '
						<tr>
							<td><a class="links" href="viewcache.php?wp='.$dane_keszynek['wp_oc'].'">'.$dane_keszynek['wp_oc'].'</a></td>
							<td><a class="links" href="'.$OpensprawdzaczSetup->scriptname.'?op_keszynki='.$dane_keszynek['wp_oc'].'"> '. $dane_keszynek['name'] . '</a> </td>
							<td><a href="viewcache.php?wp='.$dane_keszynek['wp_oc'].'"><img src="tpl/stdstyle/images/'.$dane_keszynek['icon_small'].'" /></a></td>
							<td align="center">'.$status[$dane_keszynek['status']].'</td>
							<td><a href="viewprofile.php?userid='.$dane_keszynek['user_id'].'">'.$dane_keszynek['username'] . '</td>
							<td align="center">'.$dane_keszynek['proby'] . '</td>
							<td align="center">'.$dane_keszynek['sukcesy'] . '</td>
						</tr>';
			}

			$tabelka_keszynek .= '
				<tr><td colspan="7"><img src="tpl/stdstyle/images/blue/dot_blue.png" height="1" width="100%"/></td></tr><tr>
					<td><img src="/tpl/stdstyle/images/misc/16x16-info.png" /></td>
					<td>Keszynek w Opensprawdzaczu, legenda: </td>
					<td>'.$ile_keszynek	.'</td>
					<td align="center">
							'.$status[1].'<br />'.$status[2].'
					</td>
					<td>
						(Gotowa do szukania)<br />
						(Tymczasowo niedostępna)
					</td>
					<td align="center">'.$proby.'</td>
					<td align="center">'.$trafienia.'</td>
				</tr>
			</table>';
			
			$tabelka_keszynek .= '<br /><br /> ' . $paginacja;	

			tpl_set_var("sekcja_1_start",'');
			tpl_set_var("sekcja_1_stop", '');
			tpl_set_var("sekcja_2_start",'<!--');
			tpl_set_var("sekcja_2_stop", '-->');
			tpl_set_var("sekcja_3_start",'<!--');
			tpl_set_var("sekcja_3_stop", '-->');
			tpl_set_var("sekcja_4_start",'<!--');
			tpl_set_var("sekcja_4_stop", '-->');
			tpl_set_var("sekcja_formularz_opensprawdzacza_start", '<!--');
			tpl_set_var("sekcja_formularz_opensprawdzacza_stop", '');
			tpl_set_var("formularz",$formularz);
			tpl_set_var("keszynki",$tabelka_keszynek);
			
			goto endzik;
		}


		// sekcja 2 (wyswietla dane kesza i formularz do wpisania współrzędnych)
		// pobieramy dane z bazy


		$rs = sql("SELECT `caches`.`name`,
				`caches`.`cache_id`,
				`caches`.`type`,
				`caches`.`user_id`,
				`cache_type`.`icon_large`,
				`user`.`username`
				FROM   `caches`, `user`, `cache_type`
				WHERE  `caches`.`user_id` = `user`.`user_id`
				AND    `caches`.`type` = `cache_type`.`id`
				AND    `caches`.`wp_oc` = '&1'",$cache_wp);



		// przekaznie wynikow w postaci zmiennych do pliku z kodem html
		tpl_set_var("sekcja_1_start",'<!--');
		tpl_set_var("sekcja_1_stop",'-->');
		tpl_set_var("sekcja_2_start",'');
		tpl_set_var("sekcja_2_stop",'');
		tpl_set_var("sekcja_3_start",'<!--');
		tpl_set_var("sekcja_3_stop",'-->');
		tpl_set_var("sekcja_4_start",'<!--');
		tpl_set_var("sekcja_4_stop", '-->');

		$czyjest = mysql_num_rows($rs);
		if ($czyjest == 0)
		{
			tpl_set_var("ni_ma_takiego_kesza", tr(ni_ma_takiego_kesza));
			tpl_set_var("sekcja_2_start",'<!--');
			tpl_set_var("sekcja_2_stop",'-->');
			tpl_set_var("sekcja_5_start",'');
			tpl_set_var("sekcja_5_stop",'');
			goto endzik;
		}

		$record = mysql_fetch_array($rs);
		$cache_id = $record['cache_id'];

		tpl_set_var("wp_oc",$cache_wp);
		tpl_set_var("ikonka_keszyny", '<img src="tpl/stdstyle/images/'.$record['icon_large'].'" />');
		tpl_set_var("cacheid",$record['cache_id']);
		tpl_set_var("ofner",$record['username']);
		tpl_set_var("cachename",$record['name']);
		tpl_set_var("id_uzyszkodnika", $record['user_id'] ) ;

		mysql_free_result($rs);


		$wp_rs = sql("SELECT `waypoints`.`wp_id`,
				`waypoints`.`type`,
				`waypoints`.`longitude`,
				`waypoints`.`latitude`,
				`waypoints`.`status`,
				`waypoints`.`type`,
				`waypoints`.`opensprawdzacz`
				FROM `waypoints`
				WHERE `cache_id`='&1' AND `type` = 3 ",$cache_id);

		$wp_record = sql_fetch_array($wp_rs);
		if (($wp_record['type'] == 3) && ($wp_record['opensprawdzacz'] == 1))
		{
			tpl_set_var("sekcja_formularz_opensprawdzacza_start", '');
			tpl_set_var("sekcja_formularz_opensprawdzacza_stop", '');
			tpl_set_var("okienka",'');
		}
		else
		{
			tpl_set_var("okienka", tr(os_nie_ma_w_os));
			tpl_set_var("sekcja_formularz_opensprawdzacza_start", '<!--');
			tpl_set_var("sekcja_formularz_opensprawdzacza_stop", '-->');
		}
	}
	mysql_free_result($wp_rs);
}

endzik:
// budujemy kod html ktory zostaje wsylany do przegladraki
tpl_BuildTemplate();
?>