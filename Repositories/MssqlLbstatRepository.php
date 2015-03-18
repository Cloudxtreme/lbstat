<?php namespace Dynatron\Lbstat\Repositories;

use Mreschke\Dbal\Mssql as DbalInterface;

class MssqlLbstatRepository implements LbstatRepositoryInterface
{

	protected $db;

	public function __construct(DbalInterface $db)
	{
		$this->db = $db;
		$this->db->connection('dyna-sql6');
	}

	/*
		-- By Date/Server
		-- Total clicks per server per day
		-- Three area graphs, one for clicks, size and speed
	 */
		
	public function getClicksByDateServer($dateRange)
	{
		if($dateRange < 90)
		{
			$result = $this->db->query("
				SELECT CONVERT(VARCHAR(10), date, 7) AS [Mon DD],
					ISNULL([1],0) AS SQL1, ISNULL([2],0) AS SQL2, ISNULL([3],0) AS SQL3, ISNULL([4],0) AS SQL4, ISNULL([5],0) AS SQL5, ISNULL([6],0) AS SQL6
				FROM
					(SELECT date, server, clicks
					FROM [Utility].[dbo].[tbl_stat_lb_pages]
					WHERE [date] BETWEEN DATEADD(dd, -".$dateRange.", getdate()) AND getdate()
					) AS SourceTbl
				PIVOT
				(
					SUM(clicks)
					FOR server IN ([1], [2], [3], [4], [5], [6])
				) AS PivotTable
				ORDER BY date
			")->get();
		} else 
		{
			$result = $this->db->query("
					SELECT Mnth AS [Mon DD],
					       ISNULL([1],0) AS SQL1, ISNULL([2],0) AS SQL2, ISNULL([3],0) AS SQL3, ISNULL([4],0) AS SQL4, ISNULL([5],0) AS SQL5, ISNULL([6],0) AS SQL6
					FROM (
					       SELECT DATEPART(mm, [date]) AS Mnth, [server], SUM(clicks) AS TotalClicks
					       FROM [Utility].[dbo].[tbl_stat_lb_pages]
					       WHERE [date] BETWEEN DATEADD(dd, -".$dateRange.", getdate()) AND getdate()
					       GROUP BY DATEPART(mm, [date]), [server]
					       ) AS SourceTbl
					PIVOT
					(
					       AVG(TotalClicks)
					       FOR server IN ([1], [2], [3], [4], [5], [6])
					) AS PivotTable
					ORDER BY Mnth
				")->get();		

			foreach($result as $data)
			{
				$data->{'Mon DD'} = date('F', mktime(0, 0, 0, $data->{'Mon DD'}, 10));;
			}
		}
		return $result;
	}

	/*
		-- By Date/Server
		-- Total size per server per day
		-- Three area graphs, one for clicks, size and speed
	 */

	public function getPageSizeByDateServer($dateRange)
	{
		if($dateRange < 90)
		{
			$result = $this->db->query("
			SELECT CONVERT(VARCHAR(10), date, 7) AS [Mon DD],
				ISNULL([1],0) AS SQL1, ISNULL([2],0) AS SQL2, ISNULL([3],0) AS SQL3, ISNULL([4],0) AS SQL4, ISNULL([5],0) AS SQL5, ISNULL([6],0) AS SQL6
			FROM
				(SELECT date, server, avg_size
				FROM [Utility].[dbo].[tbl_stat_lb_pages]
				WHERE [date] BETWEEN DATEADD(dd, -".$dateRange.", getdate()) AND getdate()) AS SourceTbl

			PIVOT
			(
				AVG(avg_size)
				FOR server IN ([1], [2], [3], [4], [5], [6])
			) AS PivotTable
			ORDER BY date
		")->get();
		} else 
		{
			$result = $this->db->query("
					SELECT Mnth AS [Mon DD],
					       [1] AS SQL1, [2] AS SQL2, [3] AS SQL3, [4] AS SQL4, [5] AS SQL5, [6] AS SQL6
					FROM (
					       SELECT DATEPART(mm, [date]) AS Mnth, [server], AVG(avg_size) AS avg_size
					       FROM [Utility].[dbo].[tbl_stat_lb_pages]
					       WHERE [date] BETWEEN DATEADD(dd, -".$dateRange.", getdate()) AND getdate()
					       GROUP BY DATEPART(mm, [date]), [server]
					       ) AS SourceTbl
					PIVOT
					(
					       AVG(avg_size)
					       FOR server IN ([1], [2], [3], [4], [5], [6])
					) AS PivotTable
					ORDER BY Mnth
				")->get();		

			foreach($result as $data)
			{
				$data->{'Mon DD'} = date('F', mktime(0, 0, 0, $data->{'Mon DD'}, 10));;
			}
		}
		return $result;	
	}

	/*
		-- By Date/Server
		-- Total speed per server per day
		-- Three area graphs, one for clicks, size and speed
	
	 */

	public function getPageSpeedByDateServer($dateRange)
	{
		if($dateRange < 90)
		{
			$result = $this->db->query("
			SELECT CONVERT(VARCHAR(10), date, 7) AS [Mon DD],
				ISNULL([1],0) AS SQL1, ISNULL([2],0) AS SQL2, ISNULL([3],0) AS SQL3, ISNULL([4],0) AS SQL4, ISNULL([5],0) AS SQL5, ISNULL([6],0) AS SQL6
			FROM
				(SELECT date, server, avg_speed
				FROM [Utility].[dbo].[tbl_stat_lb_pages]
				WHERE [date] BETWEEN DATEADD(dd, -".$dateRange.", getdate()) AND getdate()) AS SourceTbl

			PIVOT
			(
				AVG(avg_speed)
				FOR server IN ([1], [2], [3], [4], [5], [6])
			) AS PivotTable
			ORDER BY date
		")->get();
		} else 
		{
			$result = $this->db->query("
					SELECT Mnth AS [Mon DD],
					       ISNULL([1],0) AS SQL1, ISNULL([2],0) AS SQL2, ISNULL([3],0) AS SQL3, ISNULL([4],0) AS SQL4, ISNULL([5],0) AS SQL5, ISNULL([6],0) AS SQL6
					FROM (
					       SELECT DATEPART(mm, [date]) AS Mnth, [server], AVG(avg_speed) AS avg_speed
					       FROM [Utility].[dbo].[tbl_stat_lb_pages]
					       WHERE [date] BETWEEN DATEADD(dd, -".$dateRange.", getdate()) AND getdate()
					       GROUP BY DATEPART(mm, [date]), [server]
					       ) AS SourceTbl
					PIVOT
					(
					       AVG(avg_speed)
					       FOR server IN ([1], [2], [3], [4], [5], [6])
					) AS PivotTable
					ORDER BY Mnth
				")->get();		

			foreach($result as $data)
			{
				$data->{'Mon DD'} = date('F', mktime(0, 0, 0, $data->{'Mon DD'}, 10));;
			}
		}
		return $result;			
	}

	/*
		-- By App/Date/Server
		-- Total clicks by app per server per day
		-- Three area graphs, one for clicks, size and speed
	 */

	public function getClicksByAppDateServer($dateRange, $appName)
	{
		if($dateRange < 90)
		{
			$result = $this->db->query("
			SELECT CONVERT(VARCHAR(10), date, 7) AS [Mon DD],
				ISNULL([1],0) AS SQL1, ISNULL([2],0) AS SQL2, ISNULL([3],0) AS SQL3, ISNULL([4],0) AS SQL4, ISNULL([5],0) AS SQL5, ISNULL([6],0) AS SQL6
			FROM
				(SELECT date, server, clicks
				FROM [Utility].[dbo].[tbl_stat_lb_pages]
				WHERE [date] BETWEEN DATEADD(dd, -".$dateRange.", getdate()) AND getdate() AND App = '".$appName."'
				) AS SourceTbl
			PIVOT
			(
				SUM(clicks)
				FOR server IN ([1], [2], [3], [4], [5], [6])
			) AS PivotTable
			ORDER BY date
		")->get();
		} else 
		{
			$result = $this->db->query("
					SELECT Mnth AS [Mon DD],
					       ISNULL([1],0) AS SQL1, ISNULL([2],0) AS SQL2, ISNULL([3],0) AS SQL3, ISNULL([4],0) AS SQL4, ISNULL([5],0) AS SQL5, ISNULL([6],0) AS SQL6
					FROM (
					       SELECT DATEPART(mm, [date]) AS Mnth, [server], SUM(clicks) AS clicks
					       FROM [Utility].[dbo].[tbl_stat_lb_pages]
					       WHERE [date] BETWEEN DATEADD(dd, -".$dateRange.", getdate()) AND getdate()
					       	 AND App = '".$appName."'
					       GROUP BY DATEPART(mm, [date]), [server]
					       ) AS SourceTbl
					PIVOT
					(
					       AVG(clicks)
					       FOR server IN ([1], [2], [3], [4], [5], [6])
					) AS PivotTable
					ORDER BY Mnth
				")->get();		

			foreach($result as $data)
			{
				$data->{'Mon DD'} = date('F', mktime(0, 0, 0, $data->{'Mon DD'}, 10));;
			}
		}
		return $result;		
	}

	/*
		-- By App/Date/Server
		-- Total size by app per server per day
		-- Three area graphs, one for clicks, size and speed
	 */

	public function getPageSizeByAppDateServer($dateRange, $appName)
	{
		if($dateRange < 90)
		{
			$result = $this->db->query("
			SELECT CONVERT(VARCHAR(10), date, 7) AS [Mon DD],
				ISNULL([1],0) AS SQL1, ISNULL([2],0) AS SQL2, ISNULL([3],0) AS SQL3, ISNULL([4],0) AS SQL4, ISNULL([5],0) AS SQL5, ISNULL([6],0) AS SQL6
			FROM
				(SELECT date, server, ISNULL(avg_size,0) as avg_size
				FROM [Utility].[dbo].[tbl_stat_lb_pages]
				WHERE [date] BETWEEN DATEADD(dd, -".$dateRange.", getdate()) AND getdate() AND App = '".$appName."'
				) AS SourceTbl

			PIVOT
			(
				AVG(avg_size)
				FOR server IN ([1], [2], [3], [4], [5], [6])
			) AS PivotTable
			ORDER BY date
		")->get();	
		} else 
		{
			$result = $this->db->query("
					SELECT Mnth AS [Mon DD],
					       ISNULL([1],0) AS SQL1, ISNULL([2],0) AS SQL2, ISNULL([3],0) AS SQL3, ISNULL([4],0) AS SQL4, ISNULL([5],0) AS SQL5, ISNULL([6],0) AS SQL6
					FROM (
					       SELECT DATEPART(mm, [date]) AS Mnth, [server], AVG(avg_size) AS avg_size
					       FROM [Utility].[dbo].[tbl_stat_lb_pages]
					       WHERE [date] BETWEEN DATEADD(dd, -".$dateRange.", getdate()) AND getdate()
					       	 AND App = '".$appName."'
					       GROUP BY DATEPART(mm, [date]), [server]
					       ) AS SourceTbl
					PIVOT
					(
					       AVG(avg_size)
					       FOR server IN ([1], [2], [3], [4], [5], [6])
					) AS PivotTable
					ORDER BY Mnth
				")->get();		

			foreach($result as $data)
			{
				$data->{'Mon DD'} = date('F', mktime(0, 0, 0, $data->{'Mon DD'}, 10));;
			}
		}
		return $result;	
	}

	/*
		-- By App/Date/Server
		-- Total speed by app per server per day
		-- Three area graphs, one for clicks, size and speed	
	 */

	public function getPageSpeedByAppDateServer($dateRange, $appName)
	{
		if($dateRange < 90)
		{
			$result = $this->db->query("
			SELECT CONVERT(VARCHAR(10), date, 7) AS [Mon DD],
				ISNULL([1],0) AS SQL1, ISNULL([2],0) AS SQL2, ISNULL([3],0) AS SQL3, ISNULL([4],0) AS SQL4, ISNULL([5],0) AS SQL5, ISNULL([6],0) AS SQL6
			FROM
				(SELECT date, server, ISNULL(avg_speed,0) as avg_speed
				FROM [Utility].[dbo].[tbl_stat_lb_pages]
				WHERE [date] BETWEEN DATEADD(dd, -".$dateRange.", getdate()) AND getdate() AND App = '".$appName."'
				) AS SourceTbl

			PIVOT
			(
				AVG(avg_speed)
				FOR server IN ([1], [2], [3], [4], [5], [6])
			) AS PivotTable
			ORDER BY date
		")->get();
		} else 
		{
			$result = $this->db->query("
					SELECT Mnth AS [Mon DD],
					       ISNULL([1],0) AS SQL1, ISNULL([2],0) AS SQL2, ISNULL([3],0) AS SQL3, ISNULL([4],0) AS SQL4, ISNULL([5],0) AS SQL5, ISNULL([6],0) AS SQL6
					FROM (
					       SELECT DATEPART(mm, [date]) AS Mnth, [server], AVG(avg_speed) AS avg_speed
					       FROM [Utility].[dbo].[tbl_stat_lb_pages]
					       WHERE [date] BETWEEN DATEADD(dd, -".$dateRange.", getdate()) AND getdate()
					       	 AND App = '".$appName."'
					       GROUP BY DATEPART(mm, [date]), [server]
					       ) AS SourceTbl
					PIVOT
					(
					       AVG(avg_speed)
					       FOR server IN ([1], [2], [3], [4], [5], [6])
					) AS PivotTable
					ORDER BY Mnth
				")->get();		

			foreach($result as $data)
			{
				$data->{'Mon DD'} = date('F', mktime(0, 0, 0, $data->{'Mon DD'}, 10));;
			}
		}
		return $result;
	}

	/*
		-- By App/Page/Date/Server
		-- All Data by App/Page overtime
		-- Three area graphs, one for clicks, size and speed	
	 */

	public function getAllDataByAppPageDateServer($dateRange, $appName)
	{
		return $this->db->query("
			SELECT top 20
				page, app, sum(clicks) as totalClicks, avg(avg_size) as avgPageSize, avg(avg_speed) as avgPageSpeed
			FROM [Utility].[dbo].[tbl_stat_lb_pages]
				WHERE [date] BETWEEN DATEADD(dd, -".$dateRange.", getdate()) AND getdate() AND App = '".$appName."'
			group by app, page
			order by totalClicks desc
		")->get();
	}

	/*
		-- By Host/App/Date/Server
		-- All Data by App/Host overtime
		-- Three area graphs, one for clicks, size and speed	
	 */

	public function getHostByAppDateServer($dateRange, $appName)
	{
		return $this->db->query("
			SELECT
				host, sum(clicks) as totalClicks, avg(avg_size) as avgPageSize, avg(avg_speed) as avgPageSpeed
			FROM [Utility].[dbo].[tbl_stat_lb_pages]
				WHERE app = '".$appName."'
			group by host, app
			order by totalClicks desc
		")->get();
	}

}