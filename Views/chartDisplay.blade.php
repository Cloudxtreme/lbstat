<!--<div class="row">-->
	<div class="col-md-{{ $chart->colSize }}">
		<div class="panel panel-default">
			<div class="panel-heading">{{ $chart->name }}
			<div class="pull-right panel-icons" id="btn-group-{{ $chart->id }}">
				<button class="btn btn-xs btn-default btn-{{ $chart->id }}" chartType="1" data-toggle="tooltip" data-placement="top" title="Line Chart">
					<i class="fa fa-line-chart"></i></button>
				<button class="btn btn-xs btn-default btn-{{ $chart->id }}" chartType="2" data-toggle="tooltip" data-placement="top" title="Bar Chart">
					<i class="fa fa-bar-chart"></i></button>
				<button class="btn btn-xs btn-default btn-{{ $chart->id }}" chartType="3" data-toggle="tooltip" data-placement="top" title="Area Chart">
					<i class="fa fa-area-chart"></i></button>
				<button class="btn btn-xs btn-default btn-{{ $chart->id }}" chartType="5" data-toggle="tooltip" data-placement="top" title="Pie Chart">
					<i class="fa fa-pie-chart"></i></button>
				<button class="btn btn-xs btn-default btn-{{ $chart->id }}" chartType="6" data-toggle="tooltip" data-placement="top" title="Curved Line Chart">
					<i class="fa fa-line-chart"></i></button>					
				<button class="btn btn-xs btn-default btn-{{ $chart->id }}" chartType="8" data-toggle="tooltip" data-placement="top" title="Step Line Chart">
					<i class="fa fa-align-center"></i></button>
				<button class="btn btn-xs btn-default btn-{{ $chart->id }}" chartType="12" data-toggle="tooltip" data-placement="top" title="Scatter Chart">
					<i class="fa fa-asterisk"></i></button>
				<button class="btn btn-xs btn-default btn-{{ $chart->id }}" chartType="13" data-toggle="tooltip" data-placement="top" title="Doughnut Chart">
					<i class="fa fa-circle-o"></i></button>
				<button class="btn btn-xs btn-default btn-{{ $chart->id }}" chartType="15" data-toggle="tooltip" data-placement="top" title="Bubble Chart">
					<i class="fa fa-dot-circle-o"></i></button>
				<button class="btn btn-xs btn-default btn-{{ $chart->id }}" chartType="20" data-toggle="tooltip" data-placement="top" title="Horizontal Bar Chart">
					<i class="fa fa-align-left"></i></button>
				<button class="btn btn-xs btn-default btn-{{ $chart->id }}" id="btn-datatable-{{ $chart->id }}" chartType="0" data-toggle="tooltip" data-placement="top" title="DataTable">
					<i class="fa fa-table"></i></button>
			</div>
			</div>
			<div class="panel-body">
				<div>
				<div class="pull-right">			
					<div id="chkbox-group-{{ $chart->id }}" class="form-group chkbox-group">
					</div>
				</div>
				<div id="div-chart-{{ $chart->id }}" class="chart" style="width:100%;height:400px;display:inline-block"></div>
			</div>
			<div style="display:none" id="div-datatable-{{ $chart->id }}">
				<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered">									
				</table>
			</div>
			</div>
		</div>
	</div>
<!--</div>-->

	<script>
	$(function() {
		var chart1 = new cfx.Chart();
		var items = {!! $chart->data !!};
		var tableInit = false;
		
		//chart options
		chart1.setGallery({{ $chart->chartType }});
		//Don't turn off markershape, this breaks the points labels		
		//chart1.getAllSeries().setMarkerShape(cfx.MarkerShape.None);
		chart1.getAllSeries().setMarkerSize(2);
		chart1.getAxisX().setLabelAngle(45);
		chart1.getAnimations().getLoad().setEnabled({{ $chart->doAnimate }});
		chart1.getAxisX().setAutoScroll({{ $chart->doScroll }});
		chart1.getAxisX().setClientScroll({{ $chart->doScroll }});		
		
		//create chart
		chart1.setDataSource(items);		
		chart1.create('div-chart-{{ $chart->id }}');

		//enable chartType buttons
		var chartsAvailable = {{ $chart->chartsAvailable }};		
		for(var i =0; i<chartsAvailable.length;i++)
		{
			$("#btn-group-{{ $chart->id }} button[chartType='" + chartsAvailable[i] + "']").show();
		}
		$("#btn-group-{{ $chart->id }} button[chartType='{{ $chart->chartType }}']").addClass('btn-primary');

		//create series checkboxes
		var i = 0;
		while(chart1.getSeries().getItem(i) != undefined)
		{
			var chkbox = '<input type="checkbox" class="chkbox-group-{{ $chart->id }}" value="'+ i +'" checked="checked" />' + chart1.getSeries().getItem(i).getText();
			$('#chkbox-group-{{ $chart->id }}').append(chkbox);
			i++;
		}

		//btn toggle between chartType / DataTable
		$( ".btn-{{ $chart->id }}" ).click(function() 
		{
			$('.btn-{{ $chart->id }}').toggleClass('btn-primary', false);
			$(this).toggleClass('btn-primary', true);				

			if($(this).attr('id') == 'btn-datatable-{{ $chart->id }}')
			{
				$('#div-datatable-{{ $chart->id }}').show();
				$('#div-chart-{{ $chart->id }}').parent().hide();
				if(!tableInit)
				{
					fnDataTableInit();
				}
			}
			else
			{
				$('#div-datatable-{{ $chart->id }}').hide();
				$('#div-chart-{{ $chart->id }}').parent().show();	
				chart1.setGallery(parseInt($(this).attr('chartType')));
			}
		});						

		//checkbox toggle series display
		$( ".chkbox-group-{{ $chart->id}}" ).click(function() 
		{
			var bToggle = $(this).is(":checked") ? true : false;
			chart1.getSeries().getItem($(this).val()).setVisible(bToggle);
		});	   
		   
		//datatable init   
		function fnDataTableInit()
		{
			var keys = Object.keys(items[0]);
			var jsonData = [];
		    $.each(keys, function(k, v) 
			{
				jsonData[k] = {};
		        jsonData[k]['title'] = v;
		        jsonData[k]['data'] = v;			   			  
			});
			
			tableInit = true;
		    $('#div-datatable-{{ $chart->id }} table').DataTable( 
		    {
		        "data": items,
		        "searching": false,
		        "order": [[0, 'desc' ]],
		        "columns": jsonData	    		
	    	});
	    }   

		//init button tooltips
		$('.btn').tooltip();
	});

	</script>

