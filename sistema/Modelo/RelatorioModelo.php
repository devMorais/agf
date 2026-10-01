<?php

namespace sistema\Modelo;

use sistema\Nucleo\Conexao;

/**
 * Consultas dos relatórios do painel administrativo.
 *
 * Trabalha direto com PDO, e não com o Active Record do sistema, porque os
 * relatórios precisam de JOIN, GROUP BY e do agrupamento de pessoas repetidas.
 * Tudo que vem da tela entra como parâmetro nomeado, nunca concatenado na SQL.
 *
 * @author Fernando Aguiar
 */
class RelatorioModelo
{
    /** Nível 3 é administrador: fica fora dos relatórios de cadastrados. */
    private const NIVEL_ADMIN = 3;

    private const NOME_NORM = "LOWER(TRIM(u.nome))";
    private const TEL_NORM = "REGEXP_REPLACE(COALESCE(u.telefone,''),'[^0-9]','')";
    private const CPF_NORM = "REGEXP_REPLACE(COALESCE(u.cpf,''),'[^0-9]','')";
    private const EMAIL_NORM = "LOWER(TRIM(u.email))";

    /**
     * Chave que identifica a mesma pessoa em registros diferentes:
     * nome + telefone quando existem os dois, senão CPF, senão e-mail.
     */
    private const CHAVE_PESSOA = "CASE
            WHEN " . self::NOME_NORM . " <> '' AND " . self::TEL_NORM . " <> ''
                THEN CONCAT('nt:', " . self::NOME_NORM . ", '|', " . self::TEL_NORM . ")
            WHEN " . self::CPF_NORM . " <> '' THEN CONCAT('cpf:', " . self::CPF_NORM . ")
            ELSE CONCAT('em:', " . self::EMAIL_NORM . ")
        END";

    /**
     * Base de todos os relatórios: uma linha por pessoa (duplicados agrupados),
     * já com o endereço mais recente de cada uma.
     */
    private const DE_PESSOAS = "
        FROM (
            SELECT MIN(u.id) AS id_principal,
                   COUNT(*) AS qtd_registros
              FROM usuarios u
             WHERE u.level <> " . self::NIVEL_ADMIN . "
             GROUP BY " . self::CHAVE_PESSOA . "
        ) g
        INNER JOIN usuarios u ON u.id = g.id_principal
        LEFT JOIN (
            SELECT usuario_id, MAX(id) AS id_endereco FROM enderecos GROUP BY usuario_id
        ) ue ON ue.usuario_id = u.id
        LEFT JOIN enderecos e ON e.id = ue.id_endereco";

    /** Critérios aceitos no relatório de duplicados. */
    private const CRITERIOS = [
        'pessoa' => self::CHAVE_PESSOA,
        'telefone' => self::TEL_NORM,
        'nome' => self::NOME_NORM,
        'cpf' => self::CPF_NORM,
        'email' => self::EMAIL_NORM,
    ];

    /** Ordenações aceitas na listagem. */
    private const ORDENS = [
        'recentes' => 'u.cadastrado_em DESC',
        'antigos' => 'u.cadastrado_em ASC',
        'nome' => 'u.nome ASC',
        'cidade' => 'e.cidade IS NULL, e.cidade ASC, u.nome ASC',
        'repetidos' => 'g.qtd_registros DESC, u.nome ASC',
    ];

