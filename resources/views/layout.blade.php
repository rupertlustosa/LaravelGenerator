<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta Information -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('/vendor/rlustosa/favicon.ico') }}">

    <meta name="robots" content="noindex, nofollow">

    <title>Telescope{{ config('app.name') ? ' - ' . config('app.name') : '' }}</title>

    <!-- Style sheets-->
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="{{ asset(mix('app.css', 'vendor/rlustosa')) }}" rel="stylesheet" type="text/css">
</head>
<body>
<div id="telescope" v-cloak>
    <alert :message="alert.message"
           :type="alert.type"
           :auto-close="alert.autoClose"
           :confirmation-proceed="alert.confirmationProceed"
           :confirmation-cancel="alert.confirmationCancel"
           v-if="alert.type"></alert>

    <div class="container mb-5">
        <div class="d-flex align-items-center py-4 header">
            <svg class="logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80">
                <path class="fill-primary" d="M0 40a39.87 39.87 0 0 1 11.72-28.28A40 40 0 1 1 0 40zm34 10a4 4 0 0 1-4-4v-2a2 2 0 1 0-4 0v2a4 4 0 0 1-4 4h-2a2 2 0 1 0 0 4h2a4 4 0 0 1 4 4v2a2 2 0 1 0 4 0v-2a4 4 0 0 1 4-4h2a2 2 0 1 0 0-4h-2zm24-24a6 6 0 0 1-6-6v-3a3 3 0 0 0-6 0v3a6 6 0 0 1-6 6h-3a3 3 0 0 0 0 6h3a6 6 0 0 1 6 6v3a3 3 0 0 0 6 0v-3a6 6 0 0 1 6-6h3a3 3 0 0 0 0-6h-3zm-4 36a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM21 28a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"></path>
            </svg>

            <h4 class="mb-0 ml-3"><strong>RLustosa</strong> Gerador de Módulos{{ config('app.name') ? ' - ' . config('app.name') : '' }}</h4>
        </div>

        <div class="row mt-4">
            <div class="col-2 sidebar">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <router-link active-class="active" to="/create-module" class="nav-link d-flex align-items-center pt-0">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M0 3c0-1.1.9-2 2-2h16a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3zm2 2v12h16V5H2zm8 3l4 5H6l4-5z"></path>
                            </svg>
                            <span>Criar Módulo</span>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link active-class="active" to="/models" class="nav-link d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M10 1l10 6-10 6L0 7l10-6zm6.67 10L20 13l-10 6-10-6 3.33-2L10 15l6.67-4z"></path>
                            </svg>
                            <span>Adicionar Módulos</span>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link active-class="active" to="/redis" class="nav-link d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M.96 9.84c1.35.61 6.83 2.83 7.73 3.26.93.44 1.58.45 2.75-.16 1.13-.59 6.21-2.68 7.6-3.36l.16.06c1.04.38 1.08.7.01 1.25-1.06.56-6.59 2.83-7.77 3.44-1.17.62-1.82.61-2.75.17-.93-.45-6.81-2.82-7.87-3.33-1.06-.5-1.08-.85-.04-1.26l.18-.07zM.8 13.19h.01c1.06.5 6.94 2.88 7.87 3.33.93.44 1.58.45 2.75-.17 1.17-.6 6.6-2.84 7.74-3.42h.02c1.04.39 1.08.7.01 1.26-1.06.55-6.59 2.82-7.77 3.44-1.17.61-1.82.6-2.75.16-.93-.44-6.81-2.82-7.87-3.33-1.06-.5-1.08-.85-.04-1.26l.03-.01zm18.4-5.71c-1.06.55-6.59 2.82-7.77 3.44-1.17.61-1.82.6-2.75.16-.93-.44-6.81-2.82-7.87-3.32C-.24 7.25-.26 6.9.78 6.49c1.04-.4 6.89-2.7 8.12-3.14 1.24-.44 1.67-.46 2.72-.07 1.05.38 6.54 2.57 7.58 2.95 1.04.38 1.08.7.01 1.25zm-6.59-1.95l-1.34-.5.36-.86-1.32.44-1.4-.55.45.83-1.5.53 2 .18.63 1.04.39-.93 1.73-.18zm-2.22 4.53L11.8 8l-4.63.7 3.23 1.35zm-4.48-2.1c1.37 0 2.47-.42 2.47-.95s-1.1-.96-2.47-.96-2.48.43-2.48.96 1.11.96 2.48.96zm8.75-2.17v2.16l2.74-1.08-2.74-1.08zm-3.03 1.2l2.73 1.08.3-.12V5.8l-3.03 1.2z"></path>
                            </svg>
                            <span>Criar Migrations</span>
                        </router-link>
                    </li>
                </ul>
            </div>

            <div class="col-10">
                <router-view></router-view>
            </div>
        </div>
    </div>
</div>
<!-- Global Telescope Object -->
<script>
    window.Telescope = @json($telescopeScriptVariables);
</script>

<script src="{{asset(mix('app.js', 'vendor/rlustosa'))}}"></script>
</body>
</html>
