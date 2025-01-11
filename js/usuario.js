$(document).ready(function() {
    var funcion = '';
    var idusuario = $('#idusuario').val();
    var edit = false;

    buscar_usuario(idusuario);

    function buscar_usuario(dato) {
        funcion = 'buscar_usuario';
        $.post('../controller/userController.php', { dato, funcion }, (response) => {
            const usuario = JSON.parse(response);
            if (usuario.length > 0) {
                $('#nombre').html(usuario[0].nombre);
                $('#usuario').html(usuario[0].usuario);
                $('#estado').html(usuario[0].estado == 1 ? 'Activo' : 'Inactivo');
                $('#tipo_usuario_id').html(usuario[0].tipo);
                $('#avatar2').attr('src',usuario[0].avatar);
                $('#ava').attr('src',usuario[0].avatar);
                $('#avatar3').attr('src',usuario[0].avatar);
                $('#avatar4').attr('src',usuario[0].avatar);
                $('#avatar5').attr('src',usuario[0].avatar);
                $('#avatar1').attr('src',usuario[0].avatar);
            }
        });
    }

    $(document).on('click', '.edit', (e) => {
        funcion = 'capturar_datos';
        edit = true;
        $.post('../controller/userController.php', { funcion, idusuario }, (response) => {
            const usuario = JSON.parse(response);
            if (Array.isArray(usuario) && usuario.length > 0) {
                $('#nombreInput').val(usuario[0].nombre || '');
                $('#usuarioInput').val(usuario[0].usuario || ''); 
            }
        });
    });

    $('#form-usuario').submit(e => {
        if (edit == true) {
            let nombre = $('#nombreInput').val();
            let usuario = $('#usuarioInput').val();
            funcion = 'editar_usuario';
            $.post('../controller/userController.php', { idusuario, funcion, nombre, usuario }, (response) => {
                if (response.trim() == 'editado') {
                    $('#editado').hide('slow');
                    $('#editado').show(1000);
                    $('#editado').hide(2000);
                    $('#form-usuario').trigger('reset');
                } else {
                    console.log('Error al editar usuario:', response);
                }
                edit = false;
                buscar_usuario(idusuario);
            });
        }
        else{
            $('#noeditado').hide('slow');
            $('#noeditado').show(1000);
            $('#noeditado').hide(2000);
            $('#form-usuario').trigger('reset');
        }
        e.preventDefault();
    });
    $('#form-pass').submit(e => {
        e.preventDefault();
        let oldPassword = $('#oldPassword').val();
        let newPassword = $('#newPassword').val();
        let idusuario = $('#idusuario').val();
        let funcion = 'cambiar_contra';
    
        // Add length validation
        if (oldPassword === '' || newPassword === '') {
            $('#vacia').hide('slow');
            $('#vacia').show(1000);
            $('#vacia').hide(2000);
            return;
        }
    
        // New length check
        if (newPassword.length > 10) {
            $('#vacia').hide();
            $('#noupdate').show(1000);
            $('#noupdate').hide(2000);
            return;
        }
    
        $.post('../controller/userController.php', { idusuario, funcion, oldPassword, newPassword }, (response) => {
            if (response.trim() == 'update') {
                $('#update').hide('slow');
                $('#update').show(1000);
                $('#update').hide(2000);
                $('#form-pass').trigger('reset');
            } else {
                $('#noupdate').hide('slow');
                $('#noupdate').show(1000);
                $('#noupdate').hide(2000);
                $('#form-pass').trigger('reset');
            }
        });
    });

$('#form-photo').submit(e => {
    let formData = new FormData($('#form-photo')[0]);
    $.ajax({
        url: '../controller/userController.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        cache: false
    }).done(function(response) {
        const json = JSON.parse(response);
        if(json.alert=='edit'){
            $('#avatar2').attr('src', json.ruta);
            $('#avatar4').attr('src', json.ruta);
            $('#avatar1').attr('src', json.ruta);
            $('#edit').hide('slow');
            $('#edit').show(1000);
            $('#edit').hide(10000);
            $('#form-photo').trigger('reset');
            buscar_usuario(idusuario);
        }else{
            $('#noedit').hide('slow');
            $('#noedit').show(1000);
            $('#noedit').hide(2000);
            $('#form-photo').trigger('reset');
        }
    });
    e.preventDefault();
});



});
