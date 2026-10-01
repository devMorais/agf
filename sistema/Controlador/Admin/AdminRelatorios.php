<?php

namespace sistema\Controlador\Admin;

use sistema\Modelo\RelatorioModelo;

/**
 * Classe AdminRelatorios
 *
 * Relatórios das famílias cadastradas: contato, endereço, tempo de cadastro
 * e situação. Registros repetidos da mesma pessoa são agrupados, então cada
 * linha dos relatórios é uma pessoa, não um registro do banco.
 *
 * @author Fernando Aguiar
 */
class AdminRelatorios extends AdminControlador
{
    /** Quantas pessoas por página na listagem. */
    private const POR_PAGINA = 50;

    private RelatorioModelo $relatorio;

    public function __construct()
    {
        parent::__construct();

        $this->relatorio = new RelatorioModelo();
    }

    /**
     * Painel com os números gerais, gráficos e qualidade do cadastro
     * @return void
     */
    public function index(): void
    {
        $resumo = $this->relatorio->resumo();
        $situacoes = $this->relatorio->situacoes();
        $pessoas = (int) ($resumo['pessoas'] ?? 0);

        echo $this->template->renderizar('relatorios/index.html', [
            'resumo' => $resumo,
            'tempoMedio' => $this->tempoCadastro($resumo['media_dias'] ?? null),
            'situacoes' => array_slice($this->situacoesEmLista($situacoes, $pessoas), 0, 6),
            'semInformacao' => (int) ($situacoes['sem_informacao'] ?? 0),
            'porMes' => $this->comPercentual($this->relatorio->porMes(12), 'total'),
            'porAno' => $this->comPercentual($this->relatorio->porAno(), 'total'),
            'porEstado' => $this->comPercentual($this->relatorio->porEstado(), 'total'),
            'porCidade' => $this->comPercentual($this->relatorio->porCidade(8), 'total'),
            'faixas' => $this->faixasEmLista($this->relatorio->faixasTempo(), $pessoas),
            'qualidade' => $this->qualidade($resumo, $pessoas),
            'ultimas' => $this->prepararPessoas($this->relatorio->pessoas(['ordem' => 'recentes'], 8)),
            'geradoEm' => date('d/m/Y H:i'),
        ]);
    }

    /**
     * Relatório por situação das famílias
     * @return void
     */
    public function situacao(): void
    {
        $filtros = $this->filtros();
        $situacoes = $this->relatorio->situacoes($filtros);
        $pessoas = (int) ($situacoes['pessoas'] ?? 0);

        echo $this->template->renderizar('relatorios/situacao.html', [
            'lista' => $this->situacoesEmLista($situacoes, $pessoas),
            'pessoas' => $pessoas,
            'semInformacao' => (int) ($situacoes['sem_informacao'] ?? 0),
            'tresOuMais' => (int) ($situacoes['tres_ou_mais'] ?? 0),
            'mediaTemas' => $situacoes['media_temas'] ?? '0',
            'distribuicao' => $this->distribuicaoEmLista($this->relatorio->distribuicaoTemas(), $pessoas),
            'filtros' => $filtros,
            'estados' => $this->relatorio->estados(),
            'consulta' => $this->consulta($filtros),
            'geradoEm' => date('d/m/Y H:i'),
        ]);
    }

    /**
     * Listagem completa, com filtros, ordenação e paginação
     * @return void
     */
    public function usuarios(): void
    {
        $filtros = $this->filtros();
        $pagina = max(1, (int) ($filtros['pagina'] ?? 1));
        $offset = ($pagina - 1) * self::POR_PAGINA;

        $total = $this->relatorio->totalPessoas($filtros);
        $paginas = (int) max(1, ceil($total / self::POR_PAGINA));

        echo $this->template->renderizar('relatorios/usuarios.html', [
            'pessoas' => $this->prepararPessoas($this->relatorio->pessoas($filtros, self::POR_PAGINA, $offset)),
            'filtros' => $filtros,
            'estados' => $this->relatorio->estados(),
            'rotulosSituacoes' => RelatorioModelo::rotulosSituacoes(),
            'total' => $total,
            'pagina' => $pagina,
            'paginas' => $paginas,
            'primeiro' => $total ? $offset + 1 : 0,
            'ultimo' => min($offset + self::POR_PAGINA, $total),
            'consulta' => $this->consulta($filtros),
            'geradoEm' => date('d/m/Y H:i'),
        ]);
    }

