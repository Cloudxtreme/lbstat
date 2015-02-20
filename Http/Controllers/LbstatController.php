<?php namespace Dynatron\Lbstat\Http\Controllers;

use DB;
use View;
use Mssql;
use Mrcore;
use Dynatron\Lbstat\Repositories\LbstatRepositoryInterface;

class LbstatController extends Controller {

	protected $repo;

	public function __construct(LbstatRepositoryInterface $repo)
	{
		$this->repo = $repo;
	}

	/*
	get initial Load Balancer view & list of charts
	 */

	public function index()
	{
		$chartData = [
			'Overview' => [
				'charts' => [
					102
					, 100
					, 101
				]
			]
			, 'Apps' => [
				'charts' => [
					202
					, 200
					, 201 
				]
			]
			, 'Pages' => [
				'charts' => [
					300
				]
			]
			, 'Hosts' => [
				'charts' => [
					400
				]
			]
		];
		$chartData = json_encode([$chartData]);
		
		$post = Mrcore::post()->prepare();
		return View::make('lbstat::index', compact(
			'post', 'chartData'
		));
	}

	/*
	return chartDisplay view with appropriate chart data
	 */

	public function getChart($chartID, $dateRange, $appName)
	{

		$chart = new \stdClass();			
		/* ChartTypes:
			0 - DataTable
			1 - Line Chart
	 		2 - Bar Chart
			3 - Area Chart
			5 - Pie Chart
			6 -	Curved Line
			8 - Step Line
			12 - Scatter
			13 - Doughnut
			15 - Bubble
			20 - Horizontal Bar
	 	*/
	 
	 	//chart defaults
 		$chart->id = $chartID;
		$chart->chartType = 1; //linechart default
		$chart->name = 'Data Chart';
		$chart->chartsAvailable = json_encode([1,2,0]); //chartTypes to allow switching		
		$chart->colSize = 12; //default column size
		$chart->doAnimate = false; //enable chart animation
		$chart->doScroll = false; //emable chart horizontal scrolling
		
		switch($chartID)
		{
			case 100:
				$data = $this->repo->getClicksByDateServer($dateRange);	
				$chart->name = 'Total Clicks per Server';	
				$chart->chartType = 3;							
				$chart->chartsAvailable = json_encode([0,1,2,3,12,20]);
				$chart->colSize = 6;				
			break;
			case 101:
				$data = $this->repo->getPageSizeByDateServer($dateRange);
				$chart->name = 'Average Page Size per Server';	
				$chart->chartType = 2;			
				$chart->chartsAvailable = json_encode([0,1,2,3,12,20]);
				$chart->colSize = 6;
				$chart->doAnimate = true;
			break;
			case 102:
				$data = $this->repo->getPageSpeedByDateServer($dateRange);	
				$chart->name = 'Average Page Speed per Server';
				$chart->chartType = 1;				
				$chart->chartsAvailable = json_encode([0,1,2,3,12,20]);				
			break;
			case 200:
				$data = $this->repo->getClicksByAppDateServer($dateRange, $appName);	
				$chart->name = 'Total Clicks per Server - ('.$appName.')';
				$chart->chartsAvailable = json_encode([0,1,2,3,12,20]);								
			break;
			case 201:
				$data = $this->repo->getPageSizeByAppDateServer($dateRange, $appName);	
				$chart->name = 'Average Page Size per Server - ('.$appName.')';
				$chart->chartsAvailable = json_encode([0,1,2,3,12,20]);				
			break;
			case 202:
				$data = $this->repo->getPageSpeedByAppDateServer($dateRange, $appName);	
				$chart->name = 'Average Page Speed per Server - ('.$appName.')';
				$chart->chartsAvailable = json_encode([0,1,2,3,12,20]);				
			break;
			case 300:
				$data = $this->repo->getAllDataByAppPageDateServer($dateRange, $appName);	
				$chart->chartType = 5;
				$chart->chartsAvailable = json_encode([0,2,5,20]);
				$chart->name = 'Total Clicks, Avg. Page Size, Avg. Page Speed by Page - ('.$appName.')';				
			break;
			case 400:
				$data = $this->repo->getHostByAppDateServer($dateRange, $appName);
				$chart->chartType = 2;
				$chart->chartsAvailable = json_encode([0,2,20]);
				$chart->name = 'Total Clicks, Avg. Page Size, Avg. Page Speed by Host - ('.$appName.')';				
			break;
			default:
				$data = $this->repo->getPageSpeedByDateServer($dateRange);					
			break;
		}

		$chart->data = $data;

		return View::make('lbstat::chartDisplay', compact(
			'chart'
		));
	}

}
