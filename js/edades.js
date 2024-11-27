$(document).ready(function () {
    // Load municipalities list
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

    // Load chart types
    function cargarTiposGrafico() {
        let dropdown = $('#tipo-grafico');
        dropdown.empty();
        dropdown.append('<option value="bar">Gráfico de Barras</option>');
        dropdown.append('<option value="pie">Gráfico de Pastel</option>');
        dropdown.append('<option value="column">Gráfico de Columnas</option>');
    }

    // Get most common age by municipality
    function obtenerEdadMasComun(tipoGrafico = 'bar') {
        $.post('../controller/userController.php', { funcion: 'obtener_edad_mas_comun' }, function (response) {
            let datos = JSON.parse(response);
            datos = datos.slice(0, 20);
            let data = [['Municipio', 'Edad más común']];
            datos.forEach(dato => {
                data.push([`${dato.municipio}`, parseInt(dato.edad)]);
            });
            drawChart(data, 'Edad más común por municipio', tipoGrafico);
        });
    }

    // Get ages by municipality
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

    // Download chart as PDF
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

    // Draw chart
    function drawChart(data, titulo, tipoGrafico) {
        google.charts.load('current', { packages: ['corechart'] });
        google.charts.setOnLoadCallback(() => {
            var dataTable = google.visualization.arrayToDataTable(data);
    
            // Generate colors dynamically based on data points
            var colors = generateDynamicColors(data.length - 1);
    
            var options = {
                title: titulo,
                chartArea: { width: '70%', height: '70%' },
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
                colors: colors,
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
                    options.legend = { position: 'right' }; // Add legend for pie chart
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
    
    // Advanced color generation function
    function generateDynamicColors(count) {
        const baseColors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', 
            '#FF9F40', '#FF5370', '#4ECDC4', '#C7F464', '#6610F2',
            '#6f42c1', '#20c997', '#fd7e14', '#007bff', '#e83e8c',
            '#17a2b8', '#ffc107', '#28a745', '#dc3545', '#f8f9fa'
        ];
    
        if (count <= baseColors.length) {
            return baseColors.slice(0, count);
        }
    
        let extendedColors = [...baseColors];
        while (extendedColors.length < count) {
            const newColor = baseColors[extendedColors.length % baseColors.length];
            const modifiedColor = shadeColor(newColor, Math.random() * 0.4 - 0.2);
            extendedColors.push(modifiedColor);
        }
    
        return extendedColors;
    }
    
    // Color shade modification function
    function shadeColor(color, percent) {
        const num = parseInt(color.slice(1), 16);
        const amt = Math.round(2.55 * percent);
        const R = (num >> 16) + amt;
        const B = ((num >> 8) & 0x00ff) + amt;
        const G = (num & 0x0000ff) + amt;
    
        return "#" + (0x1000000 + (R<255?R<1?0:R:255)*0x10000 +
            (B<255?B<1?0:B:255)*0x100 +
            (G<255?G<1?0:G:255)).toString(16).slice(1);
    }

    // Event when municipality changes
    $('#municipio').on('change', function () {
        let municipio = $(this).val();
        let tipoGrafico = $('#tipo-grafico').val() || 'bar';
        if (municipio) {
            obtenerEdadesPorMunicipio(municipio, tipoGrafico);
        } else {
            obtenerEdadMasComun(tipoGrafico);
        }
    });

    // Event when chart type changes
    $('#tipo-grafico').on('change', function () {
        let municipio = $('#municipio').val();
        let tipoGrafico = $(this).val();
        if (municipio) {
            obtenerEdadesPorMunicipio(municipio, tipoGrafico);
        } else {
            obtenerEdadMasComun(tipoGrafico);
        }
    });

    // Download button
    $('#botonDescargarPdf').on('click', descargarGraficoPDF);

    // Initialize
    cargarMunicipios();
    cargarTiposGrafico();
    obtenerEdadMasComun();
});