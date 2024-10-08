$(document).ready(function() {
    var funcion = '';
    var idusuario = $('#idusuario').val();
    buscar_usuario(idusuario);

    function buscar_usuario(dato) {
        funcion = 'buscar_usuario';
        $.post('../controller/userController.php', { dato, funcion }, (response) => {
            console.log(response);
            let nombre = '';
            let usuarios = '';
            let estado = '';
            let tipo = '';

            const usuario = JSON.parse(response);
            if (usuario.length > 0) {
                nombre += `${usuario[0].nombre}`;
                usuarios += `${usuario[0].usuario}`;
                estado += `${usuario[0].estado}`;
                tipo += `${usuario[0].tipo}`;
                $('#nombre').html(nombre);
                $('#usuario').html(usuarios);
                $('#estado').html(estado);
                $('#tipo_usuario_id').html(tipo);

            }
        });
    }
});
