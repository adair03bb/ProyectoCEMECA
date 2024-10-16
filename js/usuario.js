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
                $('#avatar2').attr('src',usuario.avatar);
                $('#avatar1').attr('alt',usuario.avatar);
                $('#avatar3').attr('alt',usuario.avatar);
                $('#avatar4').attr('alt',usuario.avatar);

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
            if (oldPassword === '' || newPassword === '') {
            alert('Las contraseñas no pueden estar vacías.');
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
    })
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
        try {
            const json = JSON.parse(response);
            if (json[0].alert == 'edit') {
                $('#avatar2').attr('src', json[0].ruta);
                $('#edit').hide('slow');
                $('#edit').show(1000);
                $('#edit').hide(2000);
                $('#form-photo').trigger('reset');
            } else {
                $('#noedit').hide('slow');
                $('#noedit').show(1000);
                $('#noedit').hide(2000);
                $('#form-photo').trigger('reset');
            }
        } catch (error) {
            console.error('Error al analizar JSON:', error);
        }
    });
    e.preventDefault();
});

});
