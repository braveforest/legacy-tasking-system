<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport"    content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="csrf-token" content="{{ csrf_token() }}"><!-- CSRF Token -->

	<title>{{ config('app.name') }}</title>
	<link href="img/favicon.ico" rel="icon" type="image/x-icon" />
	<link rel="stylesheet" href="/css/bootstrap.min.css">
	<!-- Custom CSS -->
    <link href="css/styles.css" rel="stylesheet">
	@yield('head')
</head>

<body>
	<!-- Navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <a class="navbar-brand" href="/">Legacy Tasking System</a>
            </div>
        </div>
    </nav>

	<div class="container">

	@yield('content')

	<!-- Footer -->
	<footer>
		<div class="row">
			<div class="col-lg-12">
				<p>Copyright &copy; Legacy Tasking System 2016</p>
			</div>
			<!-- /.col-lg-12 -->
		</div>
		<!-- /.row -->
	</footer>
	</div> <!-- container -->
	<!-- jQuery -->
    <script src="js/jquery.min.js"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>
	@yield('script')
</body>
</html>