    /**
     * Números gerais do cadastro, já sem contar duplicados
     * @param array $filtros
     * @return array
     */
    public function resumo(array $filtros = []): array
    {
        [$onde, $parametros] = $this->filtros($filtros);

        $sql = "SELECT
                    COUNT(*) AS pessoas,
                    COALESCE(SUM(g.qtd_registros), 0) AS registros,
                    COALESCE(SUM(g.qtd_registros), 0) - COUNT(*) AS repetidos,
                    COALESCE(SUM(g.qtd_registros > 1), 0) AS pessoas_repetidas,
                    COALESCE(SUM(e.id IS NOT NULL), 0) AS com_endereco,
                    COALESCE(SUM(e.cep IS NOT NULL AND e.cep <> ''), 0) AS com_cep,
                    COALESCE(SUM(e.logradouro IS NOT NULL AND e.logradouro <> ''), 0) AS com_logradouro,
                    COALESCE(SUM(e.cidade IS NOT NULL AND e.cidade <> ''), 0) AS com_cidade,
                    COALESCE(SUM(" . self::TEL_NORM . " <> ''), 0) AS com_telefone,
                    COALESCE(SUM(" . self::CPF_NORM . " <> ''), 0) AS com_cpf,
                    COALESCE(SUM(u.texto IS NOT NULL AND u.texto <> ''), 0) AS com_historia,
                    COALESCE(SUM(u.status = 1), 0) AS ativos,
                    COALESCE(SUM(u.status = 0), 0) AS inativos,
                    COALESCE(SUM(u.ultimo_login IS NOT NULL), 0) AS ja_acessaram,
                    COALESCE(SUM(u.cadastrado_em >= DATE_SUB(NOW(), INTERVAL 30 DAY)), 0) AS novos_30,
                    COALESCE(SUM(u.cadastrado_em >= DATE_SUB(NOW(), INTERVAL 90 DAY)), 0) AS novos_90,
                    COALESCE(SUM(u.cadastrado_em >= DATE_SUB(NOW(), INTERVAL 365 DAY)), 0) AS novos_365,
                    MIN(u.cadastrado_em) AS mais_antigo,
                    MAX(u.cadastrado_em) AS mais_novo,
                    ROUND(AVG(TIMESTAMPDIFF(DAY, u.cadastrado_em, NOW()))) AS media_dias
                " . self::DE_PESSOAS . " {$onde}";

        $resumo = $this->consultar($sql, $parametros);

        return $resumo[0] ?? [];
    }

    /**
     * Uma linha por pessoa, com contato, endereço e tempo de cadastro
     * @param array $filtros
     * @param int $limite
     * @param int $offset
     * @return array
     */
    public function pessoas(array $filtros = [], int $limite = 50, int $offset = 0): array
    {
        [$onde, $parametros] = $this->filtros($filtros);
        $ordem = self::ORDENS[$filtros['ordem'] ?? ''] ?? self::ORDENS['recentes'];

        $limite = max(1, min($limite, 5000));
        $offset = max(0, $offset);

        $sql = "SELECT u.id, u.nome, u.email, u.telefone, u.cpf, u.status, u.texto,
                       u.cadastrado_em, u.atualizado_em, u.ultimo_login,
                       TIMESTAMPDIFF(DAY, u.cadastrado_em, NOW()) AS dias_cadastrado,
                       e.cep, e.logradouro, e.bairro, e.cidade, e.estado,
                       g.qtd_registros
                " . self::DE_PESSOAS . " {$onde}
                ORDER BY {$ordem}
                LIMIT {$limite} OFFSET {$offset}";

        return $this->consultar($sql, $parametros);
    }

    /**
     * Total de pessoas que atendem aos filtros
     * @param array $filtros
     * @return int
     */
    public function totalPessoas(array $filtros = []): int
    {
        [$onde, $parametros] = $this->filtros($filtros);

        $sql = "SELECT COUNT(*) AS total " . self::DE_PESSOAS . " {$onde}";
        $linha = $this->consultar($sql, $parametros);

        return (int) ($linha[0]['total'] ?? 0);
    }

    /**
     * Cadastros por mês
     * @param int $meses
     * @return array
     */
    public function porMes(int $meses = 12): array
    {
        $meses = max(1, min($meses, 60));

        $sql = "SELECT DATE_FORMAT(u.cadastrado_em, '%Y-%m') AS mes, COUNT(*) AS total
                " . self::DE_PESSOAS . "
                WHERE u.cadastrado_em >= DATE_SUB(NOW(), INTERVAL {$meses} MONTH)
                GROUP BY mes
                ORDER BY mes";

        return $this->consultar($sql);
    }

    /**
     * Cadastros por ano
     * @return array
     */
    public function porAno(): array
    {
        $sql = "SELECT YEAR(u.cadastrado_em) AS ano, COUNT(*) AS total
                " . self::DE_PESSOAS . "
                GROUP BY ano
                ORDER BY ano";

        return $this->consultar($sql);
    }

    /**
     * Distribuição por estado, incluindo quem está sem a informação
     * @return array
     */
    public function porEstado(): array
    {
        $sql = "SELECT COALESCE(NULLIF(TRIM(e.estado), ''), 'Não informado') AS estado, COUNT(*) AS total
                " . self::DE_PESSOAS . "
                GROUP BY estado
                ORDER BY total DESC, estado";

        return $this->consultar($sql);
    }

