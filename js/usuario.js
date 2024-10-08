$(document).ready(function() {
    var funcion = '';
    var idusuario = $('#idusuario').val();
    var edit = false;

    console.log('ID Usuario inicial:', idusuario); 
    buscar_usuario(idusuario);

    function buscar_usuario(dato) {
        funcion = 'buscar_usuario';
        $.post('../controller/userController.php', { dato, funcion }, (response) => {
            console.log('Response for buscar_usuario:', response);

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
        console.log('Botón Editar clickeado');
        funcion = 'capturar_datos';
        edit = true;

        $.post('../controller/userController.php', { funcion, idusuario }, (response) => {
            console.log('Response for capturar_datos:', response);

            const usuario = JSON.parse(response);
            if (Array.isArray(usuario) && usuario.length > 0) {
                $('#nombreInput').val(usuario[0].nombre || '');
                $('#usuarioInput').val(usuario[0].usuario || ''); 
            }
        });
    });
});

