<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Font Awesome --> 
<link rel="stylesheet" href="../css/css/all.min.css">
<!-- Ionicons -->
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
<!-- overlayScrollbars -->
<link rel="stylesheet" href="../css/adminlte.min.css">
<!-- Google Font: Source Sans Pro -->
<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">

</head>
<body class="hold-transition sidebar-mini">
<!-- Site wrapper -->
<div class="wrapper">
<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <!-- Left navbar links 
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="../../index3.html" class="nav-link">Home</a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="#" class="nav-link">Contact</a>
    </li>
  </ul>-->
  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">
      <a href="../controller/logout.php">Cerrar Sesión</a>
  </ul>
</nav>
<!-- /.navbar -->

<!-- Main Sidebar Container -->
<aside class="main-sidebar" style="background-color: #001d3d;" class="sidebar-blue elevation-4">
  <!-- Brand Logo -->
  <a href="../view/menuAdmin.php" class="brand-link">
    <img src="../img/perfil.png"
         alt="AdminLTE Logo"
         class="brand-image img-circle elevation-3"
         style="opacity: .8">
    <span class="brand-text font-weight-light" style="color: #FFF;">SIGECA</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img id='avatar4' src="../img/perfil.png" class="img-circle elevation-2" alt="User Image">
      </div>
      <div class="info" >
        <a href="#" class="d-block" style="color: #FFF;">
          <?php
              echo$_SESSION['nombre'];
          ?>
        </a>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Add icons to the links using the .nav-icon class
             with font-awesome or any other icon font library -->
        <li class="nav-header" style="color: #FFF;">Usuario</li>
          <li class="nav-item">
          <a href="../view/editar_datos_personales.php" class="nav-link">
            <i style="color: #a3b18a;" class="nav-icon fas fa-user-cog"></i>
            <p style="color: #a3b18a;">
              Datos Personales
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="../view/adm_usuarios.php" class="nav-link">
            <i style="color: #a3b18a;" class="nav-icon fas fa-users"></i>
            <p style="color: #a3b18a;">
              Administrar Usuarios
            </p>
          </a>
        </li>
        <li class="nav-header" style="color: #FFF;">Carga y Muestreo de Datos</li>
          <li class="nav-item">
          <a href="../view/mostrarExcel.php" class="nav-link">
          <i style="color: #a3b18a;" class="nav-icon bi bi-person-badge-fill"></i>
            <p style="color: #a3b18a;">
              Evaluación Adolescentes
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="../view/mostrarExcel2hoja.php" class="nav-link">
            <i style="color: #a3b18a;" class="nav-icon bi bi-person-vcard-fill"></i>
            <p style="color: #a3b18a;">
              Reevaluaciones
            </p>
          </a>
        </li>
        <li class="nav-header" style="color: #FFF;">Reportes</li>
          <li class="nav-item">
          <a href="../controller/reporteProductividadController.php?action=mostrarFormulario" class="nav-link">
          <i style="color: #a3b18a;" class="nav-icon bi bi-card-checklist"></i>
            <p style="color: #a3b18a;">
              Reporte de Productividad
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="../view/estadisticasMensuales.php" class="nav-link">
            <i style="color: #a3b18a;" class="nav-icon bi bi-calendar-week-fill"></i>
            <p style="color: #a3b18a;">
              Estadísticas Mensuales
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="../view/reporteIndicadores.php" class="nav-link">
            <i style="color: #a3b18a;" class="nav-icon bi bi-clipboard-data"></i>
            <p style="color: #a3b18a;">
              Reportes de Indicadores
            </p>
          </a>
        </li>

        <li class="nav-header" style="color: #FFF;">Gráficas</li>
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>