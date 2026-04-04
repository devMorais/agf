$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
    var url = $('table').attr('url');

    $.extend($.fn.dataTable.defaults, {
        language: {
            url: url + 'templates/admin/assets/js/pt-BR.json'
        },
        initComplete: function (settings, json) {
            $('[tooltip="tooltip"]').tooltip();
        }
    });

    //TABELA ENDERECOS
    $('#tabelaEnderecos').DataTable({
        order: [[0, 'desc']],
        processing: true,
        serverSide: true,
        ajax: {
            url: url + 'admin/enderecos/datatable',
            type: 'POST',
            error: function (xhr, resp, text) {
                console.log(xhr, resp, text);
            }
        },
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5 }
        ],
        columnDefs: [
            {
                className: 'dt-body-left',
                targets: [0]
            },
            {
                className: 'dt-center',
                targets: [1, 2, 3, 4, 5]
            },
        ]
    });

    $('#tabelaPosts').DataTable({
        order: [[0, 'desc']],
        processing: true,
        serverSide: true,
        ajax: {
            url: url + 'admin/posts/datatable',
            type: 'POST',
            error: function (xhr, resp, text) {
                console.log(xhr, resp, text);
            }
        },

        columns: [
            null,
            {
                data: null,
                render: function (data, type, row) {
                    if (row[1]) {
                        return '<a data-fancybox data-caption="Capa" class="overflow zoom" href="' + url + 'uploads/imagens/' + row[1] + '"><img class="thumb" src=" ' + url + 'uploads/imagens/thumbs/' + row[1] + ' " /></a>';
                    } else {
                        return '<i class="fa-regular fa-images fs-1 text-secondary"></i>';
                    }
                }
            },
            null, null, null,
            {
                data: null,
                render: function (data, type, row) {
                    if (row[5] === 1) {
                        return '<i class="fa-solid fa-circle text-success" tooltip="tooltip" title="Ativo"></i>';
                    } else {
                        return '<i class="fa-solid fa-circle text-danger" tooltip="tooltip" title="Inativo"></i>';
                    }
                }
            },
            {
                data: null,
                render: function (data, type, row) {
                    var html = '';

                    html += ' <a href=" ' + url + 'admin/posts/editar/' + row[0] + ' " tooltip="tooltip" title="Editar"><i class="fa-solid fa-pen m-1"></i></a> ';

                    //                    html += '<a href=" ' + url + 'admin/posts/deletar/' + row[0] + ' "><i class="fa-solid fa-trash m-1" tooltip="tooltip" title="Deletar"></i></a>';

                    return html;
                }
            }
        ],
        columnDefs: [
            {
                className: 'dt-body-left',
                targets: [0]
            },
            {
                className: 'dt-center',
                targets: [2, 3, 4, 5, 6]
            },
            {
                orderable: false,
                targets: [-1]
            }

        ]
    });
    //TABELA USUÁRIOS
    $('#tabelaUsuarios').DataTable({
        order: [[0, 'desc']],
        processing: true,
        serverSide: true,
        ajax: {
            url: url + 'admin/usuarios/datatable',
            type: 'POST',
            error: function (xhr, resp, text) {
                console.log(xhr, resp, text);
            }
        },
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 }
        ],
        columnDefs: [
            {
                className: 'dt-body-left',
                targets: [0]
            },
            {
                className: 'dt-center',
                targets: [1, 2, 3, 4]
            },
            {
                orderable: false,
                targets: [-1]
            },
            {
                width: "50%",
                targets: [3]
            }
        ]
    });

    //TABELA INSCREVA-SE
    $('#tabelaInscricao').DataTable({
        order: [[0, 'desc']],
        processing: true,
        serverSide: true,
        ajax: {
            url: url + 'admin/inscricoes/datatable',
            type: 'POST',
            error: function (xhr, resp, text) {
                console.log(xhr, resp, text);
            }
        },
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 }
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
        ]
    });
});
