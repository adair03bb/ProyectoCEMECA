$(document).ready(function () {
    // Cargar lista de municipios
    function cargarMunicipios() {
        $.post('../controller/userController.php', { funcion: 'obtener_municipios' }, function (response) {
            let municipios = JSON.parse(response);
            let dropdown = $('#municipio');
            municipios.forEach(municipio => {
                dropdown.append(new Option(municipio.municipio, municipio.municipio));
            });
        });
    }

    // Obtener la edad más común por municipio (gráfico por defecto)
    function obtenerEdadMasComun() {
        $.post('../controller/userController.php', { funcion: 'obtener_edad_mas_comun' }, function (response) {
            let datos = JSON.parse(response);
            let data = [['Municipio', 'Edad más común']];
            datos.forEach(dato => {
                data.push([`${dato.municipio}`, parseInt(dato.edad)]);
            });
            drawDefaultChart(data);
        });
    }

    // Obtener edades por municipio (al seleccionar del desplegable)
    function obtenerEdadesPorMunicipio(municipio) {
        $.post('../controller/userController.php', { funcion: 'obtener_edades_por_municipio', municipio: municipio }, function (response) {
            let datos = JSON.parse(response);
            let data = [['Edad', 'Total']];
            datos.forEach(dato => {
                data.push([`EDAD: ${dato.edad}`, parseInt(dato.total)]);
            });
            drawChart(data);
        });
    }

    function drawDefaultChart(data) {
        google.charts.load('current', { packages: ['corechart', 'bar'] });
        google.charts.setOnLoadCallback(() => {
            var dataTable = google.visualization.arrayToDataTable(data);
    
            var options = {
                title: 'Edad más común por municipio',
                chartArea: { width: '70%', height: '80%' }, 
                hAxis: { 
                    title: 'Edad', 
                    minValue: 0, 
                    textStyle: { fontSize: 12 } 
                },
                vAxis: { 
                    title: 'Municipio', 
                    textStyle: { fontSize: 3 }, 
                },
                bars: 'horizontal',
                legend: { position: 'none' }, 
                colors: ['#4285F4'], 
                bar: { groupWidth: '50%' },
                
            };
    
            var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
            chart.draw(dataTable, options);
        });
    }
    
    function drawChart(data) {
        google.charts.load('current', { packages: ['corechart', 'bar'] });
        google.charts.setOnLoadCallback(() => {
            var dataTable = google.visualization.arrayToDataTable(data);
    
            var options = {
                title: 'Edades por Municipio',
                chartArea: { width: '70%', height: '80%' }, 
                hAxis: { 
                    title: 'Total de Personas', 
                    minValue: 0, 
                    textStyle: { fontSize: 12 }, 
                    slantedText: true,
                    slantedTextAngle: 45 
                },
                
                vAxis: { 
                    title: 'Categorías', 
                    textStyle: { fontSize: 12 },
                },
                bars: 'horizontal',
                legend: { position: 'none' }, 
                colors: ['#34A853'], 
                bar: { groupWidth: '60%' }, 
            };
    
            var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
            chart.draw(dataTable, options);
        });
    }
    

    // Evento al cambiar el municipio
    $('#municipio').on('change', function () {
        let municipio = $(this).val();
        if (municipio) {
            obtenerEdadesPorMunicipio(municipio);
        }
    });

    // Cargar lista de municipios y gráfico por defecto
    cargarMunicipios();
    obtenerEdadMasComun(); // Mostrar gráfico por defecto
});