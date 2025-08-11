<?php

/**
 * functions.php - Funções auxiliares para o sistema de chamados hospitalares
 * Versão 3.1 - Correção de conflitos de funções
 * 
 * Este arquivo contém todas as funções helper utilizadas no sistema,
 * incluindo formatação, validação e utilitários diversos.
 */

// ================================
// FUNÇÕES DE FORMATAÇÃO
// ================================

/**
 * Formata um número de telefone para exibição
 * 
 * @param string $telefone Número de telefone (apenas números)
 * @return string Telefone formatado
 */
if (!function_exists('formatarTelefone')) {
    function formatarTelefone($telefone)
    {
        if (empty($telefone)) {
            return '';
        }

        // Remove caracteres não numéricos
        $numeros = preg_replace('/\D/', '', $telefone);

        // Formata conforme o padrão brasileiro
        if (strlen($numeros) === 11) {
            // Celular: (11) 91234-5678
            return sprintf(
                '(%s) %s-%s',
                substr($numeros, 0, 2),
                substr($numeros, 2, 5),
                substr($numeros, 7)
            );
        } elseif (strlen($numeros) === 10) {
            // Fixo: (11) 1234-5678
            return sprintf(
                '(%s) %s-%s',
                substr($numeros, 0, 2),
                substr($numeros, 2, 4),
                substr($numeros, 6)
            );
        }

        return $telefone;
    }
}

/**
 * Formata uma data para exibição (versão alternativa se já existir)
 * 
 * @param string $data Data no formato Y-m-d H:i:s
 * @param bool $incluirHora Se deve incluir a hora na formatação
 * @return string Data formatada
 */
if (!function_exists('formatarData')) {
    function formatarData($data, $incluirHora = true)
    {
        if (empty($data)) {
            return '';
        }

        try {
            $dateTime = new DateTime($data);

            if ($incluirHora) {
                return $dateTime->format('d/m/Y H:i:s');
            } else {
                return $dateTime->format('d/m/Y');
            }
        } catch (Exception $e) {
            return $data; // Retorna a data original se houver erro
        }
    }
} else {
    // Se formatarData já existe, cria uma versão alternativa
    if (!function_exists('formatarDataTelefone')) {
        function formatarDataTelefone($data, $incluirHora = true)
        {
            if (empty($data)) {
                return '';
            }

            try {
                $dateTime = new DateTime($data);

                if ($incluirHora) {
                    return $dateTime->format('d/m/Y H:i:s');
                } else {
                    return $dateTime->format('d/m/Y');
                }
            } catch (Exception $e) {
                return $data;
            }
        }
    }
}

/**
 * Formata um valor monetário
 * 
 * @param float $valor Valor a ser formatado
 * @param string $moeda Símbolo da moeda
 * @return string Valor formatado
 */
if (!function_exists('formatarMoeda')) {
    function formatarMoeda($valor, $moeda = 'R\$')
    {
        if (!is_numeric($valor)) {
            return $moeda . ' 0,00';
        }

        return $moeda . ' ' . number_format($valor, 2, ',', '.');
    }
}

/**
 * Formata um CPF para exibição
 * 
 * @param string $cpf CPF (apenas números)
 * @return string CPF formatado
 */
if (!function_exists('formatarCPF')) {
    function formatarCPF($cpf)
    {
        if (empty($cpf)) {
            return '';
        }

        $numeros = preg_replace('/\D/', '', $cpf);

        if (strlen($numeros) === 11) {
            return sprintf(
                '%s.%s.%s-%s',
                substr($numeros, 0, 3),
                substr($numeros, 3, 3),
                substr($numeros, 6, 3),
                substr($numeros, 9, 2)
            );
        }

        return $cpf;
    }
}

/**
 * Formata um CNPJ para exibição
 * 
 * @param string $cnpj CNPJ (apenas números)
 * @return string CNPJ formatado
 */
