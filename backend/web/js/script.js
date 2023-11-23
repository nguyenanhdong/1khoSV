$(document).ready( function() {
    $('table.table').DataTable( {
        dom: 'Bfrtip',
        
        buttons: [ {
            extend: 'excelHtml5',
            
        } ]
    } );
} );