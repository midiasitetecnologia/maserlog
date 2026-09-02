<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>MASER</title>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset(mix('css/main.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('css/iconfont.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('css/material-icons/material-icons.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('css/vuesax.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('css/prism-tomorrow.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('css/app.css')) }}">    

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}">    

    <!-- Font Awesome Animation -->
    <link rel="stylesheet" href="{{ asset('css/font-awesome-animation.min.css') }}">
  </head>
  <body>
    <noscript>
      <strong>Desculpe, a plataforma MASER e o modelo do painel de administração não funcionam corretamente sem o JavaScript ativado. Ative para continuar.</strong>
    </noscript>
    <div id="app">
    </div>

    <script src="{{ asset(mix('js/app.js')) }}"></script>

  </body>
</html>