    /**
     * Distribuição por cidade (só quem tem a cidade preenchida)
     * @param int $limite
     * @return array
     */
    public function porCidade(int $limite = 10): array
    {
        $limite = max(1, min($limite, 100));

        $sql = "SELECT TRIM(e.cidade) AS cidade,
                       COALESCE(NULLIF(TRIM(e.estado), ''), '--') AS estado,
                       COUNT(*) AS total
                " . self::DE_PESSOAS . "
                WHERE e.cidade IS NOT NULL AND TRIM(e.cidade) <> ''
                GROUP BY cidade, estado
                ORDER BY total DESC, cidade
                LIMIT {$limite}";

        return $this->consultar($sql);
    }

    /**
     * Quantas pessoas em cada faixa de tempo de cadastro
     * @return array
     */
    public function faixasTempo(): array
    {
        $sql = "SELECT
                    COALESCE(SUM(TIMESTAMPDIFF(DAY, u.cadastrado_em, NOW()) <= 30), 0) AS ate_30_dias,
                    COALESCE(SUM(TIMESTAMPDIFF(DAY, u.cadastrado_em, NOW()) BETWEEN 31 AND 180), 0) AS ate_6_meses,
                    COALESCE(SUM(TIMESTAMPDIFF(DAY, u.cadastrado_em, NOW()) BETWEEN 181 AND 365), 0) AS ate_1_ano,
                    COALESCE(SUM(TIMESTAMPDIFF(DAY, u.cadastrado_em, NOW()) BETWEEN 366 AND 730), 0) AS ate_2_anos,
                    COALESCE(SUM(TIMESTAMPDIFF(DAY, u.cadastrado_em, NOW()) > 730), 0) AS mais_2_anos
                " . self::DE_PESSOAS;

        $linha = $this->consultar($sql);

        return $linha[0] ?? [];
    }

    /**
     * Registros repetidos, agrupados pelo critério escolhido
     * @param string $criterio
     * @return array lista de grupos, cada um com a chave e os registros
     */
    public function duplicados(string $criterio = 'pessoa'): array
    {
        $expressao = self::CRITERIOS[$criterio] ?? self::CRITERIOS['pessoa'];

        // Em CPF e telefone o valor vazio não conta como repetição
        $naoVazio = in_array($criterio, ['telefone', 'cpf'], true) ? "AND {$expressao} <> ''" : '';

        $sql = "SELECT u.id, u.nome, u.email, u.telefone, u.cpf, u.status,
                       u.cadastrado_em, u.ultimo_login,
                       TIMESTAMPDIFF(DAY, u.cadastrado_em, NOW()) AS dias_cadastrado,
                       e.cep, e.logradouro, e.bairro, e.cidade, e.estado,
                       {$expressao} AS chave
                  FROM usuarios u
                  LEFT JOIN (
                      SELECT usuario_id, MAX(id) AS id_endereco FROM enderecos GROUP BY usuario_id
                  ) ue ON ue.usuario_id = u.id
                  LEFT JOIN enderecos e ON e.id = ue.id_endereco
                 WHERE u.level <> " . self::NIVEL_ADMIN . " {$naoVazio}
                   AND {$expressao} IN (
                       SELECT chave_repetida FROM (
                           SELECT {$expressao} AS chave_repetida
                             FROM usuarios u
                            WHERE u.level <> " . self::NIVEL_ADMIN . " {$naoVazio}
                            GROUP BY chave_repetida
                           HAVING COUNT(*) > 1
                       ) repetidos
                   )
                 ORDER BY u.nome, u.id";

        $grupos = [];

        foreach ($this->consultar($sql) as $registro) {
            $grupos[$registro['chave']][] = $registro;
        }

        return array_values($grupos);
    }