if (!function_exists('formatarCNPJ')) {
    function formatarCNPJ($cnpj)
    {
        if (empty($cnpj)) {
            return '';
        }

        $numeros = preg_replace('/\D/', '', $cnpj);

        if (strlen($numeros) === 14) {
            return sprintf(
                '%s.%s.%s/%s-%s',
                substr($numeros, 0, 2),
                substr($numeros, 2, 3),
                substr($numeros, 5, 3),
                substr($numeros, 8, 4),
                substr($numeros, 12, 2)
            );
        }

        return $cnpj;
    }
}

/**
 * Formata um CEP para exibição
 * 
 * @param string $cep CEP (apenas números)
 * @return string CEP formatado
 */
if (!function_exists('formatarCEP')) {
    function formatarCEP($cep)
    {
        if (empty($cep)) {
            return '';
        }

        $numeros = preg_replace('/\D/', '', $cep);

        if (strlen($numeros) === 8) {
            return sprintf(
                '%s-%s',
                substr($numeros, 0, 5),
                substr($numeros, 5, 3)
            );
        }

        return $cep;
    }
}

// ================================
// FUNÇÕES DE VALIDAÇÃO
// ================================

/**
 * Valida se um número de telefone é válido
 * 
 * @param string $telefone Número de telefone
 * @return bool True se válido, False caso contrário
 */
if (!function_exists('validarTelefone')) {
    function validarTelefone($telefone)
    {
        if (empty($telefone)) {
            return true; // Campo não obrigatório
        }

        $numeros = preg_replace('/\D/', '', $telefone);

        // Deve ter 10 ou 11 dígitos
        if (strlen($numeros) < 10 || strlen($numeros) > 11) {
            return false;
        }

        // Lista de DDDs válidos no Brasil
        $dddsValidos = [
            '11',
            '12',
            '13',
            '14',
            '15',
            '16',
            '17',
            '18',
            '19', // SP
            '21',
            '22',
            '24', // RJ
            '27',
            '28', // ES
            '31',
            '32',
            '33',
            '34',
            '35',
            '37',
            '38', // MG
            '41',
            '42',
            '43',
            '44',
            '45',
            '46', // PR
            '47',
            '48',
            '49', // SC
            '51',
            '53',
            '54',
            '55', // RS
            '61', // DF
            '62',
            '64', // GO
            '63', // TO
            '65',
            '66', // MT
            '67', // MS
            '68', // AC
            '69', // RO
            '71',
            '73',
            '74',
            '75',
            '77', // BA
            '79', // SE
            '81',
            '87', // PE
            '82', // AL
            '83', // PB
            '84', // RN
            '85',
            '88', // CE
            '86',
            '89', // PI
            '91',
            '93',
            '94', // PA
            '92',
            '97', // AM
            '95', // RR
            '96', // AP
            '98',
            '99'  // MA
        ];

        $ddd = substr($numeros, 0, 2);

        // Verifica se o DDD é válido
        if (!in_array($ddd, $dddsValidos)) {
            return false;
        }

        // Se for celular (11 dígitos), deve começar com 9
        if (strlen($numeros) === 11) {
            $nono = substr($numeros, 2, 1);
            if ($nono !== '9') {
                return false;
            }
        }

        return true;
    }
}

/**
 * Valida um email
 * 
 * @param string $email Email a ser validado
 * @return bool True se válido, False caso contrário
 */
