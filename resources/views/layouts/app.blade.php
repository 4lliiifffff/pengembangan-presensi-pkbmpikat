<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title')</title>
</head>
<body>

@include('layouts.navbar')

<div>
@yield('content')
</div>

@include('layouts.bottomNav')
@include('layouts.script')

</body>
</html>