    /**
     * Quantos registros repetidos existem em cada critério
     * @return array
     */
    public function resumoDuplicados(): array
    {
        $resumo = [];

        foreach (array_keys(self::CRITERIOS) as $criterio) {
            $expressao = self::CRITERIOS[$criterio];
            $naoVazio = in_array($criterio, ['telefone', 'cpf'], true) ? "AND {$expressao} <> ''" : '';

            $sql = "SELECT COUNT(*) AS grupos, COALESCE(SUM(qtd) - COUNT(*), 0) AS excedentes
                      FROM (
                          SELECT COUNT(*) AS qtd
                            FROM usuarios u
                           WHERE u.level <> " . self::NIVEL_ADMIN . " {$naoVazio}
                           GROUP BY {$expressao}
                          HAVING COUNT(*) > 1
                      ) grupos_repetidos";

            $linha = $this->consultar($sql);

            $resumo[$criterio] = [
                'grupos' => (int) ($linha[0]['grupos'] ?? 0),
                'excedentes' => (int) ($linha[0]['excedentes'] ?? 0),
            ];
        }

        return $resumo;
    }

    /**
     * Estados disponíveis para o filtro
     * @return array
     */
    public function estados(): array
    {
        $sql = "SELECT DISTINCT TRIM(estado) AS estado
                  FROM enderecos
                 WHERE estado IS NOT NULL AND TRIM(estado) <> ''
                 ORDER BY estado";

        return array_column($this->consultar($sql), 'estado');
    }

    /**
     * Monta o WHERE e os parâmetros a partir dos filtros da tela
     * @param array $filtros
     * @return array [string $onde, array $parametros]
     */
    private function filtros(array $filtros): array
    {
        $onde = [];
        $parametros = [];

        if (!empty($filtros['busca'])) {
            $onde[] = "(u.nome LIKE :busca OR u.email LIKE :busca OR u.telefone LIKE :busca
                        OR u.cpf LIKE :busca OR e.cep LIKE :busca OR e.logradouro LIKE :busca
                        OR e.bairro LIKE :busca OR e.cidade LIKE :busca)";
            $parametros['busca'] = '%' . trim($filtros['busca']) . '%';
        }

        if (!empty($filtros['estado'])) {
            $onde[] = "TRIM(e.estado) = :estado";
            $parametros['estado'] = trim($filtros['estado']);
        }

        if (!empty($filtros['cidade'])) {
            $onde[] = "e.cidade LIKE :cidade";
            $parametros['cidade'] = '%' . trim($filtros['cidade']) . '%';
        }

        if (!empty($filtros['de'])) {
            $onde[] = "u.cadastrado_em >= :de";
            $parametros['de'] = $filtros['de'] . ' 00:00:00';
        }

        if (!empty($filtros['ate'])) {
            $onde[] = "u.cadastrado_em <= :ate";
            $parametros['ate'] = $filtros['ate'] . ' 23:59:59';
        }

        if (($filtros['status'] ?? '') !== '' && isset($filtros['status'])) {
            $onde[] = "u.status = :status";
            $parametros['status'] = (int) $filtros['status'];
        }

        if (($filtros['repetidos'] ?? '') === '1') {
            $onde[] = "g.qtd_registros > 1";
        }

        if (($filtros['endereco'] ?? '') === 'com') {
            $onde[] = "e.id IS NOT NULL";
        }

        if (($filtros['endereco'] ?? '') === 'sem') {
            $onde[] = "e.id IS NULL";
        }

        $faixas = [
            'ate_30_dias' => "TIMESTAMPDIFF(DAY, u.cadastrado_em, NOW()) <= 30",
            'ate_6_meses' => "TIMESTAMPDIFF(DAY, u.cadastrado_em, NOW()) BETWEEN 31 AND 180",
            'ate_1_ano' => "TIMESTAMPDIFF(DAY, u.cadastrado_em, NOW()) BETWEEN 181 AND 365",
            'ate_2_anos' => "TIMESTAMPDIFF(DAY, u.cadastrado_em, NOW()) BETWEEN 366 AND 730",
            'mais_2_anos' => "TIMESTAMPDIFF(DAY, u.cadastrado_em, NOW()) > 730",
        ];

        if (!empty($filtros['tempo']) && isset($faixas[$filtros['tempo']])) {
            $onde[] = $faixas[$filtros['tempo']];
        }

        return [$onde ? 'WHERE ' . implode(' AND ', $onde) : '', $parametros];
    }

    /**
     * Executa a consulta e devolve as linhas como array
     * @param string $sql
     * @param array $parametros
     * @return array
     */
    private function consultar(string $sql, array $parametros = []): array
    {
        try {
            $stmt = Conexao::getInstancia()->prepare($sql);
            $stmt->execute($parametros);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $ex) {
            return [];
        }
    }
}
