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
});
