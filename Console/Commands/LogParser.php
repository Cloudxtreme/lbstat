<?php namespace Dynatron\Lbstat\Console\Commands;

use Mssql;
use Mreschke\Helpers\Console;
use Illuminate\Console\Command;
use Mreschke\Dbal\Mssql as DbalInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class LogParser extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'dynatron:lbstat:parse';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Parse haproxy logs.';

	protected $console;
	protected $db;


	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(DbalInterface $db)
	{
		// Instantiate additional console helpers
		$this->console = new Console();

		// Initialize db
		$this->db = $db;
		$this->db->connection('dyna-sql6');

		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$c = $this->console;
		$log = $this->argument('file');
		$date = $this->argument('date');
		$tmp = "/tmp/lbstat";
		$c->exec("mkdir -p $tmp");
		$dynatronIP = '66.196.205.188';
		$egrep = "/bin/egrep";
		$sed = "/bin/sed";
		$awk = "/usr/bin/awk";


		// Trim log size by removing lines I will never analyze, like Dynacomm
		// We will use this filter to copy to our temp location too
		// We do NOT filter internal IP here yet, I want all counts first, we will filter by page clicks later
		$c->header("Filtering haproxy $log");
		$c->item("Removing uninteresting rows from $log");
		$c->exec("cat '$log' | $egrep -iv '\- \- C| 404 |dynacomm|Proxy |Pausing f|Stopping f|Stopping b|SSL handshake|wpad.dat' > $tmp/log");

		// Split log by all webaq and non-webaq entries
		$c->item("Filtering WebAQ Lines");
		$c->exec("$sed -ne '/\.weba/IbY; w $tmp/log2' -e 'b; :Y; w $tmp/webaq' $tmp/log");

		// Split log by all menus and non-menus entries
		$c->item("Filtering Menus Lines");
		$c->exec("$sed -ne '/\.dealerm/IbY; w $tmp/log3' -e 'b; :Y; w $tmp/menus' $tmp/log2");
		$c->exec("$sed -ne '/dealermenus/IbY; w $tmp/log33' -e 'b; :Y; w $tmp/menus2' $tmp/log3");
		$c->exec("cat $tmp/menus2 >> $tmp/menus; rm $tmp/menus2; mv $tmp/log33 $tmp/log3");
		$c->exec("$sed -ne '/cardinaleway/IbY; w $tmp/log33' -e 'b; :Y; w $tmp/menus2' $tmp/log3");
		$c->exec("cat $tmp/menus2 >> $tmp/menus; rm $tmp/menus2; mv $tmp/log33 $tmp/log3");

		// Split log by all carmail and non-carmail entries
		// Filter carmail before ebis since same url
		$c->item("Filtering Carmail Lines");
		$c->exec("$sed -ne '/{carmail/IbY; w $tmp/log4' -e 'b; :Y; w $tmp/carmail' $tmp/log3");

		// Split log by all vpg and non-vpg entries
		// Must come before ebis
		$c->item("Filtering VPG Lines");
		$c->exec("$sed -ne '/vpg.ebisr/IbY; w $tmp/log4a' -e 'b; :Y; w $tmp/vpg' $tmp/log4");
		
		// Split log by all ebis and non-ebis entries
		$c->item("Filtering Ebis Lines");
		$c->exec("$sed -ne '/\.ebisr/IbY; w $tmp/log5' -e 'b; :Y; w $tmp/ebis' $tmp/log4a");

		// Split log by all websa and non-websa entries
		$c->item("Filtering Websa Lines");
		$c->exec("$sed -ne '/\.webs/IbY; w $tmp/log6' -e 'b; :Y; w $tmp/websa' $tmp/log5");
		$c->exec("$sed -ne '/\webserviceadvisor/IbY; w $tmp/log66' -e 'b; :Y; w $tmp/websa2' $tmp/log6");
		$c->exec("cat $tmp/websa2 >> $tmp/websa; rm $tmp/websa2; mv $tmp/log66 $tmp/log6");
		$c->exec("$sed -ne '/schumacher/IbY; w $tmp/log66' -e 'b; :Y; w $tmp/websa2' $tmp/log6");
		$c->exec("cat $tmp/websa2 >> $tmp/websa; rm $tmp/websa2; mv $tmp/log66 $tmp/log6");

		// Split log by all wiki and non-wiki entries
		$c->item("Filtering Wiki Lines");
		$c->exec("$sed -ne '/{dynatron/IbY; w $tmp/log7' -e 'b; :Y; w $tmp/wiki' $tmp/log6");

		// Split log by all sso and non-sso entries
		$c->item("Filtering SSO Lines");
		$c->exec("$sed -ne '/sso.dyna/IbY; w $tmp/log9' -e 'b; :Y; w $tmp/sso' $tmp/log8");

		// Split log by all survey and non-surveys entries
		$c->item("Filtering Survey Lines");
		$c->exec("$sed -ne '/dynasurvey/IbY; w $tmp/log8' -e 'b; :Y; w $tmp/survey' $tmp/log7");

		// Split log by all dynaarmor and non-dynaarmor entries
		$c->item("Filtering DynaArmor Lines");
		$c->exec("$sed -ne '/dynaarmor/IbY; w $tmp/log10' -e 'b; :Y; w $tmp/dynaarmor' $tmp/log9");
		$c->exec("$sed -ne '/dynaspiff/IbY; w $tmp/log11' -e 'b; :Y; w $tmp/dynaarmor2' $tmp/log10");
		$c->exec("cat $tmp/dynaarmor2 >> $tmp/dynaarmor; rm $tmp/dynaarmor2");

		// Split log by all aaacarcare and non-aaacarcare entries
		$c->item("Filtering AAACarcare Lines");
		$c->exec("$sed -ne '/aaacarcare/IbY; w $tmp/log12' -e 'b; :Y; w $tmp/aaacarcare' $tmp/log11");

		// Split log by all profiler and non-profiler entries
		$c->item("Filtering Profiler Lines");
		$c->exec("$sed -ne '/dealerprofiler/IbY; w $tmp/log13' -e 'b; :Y; w $tmp/profiler' $tmp/log12");

		// Split log by all simmons and non-simmons entries
		$c->item("Filtering Simmons Lines");
		$c->exec("$sed -ne '/simmons/IbY; w $tmp/log14' -e 'b; :Y; w $tmp/simmons' $tmp/log13");

		// Split log by all dynacall and non-dynacall entries
		$c->item("Filtering Dynacall Lines");
		$c->exec("$sed -ne '/dynacall/IbY; w $tmp/log15' -e 'b; :Y; w $tmp/dynacall' $tmp/log14");

		// Split log by all clubs and non-clubs entries
		$c->item("Filtering Clubs Lines");
		$c->exec("$sed -ne '/dealerclubs/IbY; w $tmp/log16' -e 'b; :Y; w $tmp/clubs' $tmp/log15");

		// Split log by all castle and non-castle entries
		$c->item("Filtering Castle Lines");
		$c->exec("$sed -ne '/castle/IbY; w $tmp/log17' -e 'b; :Y; w $tmp/castle' $tmp/log16");

		// Split log by all qma and non-castle entries
		$c->item("Filtering Qma Lines");
		$c->exec("$sed -ne '/qma/IbY; w $tmp/log18' -e 'b; :Y; w $tmp/qma' $tmp/log17");




		# AAACarCare filters
		$include['aaacarcare'] = ['\.aspx', 'GET / HTTP'];
		$exclude['aaacarcare'] = ['\- \- C'];

		# Carmail filters
		$include['carmail'] = ['\.aspx', 'GET / HTTP'];
		$exclude['carmail'] = ['\- \- C', '/adrotator.aspx', '/tools/imageresizer.aspx', '/LogginIn.aspx'];

		# Castle filters
		$include['castle'] = ['\.aspx', 'GET / HTTP'];
		$exclude['castle'] = ['\- \- C'];

		# Clubs filters
		$include['clubs'] = ['\.aspx', 'GET / HTTP'];
		$exclude['clubs'] = ['\- \- C'];

		# DynaArmor filters
		$include['dynaarmor'] = ['\.aspx', 'GET / HTTP'];
		$exclude['dynaarmor'] = ['\- \- C'];

		# DynaCall filters
		$include['dynacall'] = ['\.aspx', 'GET / HTTP'];
		$exclude['dynacall'] = ['\- \- C'];

		# Ebis Page Filters
		$include['ebis'] = ['\.aspx', 'GET / HTTP'];
		$exclude['ebis'] = ['\- \- C', '/ebisPlusRedirect.aspx', '/access_denied.aspx', '/maintainSession.aspx', '/logout.aspx'];

		# Menus filters
		$include['menus'] = ['\.aspx', 'GET / HTTP', '\.pdf'];
		$exclude['menus'] = ['\- \- C'];

		# Profiler filters
		$include['profiler'] = ['\.aspx', 'GET / HTTP'];
		$exclude['profiler'] = ['\- \- C'];

		# Qma filters
		$include['qma'] = ['\.aspx', 'GET / HTTP', '\.pdf'];
		$exclude['qma'] = ['\- \- C'];

		# Simmons filters
		$include['simmons'] = ['\.aspx', 'GET / HTTP', '\.pdf'];
		$exclude['simmons'] = ['\- \- C'];

		# SSO filters
		$include['sso'] = ['\.aspx', 'GET / HTTP'];
		$exclude['sso'] = ['\- \- C'];

		# Survey filters
		$include['survey'] = ['\.aspx', 'GET / HTTP'];
		$exclude['survey'] = ['\- \- C'];

		# VPG filters
		$include['vpg'] = ['\.aspx', 'GET / HTTP'];
		$exclude['vpg'] = ['\- \- C'];

		# WebAQ Page Filters
		$include['webaq'] = ['\.aspx', 'GET / HTTP'];
		$exclude['webaq'] = ['\- \- C', '/dynacomm', '/logout.aspx', '/Tools/KeepAlive.aspx', '/Tools/bannerImage.aspx', '/Admin/'];

		# WebSA filters
		$include['websa'] = ['\.aspx', 'GET / HTTP'];
		$exclude['websa'] = ['\- \- C'];

		# Wiki filters
		$include['wiki'] = [];
		$exclude['wiki'] = ['\- \- C', '/file/', '/search', '\.jpg', '\.png', '\.gif', '\.ico', '\.svg', '\.css', '\.js', '\.woff', '\.eot', '\.ttf', '\.aspx', '\.xml', '/robots.txt'];

		$c->header("Calculating and inserting loadbalancer stats per product");

		// Delete from stats tables by this date
		$c->item("Deleting any existing summary data by date");
		$this->db->query("DELETE FROM Utility.dbo.tbl_stat_lb WHERE date = '$date'");
		$this->db->query("DELETE FROM Utility.dbo.tbl_stat_lb_pages WHERE date = '$date'");

		$files = scandir($tmp);
		foreach ($files as $app) {
			$file = "$tmp/$app";
			if ($app == '.' || $app == '..' || substr($app, 0, 3) == 'log' || str_contains($app, 'pages')) continue;

			
			// Calculate app summary (tbl_stat_lb)
			$c->item("Calculating $app summary");
			$req = $c->exec("cat $file | wc -l");
			if ($req > 0) {
				$reqInternal = $c->exec("cat $file | $egrep -i $dynatronIP | wc -l");
				$totalSize = $c->exec("cat $file | awk 'BEGIN { size=0 } { size += $12 } END { print size / 1024 }'");
				$totalSpeed = $c->exec("cat $file | awk 'BEGIN { time=0 } { split($10, timers, \"/\"); time += timers[4] } END { print time }'");
				
				$query = "
					INSERT INTO Utility.dbo.tbl_stat_lb (
						app, date, req, req_internal, req_external, avg_size, avg_speed
					) VALUES (
						'$app', '$date', $req, $reqInternal, ".($req - $reqInternal).", ".round($totalSize / $req, 2).", ".round($totalSpeed / $req)."
					)
				";
				$this->db->query($query);
			}


			
			// Calculate summary by host and page (tbl_stat_lb_pages)
			if(isset($include[$app])) {
				$c->item("Calculating $app summary by host and page");

				// Build the include/exclude grep filter
				// Example: cat /tmp/lbstat/webaq | /bin/egrep -iv '66.196.205.188|/dynacomm|/logout.aspx|/Tools/KeepAlive.aspx|/Tools/bannerImage.aspx|/Admin/'| /bin/egrep -E '\.weba.*.aspx' > /tmp/lbstat/webaq-pages
				$c->item("Applying main $app grep filter to haproxy log", 2);
				$cmd = "cat $file ";
				if (count($exclude[$app]) > 0) {
					$cmd .= "| $egrep -iv '";
					foreach ($exclude[$app] as $exc) {
						$cmd .= "$exc|";
					}
					$cmd = substr($cmd, 0, -1)."'";
				}

				if (count($include[$app]) > 0) {
					$cmd .= "| $egrep -i '";
					foreach ($include[$app] as $inc) {
						$cmd .= "$inc|";
					}
					$cmd = substr($cmd, 0, -1)."'";
				}
				$cmd .= " > $tmp/$app-pages";
				$c->exec($cmd);
				#echo $cmd;


				// Average size and speed data into array by dealer/url
				if (file_exists("$tmp/$app-pages")) {
					$c->item("Averaging size and speed data into array by dealer/url", 2);
					$data = [];
					$lineCount = 0;
					if (($fh = fopen("$tmp/$app-pages", "r")) !== false) {
						while (($row = fgets($fh)) !== false) {
							$row = preg_replace("'  '", ' ', $row); #critical becuase if date is < 10, there are two spaces after month 'Sep  1' vs 'Sep 10'...
							$cols = explode(" ", $row);
							$timers = explode("/", $cols[9]);
							$speed = $timers[4];
							$size = $cols[11] / 1024;
							$host = strtolower(substr($cols[17], 1, -1));
							if (strpos($host, '.') !== false) {
								$host = substr($host, 0, strpos($host, '.'));
							}
							$url = ltrim($cols[19], "/");
							if (strpos($url, '?') !== false) {
								$url = substr($url, 0, strpos($url, '?'));
							}

							if (!isset($data[$host][$url]['count'])) {
								$data[$host][$url]['count'] = 1;
								$data[$host][$url]['size'] = $size;
								$data[$host][$url]['speed'] = $speed;
							} else {
								$data[$host][$url]['count'] += 1;
								$data[$host][$url]['size'] += $size;
								$data[$host][$url]['speed'] += $speed;
							}

							$lineCount += 1;
						}
					}
					
					// Insert summary page stats
					$c->item("Inserting average page size/speed per dealer", 2);
					foreach ($data as $host => $hostData) {
						foreach ($hostData as $url => $urlData) {
							if ($urlData['count'] > 0) {
								$url = $this->db->escape($url);
								$query = "INSERT INTO Utility.dbo.tbl_stat_lb_pages (
									app, host, date, page, clicks, avg_size, avg_speed
								) VALUES (
									'$app', '$host', '$date', '$url', ".$urlData['count'].",
									".round($urlData['size'] / $urlData['count'], 1).",
									".round($urlData['speed'] / $urlData['count'], 0)."
								)";
								$this->db->query($query);
							}
						}
					}
				}

			}

		}


		// Update server column
		$query = "
			--WebAQ
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 1 WHERE app = 'webaq' AND host in (SELECT DISTINCT serverName FROM [dyna-sql1].WebAQ.dbo.tblDealers)
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 2 WHERE app = 'webaq' AND host in (SELECT DISTINCT serverName FROM [dyna-sql2].WebAQ.dbo.tblDealers)
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 3 WHERE app = 'webaq' AND host in (SELECT DISTINCT serverName FROM [dyna-sql3].WebAQ.dbo.tblDealers)
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 4 WHERE app = 'webaq' AND host in (SELECT DISTINCT serverName FROM [dyna-sql4].WebAQ.dbo.tblDealers)
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 5 WHERE app = 'webaq' AND host in (SELECT DISTINCT serverName FROM [dyna-sql5].WebAQ.dbo.tblDealers)
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 6 WHERE app = 'webaq' AND host in (SELECT DISTINCT serverName FROM [dyna-sql6].WebAQ.dbo.tblDealers)
			DELETE FROM [Utility].[dbo].[tbl_stat_lb_pages] WHERE app = 'webaq' and server = 0

			--WebSA
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 1 WHERE app = 'websa' AND host in (SELECT DISTINCT dealerName FROM [dyna-sql1].WebSA.dbo.tblWSADealerSetting)
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 2 WHERE app = 'websa' AND host in (SELECT DISTINCT dealerName FROM [dyna-sql2].WebSA.dbo.tblWSADealerSetting)
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 3 WHERE app = 'websa' AND host in (SELECT DISTINCT dealerName FROM [dyna-sql3].WebSA.dbo.tblWSADealerSetting)
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 4 WHERE app = 'websa' AND host in (SELECT DISTINCT dealerName FROM [dyna-sql4].WebSA.dbo.tblWSADealerSetting)
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 5 WHERE app = 'websa' AND host in (SELECT DISTINCT dealerName FROM [dyna-sql5].WebSA.dbo.tblWSADealerSetting)
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 6 WHERE app = 'websa' AND host in (SELECT DISTINCT dealerName FROM [dyna-sql6].WebSA.dbo.tblWSADealerSetting)

			--Menus
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 1 WHERE app = 'menus' AND host in (SELECT DISTINCT dealerName FROM [dyna-sql1].Ebis_Prod.dbo.tblDealerMenuDealers)
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 2 WHERE app = 'menus' AND host in (SELECT DISTINCT dealerName FROM [dyna-sql2].Ebis_Prod.dbo.tblDealerMenuDealers)
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 3 WHERE app = 'menus' AND host in (SELECT DISTINCT dealerName FROM [dyna-sql3].Ebis_Prod.dbo.tblDealerMenuDealers)
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 4 WHERE app = 'menus' AND host in (SELECT DISTINCT dealerName FROM [dyna-sql4].Ebis_Prod.dbo.tblDealerMenuDealers)
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 5 WHERE app = 'menus' AND host in (SELECT DISTINCT dealerName FROM [dyna-sql5].Ebis_Prod.dbo.tblDealerMenuDealers)
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 6 WHERE app = 'menus' AND host in (SELECT DISTINCT dealerName FROM [dyna-sql6].Ebis_Prod.dbo.tblDealerMenuDealers)

			--EBIS and EBIS Plus
			UPDATE p SET p.server = h.server_num FROM [dyna-sql6].Utility.dbo.tbl_stat_lb_pages p INNER JOIN [dyna-sql6].VFI.dbo.tbl_host h on p.host = h.subdomain WHERE p.app = 'ebis'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 1 WHERE app = 'ebis' and host = 'www'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 1 WHERE app = 'ebis' and host = 'ebisplus'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 2 WHERE app = 'ebis' and host = 'ebisplus2'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 3 WHERE app = 'ebis' and host = 'ebisplus3'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 4 WHERE app = 'ebis' and host = 'ebisplus4'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 5 WHERE app = 'ebis' and host = 'ebisplus5'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 6 WHERE app = 'ebis' and host = 'ebisplus6'
			DELETE FROM [Utility].[dbo].[tbl_stat_lb_pages] WHERE app = 'ebis' and server = 0

			--Carmail
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 1 WHERE app = 'carmail' and host = 'carmail'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 2 WHERE app = 'carmail' and host = 'carmail2'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 3 WHERE app = 'carmail' and host = 'carmail3'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 4 WHERE app = 'carmail' and host = 'carmail4'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 5 WHERE app = 'carmail' and host = 'carmail5'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 6 WHERE app = 'carmail' and host = 'carmail6'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 7 WHERE app = 'carmail' and host = 'carmail7'

			--All the singles
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 2 WHERE app = 'profiler'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 6 WHERE app = 'dynaarmor'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 6 WHERE app = 'castle'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 6 WHERE app = 'dynacall'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 1 WHERE app = 'simmons'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 6 WHERE app = 'sso'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 7 WHERE app = 'qma'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 6 WHERE app = 'survey'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 6 WHERE app = 'vpg'
			UPDATE [dyna-sql6].Utility.dbo.tbl_stat_lb_pages SET [server] = 4 WHERE app = 'aaacarcare'



		";
		$this->db->query($query);


		// Cleanup
		#$c->exec("rm -rf $tmp");

		

		// Report Queries
		/*

		-- By Date/Server
		-- This is the main metric we can use to watch server performance
		-- If this slowly decreases, we are increasing server capacity or making pages more efficient
		SELECT
		date, server, sum(clicks) as totalClicks, avg(avg_size) as avgPageSize, avg(avg_speed) as avgPageSpeed
		FROM [Utility].[dbo].[tbl_stat_lb_pages]
		group by date, server
		order by server, date

		-- By Date/Server/App
		-- This one too, this is our main metric to how fast a product is going on each server
		SELECT
		date, server, app, sum(clicks) as totalClicks, avg(avg_size) as avgPageSize, avg(avg_speed) as avgPageSpeed
		FROM [Utility].[dbo].[tbl_stat_lb_pages]
		group by date, server, app
		order by app, server

		-- By Date/Page
		-- This breaks down the above by app/page
		SELECT
		date, app, page, sum(clicks) as totalClicks, avg(avg_size) as avgPageSize, avg(avg_speed) as avgPageSpeed
		FROM [Utility].[dbo].[tbl_stat_lb_pages]
		where app = 'ebis'
		group by date, app, page
		order by avgPageSpeed desc


		-- By Date/Host
		-- Great to see your biggest hosts, use by product
		SELECT
		date, host, server, sum(clicks) as totalClicks, avg(avg_size) as avgPageSize, avg(avg_speed) as avgPageSpeed
		FROM [Utility].[dbo].[tbl_stat_lb_pages]
		WHERE app = 'webaq'
		group by date, host, server
		order by totalClicks desc


		convert(decimal(8,2), (convert(decimal(8,2), avg(avg_speed)) / convert(decimal(8,2), sum(clicks)))) as avgMSPerClick

SELECT
date, host, server, sum(clicks) as totalClicks, convert(decimal(8,2), avg(avg_size)) as avgPageKb, avg(avg_speed) as avgPageMs, 

convert(decimal(8,2), (convert(decimal(8,2), avg(avg_speed)) / convert(decimal(8,2), sum(clicks)))) as avgMsPerClick

FROM [Utility].[dbo].[tbl_stat_lb_pages]
WHERE app = 'webaq'
group by date, host, server
order by avgPageMs desc


		*/


	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('file', InputArgument::REQUIRED, 'Full path and filename to log file to haproxy log file.'),
			array('date', InputArgument::REQUIRED, 'YYYY-MM-DD of log file.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
		);
	}

}
