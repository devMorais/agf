$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
    var url = $('#tabelaInscrevase').attr('url');
    $.extend($.fn.dataTable.defaults, {
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.3/i18n/pt-BR.json'
        },
        initComplete: function (settings, json) {
            $('[tooltip="tooltip"]').tooltip( );
        }
    });

    //TABELA INSCREVA-SE
    $('#tabelaInscrevase').DataTable({
        order: [[0, 'desc']],
        processing: true,
        serverSide: true,
        ajax: {
            url: url + 'saas/datatable',
            type: 'POST',
            error: function (xhr, resp, text) {
                console.log(xhr, resp, text);
            }
        },
        columns: [
            {data: 0},
            {data: 1},
            {data: 2}
        ],
        columnDefs: [
            {
                className: 'dt-body-left',
                targets: [0]
            },
            {
                className: 'dt-center',
                targets: [1, 2]
            },
            {
                orderable: false,
                targets: [-1]
            }

        ]
    });
});