    /**
     * Baixa a listagem filtrada em CSV, pronto para abrir no Excel
     * @return void
     */
    public function exportar(): void
    {
        $filtros = $this->filtros();
        $pessoas = $this->prepararPessoas($this->relatorio->pessoas($filtros, 5000));

        $arquivo = 'relatorio-familias-' . date('Y-m-d-His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $arquivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $saida = fopen('php://output', 'w');

        // BOM: faz o Excel abrir os acentos corretamente
        fwrite($saida, "\xEF\xBB\xBF");

        fputcsv($saida, [
            'ID',
            'Nome',
            'E-mail',
            'Telefone',
            'CPF',
            'CEP',
            'Logradouro',
            'Bairro',
            'Cidade',
            'Estado',
            'Cadastrado em',
            'Tempo de cadastro',
            'Dias cadastrado',
            'Ultimo acesso',
            'Situacao (indicios no relato)',
            'Situacao do cadastro',
        ], ';');

        foreach ($pessoas as $pessoa) {
            fputcsv($saida, [
                $pessoa['id'],
                $pessoa['nome'],
                $pessoa['email'],
                $pessoa['telefone_formatado'],
                $pessoa['cpf_formatado'],
                $pessoa['cep_formatado'],
                $pessoa['logradouro'],
                $pessoa['bairro'],
                $pessoa['cidade'],
                $pessoa['estado'],
                $pessoa['cadastrado_br'],
                $pessoa['tempo_cadastro'],
                $pessoa['dias_cadastrado'],
                $pessoa['ultimo_acesso_br'],
                implode(' | ', $pessoa['situacoes']),
                $pessoa['status'] == 1 ? 'Ativo' : 'Inativo',
            ], ';');
        }

        fclose($saida);
        exit;
    }

    /**
     * Lê os filtros da URL, sempre como texto simples
     * @return array
     */
    private function filtros(): array
    {
        $dados = filter_input_array(INPUT_GET, FILTER_DEFAULT) ?: [];

        $campos = ['busca', 'estado', 'cidade', 'de', 'ate', 'status', 'tempo', 'endereco', 'situacao', 'temas_min', 'ordem', 'pagina'];
        $filtros = [];

        foreach ($campos as $campo) {
            $valor = $dados[$campo] ?? '';
            $filtros[$campo] = is_string($valor) ? trim(strip_tags($valor)) : '';
        }

        return $filtros;
    }

    /**
     * Querystring dos filtros atuais, para manter a seleção nos links
     * @param array $filtros
     * @return string
     */
    private function consulta(array $filtros): string
    {
        unset($filtros['pagina']);

        return http_build_query(array_filter($filtros, fn($valor) => $valor !== '' && $valor !== null));
    }

    /**
     * Acrescenta os campos formatados que as telas e o CSV usam
     * @param array $pessoas
     * @return array
     */
    private function prepararPessoas(array $pessoas): array
    {
        $rotulos = RelatorioModelo::rotulosSituacoes();

        foreach ($pessoas as &$pessoa) {
            $pessoa['telefone_formatado'] = $this->formatarTelefone($pessoa['telefone'] ?? null);
            $pessoa['cpf_formatado'] = $this->formatarCpf($pessoa['cpf'] ?? null);
            $pessoa['cep_formatado'] = $this->formatarCep($pessoa['cep'] ?? null);
            $pessoa['tempo_cadastro'] = $this->tempoCadastro($pessoa['dias_cadastrado'] ?? null);
            $pessoa['cadastrado_br'] = $this->dataBr($pessoa['cadastrado_em'] ?? null);
            $pessoa['ultimo_acesso_br'] = $this->dataBr($pessoa['ultimo_login'] ?? null, 'Nunca acessou');

            $situacoes = [];

            foreach ($rotulos as $chave => $rotulo) {
                if (!empty($pessoa['sit_' . $chave])) {
                    $situacoes[] = $rotulo;
                }
            }

            $pessoa['situacoes'] = $situacoes;
        }

        return $pessoas;
    }

    /**
     * Situações em lista ordenada, com percentual, para as telas
     * @param array $situacoes
     * @param int $pessoas
     * @return array
     */
    private function situacoesEmLista(array $situacoes, int $pessoas): array
    {
        $lista = [];

        foreach (RelatorioModelo::rotulosSituacoes() as $chave => $rotulo) {
            $total = (int) ($situacoes[$chave] ?? 0);

            $lista[] = [
                'chave' => $chave,
                'rotulo' => $rotulo,
                'total' => $total,
                'percentual' => $pessoas > 0 ? round(($total / $pessoas) * 100) : 0,
            ];
        }

        usort($lista, fn(array $a, array $b): int => $b['total'] <=> $a['total']);

        return $lista;
    }

    /**
     * Distribuição por quantidade de situações citadas
     * @param array $linhas
     * @param int $pessoas
     * @return array
     */
    private function distribuicaoEmLista(array $linhas, int $pessoas): array
    {
        $lista = [];

        foreach ($linhas as $linha) {
            $temas = (int) $linha['temas'];
            $total = (int) $linha['total'];

            $lista[] = [
                'temas' => $temas,
                'rotulo' => match (true) {
                    $temas === 0 => 'Nenhuma situação identificada',
                    $temas === 1 => '1 situação citada',
                    default => $temas . ' situações citadas',
                },
                'total' => $total,
                'percentual' => $pessoas > 0 ? round(($total / $pessoas) * 100) : 0,
            ];
        }

        return $lista;
    }

    /**
     * Percentual de cada linha em relação à maior, para desenhar as barras
     * @param array $linhas
     * @param string $coluna
     * @return array
     */
    private function comPercentual(array $linhas, string $coluna): array
    {
        $maior = 0;

        foreach ($linhas as $linha) {
            $maior = max($maior, (int) $linha[$coluna]);
        }

        foreach ($linhas as &$linha) {
            $linha['percentual'] = $maior > 0 ? round(((int) $linha[$coluna] / $maior) * 100) : 0;
        }

        return $linhas;
    }

    /**
     * Transforma as faixas de tempo em lista pronta para a tela
     * @param array $faixas
     * @param int $pessoas
     * @return array
     */
    private function faixasEmLista(array $faixas, int $pessoas): array
    {
        $rotulos = [
            'ate_30_dias' => 'Até 30 dias',
            'ate_6_meses' => 'De 1 a 6 meses',
            'ate_1_ano' => 'De 6 meses a 1 ano',
            'ate_2_anos' => 'De 1 a 2 anos',
            'mais_2_anos' => 'Mais de 2 anos',
        ];

        $lista = [];

        foreach ($rotulos as $chave => $rotulo) {
            $quantidade = (int) ($faixas[$chave] ?? 0);

            $lista[] = [
                'chave' => $chave,
                'rotulo' => $rotulo,
                'total' => $quantidade,
                'percentual' => $pessoas > 0 ? round(($quantidade / $pessoas) * 100) : 0,
            ];
        }

        return $lista;
    }

    /**
     * Quanto do cadastro está realmente preenchido
     * @param array $resumo
     * @param int $pessoas
     * @return array
     */
    private function qualidade(array $resumo, int $pessoas): array
    {
        $itens = [
            'Telefone' => 'com_telefone',
            'Endereço' => 'com_endereco',
            'CEP' => 'com_cep',
            'Logradouro' => 'com_logradouro',
            'Cidade e estado' => 'com_cidade',
            'CPF' => 'com_cpf',
            'Relato da família' => 'com_historia',
            'Situação identificada' => 'com_situacao',
        ];

        $qualidade = [];

        foreach ($itens as $rotulo => $chave) {
            $preenchidos = (int) ($resumo[$chave] ?? 0);

            $qualidade[] = [
                'rotulo' => $rotulo,
                'preenchidos' => $preenchidos,
                'faltando' => max(0, $pessoas - $preenchidos),
                'percentual' => $pessoas > 0 ? round(($preenchidos / $pessoas) * 100) : 0,
            ];
        }

        return $qualidade;
    }

    /**
     * Tempo de cadastro em texto: dias, meses ou anos
     * @param int|string|null $dias
     * @return string
     */
    private function tempoCadastro($dias): string
    {
        if ($dias === null || $dias === '') {
            return '--';
        }

        $dias = (int) $dias;

        if ($dias <= 0) {
            return 'hoje';
        }

        if ($dias < 30) {
            return $dias . ($dias == 1 ? ' dia' : ' dias');
        }

        $meses = intdiv($dias, 30);

        if ($meses < 12) {
            return $meses . ($meses == 1 ? ' mês' : ' meses');
        }

        $anos = intdiv($meses, 12);
        $resto = $meses % 12;

        $texto = $anos . ($anos == 1 ? ' ano' : ' anos');

        if ($resto > 0) {
            $texto .= ' e ' . $resto . ($resto == 1 ? ' mês' : ' meses');
        }

        return $texto;
    }

    /**
     * Data no formato brasileiro
     * @param string|null $data
     * @param string $vazio
     * @return string
     */
    private function dataBr(?string $data, string $vazio = '--'): string
    {
        if (empty($data) || str_starts_with($data, '0000')) {
            return $vazio;
        }

        $tempo = strtotime($data);

        return $tempo ? date('d/m/Y', $tempo) : $vazio;
    }

    /**
     * Telefone no formato (00) 00000-0000
     * @param string|null $telefone
     * @return string
     */
    private function formatarTelefone(?string $telefone): string
    {
        $numero = preg_replace('/\D/', '', (string) $telefone);

        if ($numero === '') {
            return '--';
        }

        if (strlen($numero) === 11) {
            return sprintf('(%s) %s-%s', substr($numero, 0, 2), substr($numero, 2, 5), substr($numero, 7));
        }

        if (strlen($numero) === 10) {
            return sprintf('(%s) %s-%s', substr($numero, 0, 2), substr($numero, 2, 4), substr($numero, 6));
        }

        return $numero;
    }

    /**
     * CPF no formato 000.000.000-00
     * @param string|null $cpf
     * @return string
     */
    private function formatarCpf(?string $cpf): string
    {
        $numero = preg_replace('/\D/', '', (string) $cpf);

        if ($numero === '') {
            return '--';
        }

        if (strlen($numero) !== 11) {
            return $numero;
        }

        return sprintf(
            '%s.%s.%s-%s',
            substr($numero, 0, 3),
            substr($numero, 3, 3),
            substr($numero, 6, 3),
            substr($numero, 9)
        );
    }

    /**
     * CEP no formato 00000-000
     * @param string|null $cep
     * @return string
     */
    private function formatarCep(?string $cep): string
    {
        $numero = preg_replace('/\D/', '', (string) $cep);

        if ($numero === '') {
            return '--';
        }

        if (strlen($numero) !== 8) {
            return $numero;
        }

        return substr($numero, 0, 5) . '-' . substr($numero, 5);
    }
}
