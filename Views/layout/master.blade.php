@extends('layout.app')

@section('css')
	@parent	
	
	<style>
		.body, .no-container .body {
			padding: 0px 0px 0px 0px;
		}

		#div-charts {
			margin-top:15px;
		}

		.panel-heading {
			font-weight:bold;
			/*color:#666666 !important;*/
		}

		.panel-icons {
			position:absolute;
			top:10px;
			right:25px;
		}

		.panel-icons button {
			display:none;
			height:20px;
		}

		.chkbox-group {
			 display:inline;
			 font-size:10px;
		}
		
		.chkbox-group input {
			margin:5px;
		}

	</style>
@stop


@section('content')
	@section('subheader-title')
		<i class="fa fa-sliders fa-large"></i> Load Balancer
	@stop
	@section('subheader-content')
		<select name="ddlChartType" id="ddlChartType" class="form-control wb-lb-filter" style="display:inline;width:175px;">
			<option value="Overview" selected="selected">Overview Charts</option>
			<option value="Apps">App Charts</option>
			<option value="Pages">Page Charts</option>
			<option value="Hosts">Hosts Charts</option>
		</select>
		<select name="ddlAppType" id="ddlAppType" class="form-control wb-lb-filter" style="display:none;width:175px;">
			<option value="webaq" selected="selected">WebAQ</option>
			<option value="websa">WebSA</option>
			<option value="ebis">Ebis</option>
			<option value="menus">Menus</option>
			<option value="carmail">CarMail</option>
			<option value="dynaarmor">DynaArmor</option>
			<option value="profiler">Profiler</option>
			<option value="castle">Castle</option>
			<option value="dynacall">DynaCall</option>
			<option value="simmons">Simmons</option>
			<option value="sso">SSO</option>
			<option value="qma">QMA</option>
			<option value="survey">Survey</option>
			<option value="vpg">VPG</option>
			<option value="aaacarcare">AAACarCare</option>
		</select>
		<div class="pull-right">
			<select name="ddlDateRange" id="ddlDateRange" class="form-control wb-lb-filter" style="width:150px;display:inline;">
			<option value="7">7 Days</option>
			<option value="30" selected="selected">30 Days</option>
			<option value="60">60 Days</option>
			<option value="90">90 Days</option>
			<option value="180">180 Days</option>						
		</select>
		</div>
	@stop

	<div class="container">
		<div class="row">
			<div class="wb-content col-md-12">
				@yield('wb-content')
			</div>
		</div>
	</div>
@stop


@section('script')
	@parent

	<script src="{{ asset('js/jchartfx/jchartfx.system.js') }}"></script>
	<script src="{{ asset('js/jchartfx/jchartfx.coreVector.js') }}"></script>
	<script src="{{ asset('js/jchartfx/jchartfx.animation.js') }}"></script>
	<script src="{{ asset('js/jchartfx/jchartfx.advanced.js') }}"></script>

@stop