if (!function_exists('validarEmail')) {
    function validarEmail($email)
    {
        if (empty($email)) {
            return true; // Campo não obrigatório
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

/**
 * Valida um CPF
 * 
 * @param string $cpf CPF a ser validado
 * @return bool True se válido, False caso contrário
 */
if (!function_exists('validarCPF')) {
    function validarCPF($cpf)
    {
        if (empty($cpf)) {
            return true; // Campo não obrigatório
        }

        $cpf = preg_replace('/\D/', '', $cpf);

        // Verifica se tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Calcula os dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }
}

/**
 * Valida um CNPJ
 * 
 * @param string $cnpj CNPJ a ser validado
 * @return bool True se válido, False caso contrário
 */
if (!function_exists('validarCNPJ')) {
    function validarCNPJ($cnpj)
    {
        if (empty($cnpj)) {
            return true; // Campo não obrigatório
        }

        $cnpj = preg_replace('/\D/', '', $cnpj);

        // Verifica se tem 14 dígitos
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Calcula o primeiro dígito verificador
        $soma = 0;
        $peso = 2;
        for ($i = 11; $i >= 0; $i--) {
            $soma += $cnpj[$i] * $peso;
            $peso = ($peso == 9) ? 2 : $peso + 1;
        }
        $resto = $soma % 11;
        $dv1 = ($resto < 2) ? 0 : 11 - $resto;

        // Calcula o segundo dígito verificador
        $soma = 0;
        $peso = 2;
        for ($i = 12; $i >= 0; $i--) {
            $soma += $cnpj[$i] * $peso;
            $peso = ($peso == 9) ? 2 : $peso + 1;
        }
        $resto = $soma % 11;
        $dv2 = ($resto < 2) ? 0 : 11 - $resto;

        return ($cnpj[12] == $dv1 && $cnpj[13] == $dv2);
    }
}

// ================================
// FUNÇÕES DE LIMPEZA
// ================================

/**
 * Limpa a formatação do telefone, deixando apenas números
 * 
 * @param string $telefone Telefone formatado
 * @return string Apenas números
 */
if (!function_exists('limparTelefone')) {
    function limparTelefone($telefone)
    {
        return preg_replace('/\D/', '', $telefone);
    }
}

/**
 * Limpa a formatação do CPF, deixando apenas números
 * 
 * @param string $cpf CPF formatado
 * @return string Apenas números
 */
if (!function_exists('limparCPF')) {
    function limparCPF($cpf)
    {
        return preg_replace('/\D/', '', $cpf);
    }
}

/**
 * Limpa a formatação do CNPJ, deixando apenas números
 * 
 * @param string $cnpj CNPJ formatado
 * @return string Apenas números
 */
if (!function_exists('limparCNPJ')) {
    function limparCNPJ($cnpj)
    {
        return preg_replace('/\D/', '', $cnpj);
    }
}

/**
 * Limpa a formatação do CEP, deixando apenas números
 * 
 * @param string $cep CEP formatado
 * @return string Apenas números
 */
if (!function_exists('limparCEP')) {
    function limparCEP($cep)
    {
        return preg_replace('/\D/', '', $cep);
    }
}

/**
 * Sanitiza uma string para uso seguro
 * 
 * @param string $input String a ser sanitizada
 * @return string String sanitizada
 */
if (!function_exists('sanitize_input')) {
    function sanitize_input($input)
    {
        if (empty($input)) {
            return '';
        }

        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

        return $input;
    }
}

// ================================
// FUNÇÕES DE STATUS E CORES
// ================================

/**
 * Retorna a cor correspondente ao status
 * 
 * @param string $status Nome do status
 * @return string Classe CSS da cor
 */
if (!function_exists('getStatusColor')) {
    function getStatusColor($status)
    {
        $cores = [
            'aberto' => 'primary',
            'em_andamento' => 'warning',
            'em_atendimento' => 'warning',
            'pausado' => 'info',
            'aguardando' => 'info',
            'concluido' => 'success',
            'finalizado' => 'success',
            'cancelado' => 'danger',
            'rejeitado' => 'danger',
            'urgente' => 'danger',
            'alta_prioridade' => 'warning',
            'baixa_prioridade' => 'info'
        ];

        $statusLimpo = strtolower(str_replace([' ', '-'], '_', $status));

        return isset($cores[$statusLimpo]) ? $cores[$statusLimpo] : 'primary';
    }
}

/**
 * Retorna o ícone correspondente ao status
 * 
 * @param string $status Nome do status
 * @return string Classe CSS do ícone
 */
if (!function_exists('getStatusIcon')) {
    function getStatusIcon($status)
    {
        $icones = [
            'aberto' => 'fas fa-folder-open',
            'em_andamento' => 'fas fa-cog fa-spin',
            'em_atendimento' => 'fas fa-user-md',
            'pausado' => 'fas fa-pause',
            'aguardando' => 'fas fa-clock',
            'concluido' => 'fas fa-check-circle',
            'finalizado' => 'fas fa-flag-checkered',
            'cancelado' => 'fas fa-times-circle',
            'rejeitado' => 'fas fa-ban',
            'urgente' => 'fas fa-exclamation-triangle',
            'alta_prioridade' => 'fas fa-arrow-up',
            'baixa_prioridade' => 'fas fa-arrow-down'
        ];

        $statusLimpo = strtolower(str_replace([' ', '-'], '_', $status));

        return isset($icones[$statusLimpo]) ? $icones[$statusLimpo] : 'fas fa-circle';
    }
}

// ================================
// FUNÇÕES DE TEMPO
// ================================

/**
 * Calcula o tempo decorrido entre duas datas
 * 
 * @param string $dataInicio Data de início
 * @param string $dataFim Data de fim (opcional, usa data atual se não informada)
 * @return string Tempo formatado
 */
if (!function_exists('calcularTempoDecorrido')) {
    function calcularTempoDecorrido($dataInicio, $dataFim = null)
    {
        try {
            $inicio = new DateTime($dataInicio);
            $fim = $dataFim ? new DateTime($dataFim) : new DateTime();

            $diff = $inicio->diff($fim);

            $tempoFormatado = '';

            if ($diff->y > 0) {
                $tempoFormatado .= $diff->y . ' ano(s), ';
            }

            if ($diff->m > 0) {
                $tempoFormatado .= $diff->m . ' mês(es), ';
            }

            if ($diff->d > 0) {
                $tempoFormatado .= $diff->d . ' dia(s), ';
            }

            $tempoFormatado .= sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);

            return $tempoFormatado;
        } catch (Exception $e) {
            return 'Tempo indisponível';
        }
    }
}

/**
 * Converte segundos em formato legível
 * 
 * @param int $segundos Número de segundos
 * @return string Tempo formatado
 */
if (!function_exists('segundosParaTempo')) {
    function segundosParaTempo($segundos)
    {
        $dias = floor($segundos / 86400);
        $horas = floor(($segundos % 86400) / 3600);
        $minutos = floor(($segundos % 3600) / 60);
        $segs = $segundos % 60;

        $tempo = '';

        if ($dias > 0) {
            $tempo .= $dias . ' dia(s), ';
        }

        $tempo .= sprintf('%02d:%02d:%02d', $horas, $minutos, $segs);

        return $tempo;
    }
}

// ================================
// FUNÇÕES DE TEXTO
// ================================

/**
 * Trunca um texto mantendo palavras inteiras
 * 
 * @param string $texto Texto a ser truncado
 * @param int $limite Limite de caracteres
 * @param string $sufixo Sufixo a ser adicionado
 * @return string Texto truncado
 */
if (!function_exists('truncarTexto')) {
    function truncarTexto($texto, $limite = 100, $sufixo = '...')
    {
        if (strlen($texto) <= $limite) {
            return $texto;
        }

        $textoTruncado = substr($texto, 0, $limite);
        $ultimoEspaco = strrpos($textoTruncado, ' ');

        if ($ultimoEspaco !== false) {
            $textoTruncado = substr($textoTruncado, 0, $ultimoEspaco);
        }

        return $textoTruncado . $sufixo;
    }
}

/**
 * Converte texto para slug (URL amigável)
 * 
 * @param string $texto Texto a ser convertido
 * @return string Slug gerado
 */
if (!function_exists('gerarSlug')) {
    function gerarSlug($texto)
    {
        // Remove acentos
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);

        // Converte para minúsculas
        $texto = strtolower($texto);

        // Remove caracteres especiais
        $texto = preg_replace('/[^a-z0-9\s-]/', '', $texto);

        // Substitui espaços e múltiplos hífens por um hífen
        $texto = preg_replace('/[\s-]+/', '-', $texto);

        // Remove hífens do início e fim
        $texto = trim($texto, '-');

        return $texto;
    }
}

/**
 * Destaca termos de busca em um texto
 * 
 * @param string $texto Texto original
 * @param string $termo Termo a ser destacado
 * @param string $classe Classe CSS para destaque
 * @return string Texto com destaque
 */
if (!function_exists('destacarBusca')) {
    function destacarBusca($texto, $termo, $classe = 'highlight')
    {
        if (empty($termo)) {
            return $texto;
        }

        return preg_replace(
            '/(' . preg_quote($termo, '/') . ')/i',
            '<span class="' . $classe . '">$1</span>',
            $texto
        );
    }
}

// ================================
// FUNÇÕES DE ARQUIVO
// ================================

/**
 * Formata o tamanho de arquivo para exibição
 * 
 * @param int $bytes Tamanho em bytes
 * @param int $precisao Precisão decimal
 * @return string Tamanho formatado
 */
if (!function_exists('formatarTamanhoArquivo')) {
    function formatarTamanhoArquivo($bytes, $precisao = 2)
    {
        $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($unidades) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precisao) . ' ' . $unidades[$i];
    }
}

/**
 * Verifica se um arquivo é uma imagem válida
 * 
 * @param string $arquivo Caminho do arquivo
 * @return bool True se for imagem válida
 */
if (!function_exists('isImagemValida')) {
    function isImagemValida($arquivo)
    {
        if (!file_exists($arquivo)) {
            return false;
        }

        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $tipoArquivo = mime_content_type($arquivo);

        return in_array($tipoArquivo, $tiposPermitidos);
    }
}

// ================================
// FUNÇÕES DE SEGURANÇA
// ================================

/**
 * Gera um token CSRF
 * 
 * @return string Token gerado
 */
if (!function_exists('gerarTokenCSRF')) {
    function gerarTokenCSRF()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

/**
 * Verifica um token CSRF
 * 
 * @param string $token Token a ser verificado
 * @return bool True se válido
 */
if (!function_exists('verificarTokenCSRF')) {
    function verificarTokenCSRF($token)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

/**
 * Gera uma senha aleatória
 * 
 * @param int $tamanho Tamanho da senha
 * @param bool $incluirSimbolos Se deve incluir símbolos
 * @return string Senha gerada
 */
if (!function_exists('gerarSenhaAleatoria')) {
    function gerarSenhaAleatoria($tamanho = 12, $incluirSimbolos = true)
    {
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        if ($incluirSimbolos) {
            $caracteres .= '!@#$%^&*()_+-=[]{}|;:,.<>?';
        }

        $senha = '';
        $maxIndex = strlen($caracteres) - 1;

        for ($i = 0; $i < $tamanho; $i++) {
            $senha .= $caracteres[random_int(0, $maxIndex)];
        }

        return $senha;
    }
}

// ================================
// FUNÇÕES DE LOG
// ================================

/**
 * Registra um log no sistema
 * 
 * @param string $nivel Nível do log (INFO, WARNING, ERROR)
 * @param string $mensagem Mensagem do log
 * @param array $contexto Contexto adicional
 * @return bool True se registrado com sucesso
 */
if (!function_exists('registrarLog')) {
    function registrarLog($nivel, $mensagem, $contexto = [])
    {
        $logDir = __DIR__ . '/../logs/';

        // Cria diretório se não existir
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $arquivo = $logDir . 'sistema_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $usuario = $_SESSION['usuario_id'] ?? 'Sistema';

        $logEntry = "[$timestamp] [$nivel] [IP: $ip] [User: $usuario] $mensagem";

        if (!empty($contexto)) {
            $logEntry .= ' | Contexto: ' . json_encode($contexto);
        }

        $logEntry .= PHP_EOL;

        return file_put_contents($arquivo, $logEntry, FILE_APPEND | LOCK_EX) !== false;
    }
}

// ================================
// FUNÇÕES DE DEBUG
// ================================

/**
 * Função de debug melhorada
 * 
 * @param mixed $dados Dados a serem exibidos
 * @param string $label Label para identificação
 * @param bool $die Se deve parar a execução
 */
if (!function_exists('debug')) {
    function debug($dados, $label = 'DEBUG', $die = false)
    {
        echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin: 10px 0; border-radius: 5px; font-family: monospace;'>";
        echo "<strong style='color: #495057;'>$label:</strong><br>";
        echo "<pre style='margin: 10px 0; color: #212529;'>";
        print_r($dados);
        echo "</pre>";
        echo "</div>";

        if ($die) {
            die();
        }
    }
}

/**
 * Registra informações de debug em arquivo
 * 
 * @param mixed $dados Dados a serem registrados
 * @param string $label Label para identificação
 */
if (!function_exists('debugLog')) {
    function debugLog($dados, $label = 'DEBUG')
    {
        $debugDir = __DIR__ . '/../logs/debug/';

        if (!is_dir($debugDir)) {
            mkdir($debugDir, 0755, true);
        }

        $arquivo = $debugDir . 'debug_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');

        $logEntry = "[$timestamp] [$label] " . print_r($dados, true) . PHP_EOL;

        file_put_contents($arquivo, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

// ================================
// FUNÇÕES ESPECÍFICAS DO SISTEMA
// ================================

/**
 * Verifica se o usuário está logado
 * 
 * @return bool True se logado
 */
if (!function_exists('isLoggedIn')) {
    function isLoggedIn()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
    }
}

/**
 * Obtém o ID do usuário logado
 * 
 * @return int|null ID do usuário ou null se não logado
 */
if (!function_exists('get_user_id')) {
    function get_user_id()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['usuario_id'] ?? null;
    }
}

/**
 * Obtém o ID da empresa do usuário logado
 * 
 * @return int|null ID da empresa ou null se não logado
 */
if (!function_exists('get_empresa_id')) {
    function get_empresa_id()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['empresa_id'] ?? null;
    }
}

/**
 * Verifica se o usuário tem uma permissão específica
 * 
 * @param string $permissao Nome da permissão
 * @return bool True se tem permissão
 */
if (!function_exists('hasPermission')) {
    function hasPermission($permissao)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return in_array($permissao, $_SESSION['permissoes'] ?? []);
    }
}

/**
 * Redireciona para uma URL
 * 
 * @param string $url URL de destino
 * @param int $codigo Código HTTP de redirecionamento
 */
if (!function_exists('redirect')) {
    function redirect($url, $codigo = 302)
    {
        header("Location: $url", true, $codigo);
        exit();
    }
}

/**
 * Define uma mensagem flash
 * 
 * @param string $tipo Tipo da mensagem (success, error, warning, info)
 * @param string $mensagem Mensagem a ser exibida
 */
if (!function_exists('set_flash_message')) {
    function set_flash_message($tipo, $mensagem)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_messages'][] = [
            'tipo' => $tipo,
            'mensagem' => $mensagem
        ];
    }
}

/**
 * Obtém e limpa as mensagens flash
 * 
 * @return array Array de mensagens flash
 */
if (!function_exists('get_flash_messages')) {
    function get_flash_messages()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $mensagens = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $mensagens;
    }
}

/**
 * Gera a URL base do sistema
 * 
 * @param string $path Caminho adicional
 * @return string URL completa
 */
if (!function_exists('base_url')) {
    function base_url($path = '')
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);

        $baseUrl = $protocol . '://' . $host . $scriptName;

        if ($path) {
            $baseUrl .= '/' . ltrim($path, '/');
        }

        return $baseUrl;
    }
}

/**
 * Carrega uma view
 * 
 * @param string $view Nome da view
 * @param array $data Dados para a view
 */
if (!function_exists('load_view')) {
    function load_view($view, $data = [])
    {
        extract($data);

        $viewFile = __DIR__ . "/../views/$view.php";

        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new Exception("View não encontrada: $view");
        }
    }
}
