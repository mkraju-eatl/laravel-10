<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Big Blue Button</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
<div class="container">
    <div class="row">
        <h1 class="text-center">Big Blue Button </h1>
    </div>
     <div class="row">
        <div class="col-md-12">
            <nav class="navbar navbar-expand-lg " style="background-color: #e3f2fd;">
                <div class="container-fluid">
                    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                        <div class="navbar-nav">
                            <a class="nav-link {{ request()->segment(1) == "" ? 'active' : '' }}"
                               href="{{ route('home') }}">Home</a>
                            <a class="nav-link {{ request()->segment(1) == "check-bigblue-conneciton" ? 'active' : '' }}"
                               href="{{ route('check-bigblue-conneciton') }}">Check Connection</a>
                            <a class="nav-link" href="{{ route('create-meeting') }}">Create Meeting</a>
                            <a class="nav-link" href="{{ route('store-on-redis') }}">Store On Redis</a>
                            <a class="nav-link" href="{{ route('add-to-cart') }}">Add To Cart</a>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </div>
    @yield('content')
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
