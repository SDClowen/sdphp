<?php 
	class Main extends Controller
	{
		public function index()
		{
			echo "This Welcome::index";
		}

		#[route(method: route::get, uri: "login")]
		public function test()
		{
			echo "Hello this is test functi0n";
		}
	}
?>
