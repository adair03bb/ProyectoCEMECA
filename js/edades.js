$(document).ready(function () {
    // Cargar lista de municipios
    function cargarMunicipios() {
        $.post('../controller/userController.php', { funcion: 'obtener_municipios' }, function (response) {
            let municipios = JSON.parse(response);
            let dropdown = $('#municipio');
            dropdown.empty(); 
            dropdown.append('<option value="">Seleccione un Municipio</option>');
            municipios.forEach(municipio => {
                dropdown.append(new Option(municipio.municipio, municipio.municipio));
            });
        });
    }

    // Cargar tipos de gráfico
    function cargarTiposGrafico() {
        let dropdown = $('#tipo-grafico');
        dropdown.append('<option value="bar">Gráfico de Barras</option>');
        dropdown.append('<option value="pie">Gráfico de Pastel</option>');
        dropdown.append('<option value="column">Gráfico de Columnas</option>');
    }

    // Obtener la edad más común por municipio
    function obtenerEdadMasComun(tipoGrafico = 'bar') {
        $.post('../controller/userController.php', { funcion: 'obtener_edad_mas_comun' }, function (response) {
            let datos = JSON.parse(response);
            let data = [['Municipio', 'Edad más común']];
            datos.forEach(dato => {
                data.push([`${dato.municipio}`, parseInt(dato.edad)]);
            });
            drawChart(data, 'Edad más común por municipio', tipoGrafico);
        });
    }

    // Obtener edades por municipio
    function obtenerEdadesPorMunicipio(municipio, tipoGrafico = 'bar') {
        $.post('../controller/userController.php', { funcion: 'obtener_edades_por_municipio', municipio: municipio }, function (response) {
            let datos = JSON.parse(response);
            let data = [['Edad', 'Total']];
            datos.forEach(dato => {
                data.push([`EDAD: ${dato.edad}`, parseInt(dato.total)]);
            });
            drawChart(data, `Edades en ${municipio}`, tipoGrafico);
        });
    }

    // Descargar gráfico como PDF
    function descargarGraficoPDF() {
        var { jsPDF } = window.jspdf;
        var pdf = new jsPDF('landscape');
        
        html2canvas(document.getElementById('chart_div')).then(canvas => {
            var imgData = canvas.toDataURL('image/png');
            
            pdf.setFontSize(16);
            pdf.text('Reporte de Edades por Municipio', 15, 15);
            
            pdf.addImage(imgData, 'PNG', 15, 25, 270, 150);
            
            var municipioSeleccionado = $('#municipio').val() || 'Todos los Municipios';
            var tipoGraficoSeleccionado = $('#tipo-grafico option:selected').text();
            pdf.setFontSize(12);
            pdf.text(`Municipio: ${municipioSeleccionado}`, 15, 180);
            pdf.text(`Tipo de Gráfico: ${tipoGraficoSeleccionado}`, 15, 190);
            
            pdf.save('reporte_edades_municipio.pdf');
        });
    }

    // Dibujar gráfico
    function drawChart(data, titulo, tipoGrafico) {
        google.charts.load('current', { packages: ['corechart'] });
        google.charts.setOnLoadCallback(() => {
            var dataTable = google.visualization.arrayToDataTable(data);
    
            var options = {
                title: titulo,
                chartArea: { width: '70%', height: '80%' },
                hAxis: { 
                    title: 'Valores', 
                    textStyle: { fontSize: 12 },
                    slantedText: true,
                    slantedTextAngle: 45 
                },
                vAxis: { 
                    title: 'Categorías', 
                    textStyle: { fontSize: 12 },
                },
                legend: { position: 'none' },
                colors: ['#4285F4'],
            };
    
            var chart;
            switch(tipoGrafico) {
                case 'bar':
                    chart = new google.visualization.BarChart(document.getElementById('chart_div'));
                    options.bars = 'horizontal';
                    break;
                case 'pie':
                    chart = new google.visualization.PieChart(document.getElementById('chart_div'));
                    delete options.hAxis;
                    delete options.vAxis;
                    break;
                case 'column':
                    chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
                    options.bars = 'vertical';
                    break;
                default:
                    chart = new google.visualization.BarChart(document.getElementById('chart_div'));
            }
    
            chart.draw(dataTable, options);
        });
    }

    // Evento al cambiar el municipio
    $('#municipio').on('change', function () {
        let municipio = $(this).val();
        let tipoGrafico = $('#tipo-grafico').val() || 'bar';
        if (municipio) {
            obtenerEdadesPorMunicipio(municipio, tipoGrafico);
        } else {
            obtenerEdadMasComun(tipoGrafico);
        }
    });

    // Evento al cambiar el tipo de gráfico
    $('#tipo-grafico').on('change', function () {
        let municipio = $('#municipio').val();
        let tipoGrafico = $(this).val();
        if (municipio) {
            obtenerEdadesPorMunicipio(municipio, tipoGrafico);
        } else {
            obtenerEdadMasComun(tipoGrafico);
        }
    });

    // Botón de descarga
    $('#botonDescargarPdf').on('click', descargarGraficoPDF);

    // Inicializar
    cargarMunicipios();
    cargarTiposGrafico();
    obtenerEdadMasComun();
});