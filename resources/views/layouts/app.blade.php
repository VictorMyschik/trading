<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta name="mailru-domain" content="CvUL6H4o9szGT7Co" />
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>
    @if(isset($page_title))
      {!! substr($page_title, 0, 25) !!}
    @else
      {{ config('app.name', 'Laravel') }}
    @endif
  </title>

  <!-- Scripts -->
  <script src="{{ asset('js/app.js') }}" defer></script>

  <!-- Fonts -->
  <link rel="dns-prefetch" href="//fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

  <!-- Styles -->
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">
  <link href="{{ asset('css/mr-style.css') }}" rel="stylesheet">
</head>
<body>
<div id="app" class="mr-main-div">
  @yield('content')
</div>
<div class="modal fade padding-0" id="mr_modal" role="dialog"></div>

</body>
</html>
