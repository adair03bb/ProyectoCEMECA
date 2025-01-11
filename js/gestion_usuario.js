$(document).ready(function(){
  var tipo_usuario = $('#tipo_usuario').val();
  if(tipo_usuario==2){
    $('#button_crear').hide();
  }

  function ocultarCamposUsuarioNormal() {
    if (tipo_usuario == 2) {
        $('.card').find('#nomusu, #passusu').hide();
    }
  }
  function ocultarCamposUsuarioAdmin() {
    if (tipo_usuario == 1) {
        $('.card').find('#passusu').hide();
    }
  }

    buscar_datos();
    var funcion;

    function buscar_datos(consulta){
        funcion ='buscar_usuarios_adm';
        $.post('../controller/userController.php', {consulta,funcion}, (response)=>{
            const usuarios = JSON.parse(response);
            let template='';
            usuarios.forEach(usuario => {
                template+=`
                <div usuarioId="${usuario.idusuario}" class="col-12 col-sm-6 col-md-4 d-flex align-items-stretch">
              <div class="card bg-light" >
                <div class="card-header text-muted border-bottom-0">`;
                  if (usuario.tipo_usuario==3) {
                    template+=`<h1 class="badge badge-danger">${usuario.tipo}</h1>`;
                  } 
                  if (usuario.tipo_usuario==1) {
                    template+=`<h1 class="badge badge-warning">${usuario.tipo}</h1>`;
                  } 
                  if (usuario.tipo_usuario==2) {
                    template+=`<h1 class="badge badge-info">${usuario.tipo}</h1>`;
                  } 
                template+=`</div>
                <div class="card-body pt-0">
                  <div class="row">
                    <div class="col-7">
                      <h2 class="lead"><b>${usuario.nombre}</b></h2>
                      
                      <p id="nomusu" class="text-muted text-sm"><b>Nombre de usuario: </b> ${usuario.usuario} </p>
                      <ul class="ml-4 mb-0 fa-ul text-muted">
                        <li class="small"><span class="fa-li"><i class="fas fa-lg fa-calendar-alt"></i></span> Fecha de alta: ${usuario.fecha_alta}</li>
                        <li id="passusu" class="small"><span class="fa-li"><i class="fas fa-lg fa-lock"></i></span> Password: ${usuario.contrasena}</li>
                      </ul>
                    </div>
                    <div class="col-5 text-center">
                      <img src="${usuario.avatar}" alt="" class="img-circle img-fluid">
                    </div>
                  </div>
                </div>
                <div class="card-footer">
                  <div class="text-right">`;
                  if (tipo_usuario==3) {
                    if(usuario.tipo_usuario!=3){
                      template+=`
                        <button class="borrar-usuario btn btn-danger mr-1" type="button" data-toggle="modal" data-target="#confirmar">
                        <i class="fas fa-window-close mr-2 mr-1"></i>Eliminar
                    </button>
                      `;
                    }
                    if (usuario.tipo_usuario==2) {
                      template+=`
                      <button class="ascender btn btn-primary ml-1" type="button" data-toggle="modal" data-target="#confirmar">
                     <i class="fas fa-star mr-2 mr-1"></i>Ascender
                       </button>
                    `;
                    }
                    if (usuario.tipo_usuario==1) {
                      template+=`
                      <button class="descender btn btn-secondary ml-1" type="button" data-toggle="modal" data-target="#confirmar">
                      <i class="fas fa-sort-amount-down mr-1"></i>Descender
                       </button>
                       
                    `;
                    } else {
                      
                    }
                  }else{
                      if (tipo_usuario==1 && usuario.tipo_usuario!=1 && usuario.tipo_usuario!=3) {
                        template+=`
                        <button class="borrar-usuario btn btn-danger mr-1" type="button" data-toggle="modal" data-target="#confirmar">
                        <i class="fas fa-window-close mr-2"></i>Eliminar
                    </button>
                      `;
                      }  
                  }
                    template+=`
                  </div> `;
                  if (tipo_usuario==3) {
                    if(usuario.tipo_usuario!=3){
                      template+=`
                        
                    <li class="list-group-item mt-2">
                        <b style="color:#0b7300">Estado</b>
                        <span class="float-right">
                            <label class="switch">
                                <input type="checkbox" class="toggleEstado" data-idusuario="${usuario.idusuario}" ${usuario.estado == 1 ? 'checked' : ''}>
                                <span class="slider round"></span>
                            </label>
                        </span>
                    </li>
                        <style>
                          .switch {
                            position: relative;
                            display: inline-block;
                            width: 34px;
                            height: 20px;
                          }
                          .switch input {
                            opacity: 0;
                            width: 0;
                            height: 0;
                          }

                          .slider {
                            position: absolute;
                            cursor: pointer;
                            top: 0;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            background-color: #ccc;
                            transition: 0.4s;
                            border-radius: 34px;
                          }

                          .slider:before {
                            position: absolute;
                            content: "";
                            height: 14px;
                            width: 14px;
                            left: 3px;
                            bottom: 3px;
                            background-color: white;
                            transition: 0.4s;
                            border-radius: 50%;
                          }

                          input:checked + .slider {
                            background-color: #2196F3;
                          }

                          input:checked + .slider:before {
                            transform: translateX(14px);
                          }
                        </style>
                      `;
                    }}
                     template+=`
                </div>
              </div>
            </div>
                `;
            });
            $('#usuarios').html(template);
            ocultarCamposUsuarioNormal();
            ocultarCamposUsuarioAdmin();
        });
    }
$(document).on('keyup','#buscar',function(){
    let valor =$(this).val();
    if(valor != ""){
        buscar_datos(valor);
    }else{
        buscar_datos();
    }
});

$('#form-crear').submit((e) => {
  let nombres = $('#nombrePer').val();
  let nombreUsu = $('#nombre').val();
  let password = $('#pass').val();

  let funcion = 'crear_usuario';
  $.post('../controller/userController.php', {
      funcion: funcion,
      nombres: nombres, 
      nombreUsu: nombreUsu,
      password: password
  }, (response) => {
      console.log(response); 
      if(response.trim() == 'add') {
          $('#add').hide('slow');
          $('#add').show(1000);
          $('#add').hide(2000);
          $('#form-crear').trigger('reset');
          buscar_datos(); 
      } else {
        $('#noadd').hide('slow');
        $('#noadd').show(1000);
        $('#noadd').hide(4000);
        $('#form-crear').trigger('reset');
      }
  });
  e.preventDefault();
});





$(document).on('click','.ascender',(e)=>{
  const elemento=$(this)[0].activeElement.parentElement.parentElement.parentElement.parentElement;
  const id=$(elemento).attr('usuarioId');
  funcion='ascender';
  $('#id_user').val(id);
  $('#funcion').val(funcion);
});


$(document).on('click','.descender',(e)=>{
  const elemento=$(this)[0].activeElement.parentElement.parentElement.parentElement.parentElement;
  const id=$(elemento).attr('usuarioId');
  funcion='descender';
  $('#id_user').val(id);
  $('#funcion').val(funcion);
});

$(document).on('click','.borrar-usuario',(e)=>{
  const elemento=$(this)[0].activeElement.parentElement.parentElement.parentElement.parentElement;
  const id=$(elemento).attr('usuarioId');
  funcion='borrar_usuario';
  $('#id_user').val(id);
  $('#funcion').val(funcion);
});

$('#form-confirmar').submit(e => {
  let pass = $('#oldPassword').val();
  let id_usuario = $('#id_user').val();
  funcion = $('#funcion').val();
  $.post('../controller/userController.php', {pass, id_usuario, funcion}, (response) => {
    if (response == 'ascendido' || response == 'descendido' || response == 'borrado') {
      $('#confirmado').hide('slow');
      $('#confirmado').show(1000);
      $('#confirmado').hide(2000);
      $('#form-confirmar').trigger('reset');
    } else if (response == 'password_incorrecto') {  // Add this specific check
      $('#rechazado').hide('slow');
      $('#rechazado').show(1000);
      $('#rechazado').hide(2000);
      $('#form-confirmar').trigger('reset');
    } else {
      // Handle any other unexpected responses
      console.log('Unexpected response:', response);
    }
    buscar_datos();
  });
  e.preventDefault();
});

$(document).on('change', '.toggleEstado', function () {
  const estado = $(this).is(':checked') ? 1 : 0;
  const idusuario = $(this).data('idusuario');
  const funcion = 'cambiar_estado';

  $.post('../controller/userController.php', { idusuario, funcion, estado }, function (response) {
      if (response.trim() === 'update') {
          alert('Estado actualizado correctamente.');
      } else {
          alert('Error al actualizar el estado.');
      }
  });
});




})