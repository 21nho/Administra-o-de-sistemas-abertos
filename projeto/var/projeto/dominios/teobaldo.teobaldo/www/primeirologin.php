<?php
# DEFINE CONEXÃO COM O BD E VERIFICA SE O USER ESTÁ AUTENTICADO
# inicia sessão e define autenticação, cookies
session_start();
if (!($_SESSION["autenticado"])) {
    header("location: indexlogin.php");
    exit();  // Adicione o exit para garantir que o script pare após o redirecionamento
}

# CONECTAR AO BANCO DE DADOS
$bd = new mysqli("192.168.102.100", "container26", "1F(450307)", "BD26");

# MUDAR A PRÓPRIA SENHA / PROPRIASENHA #
# PEGANDO A VARIÁVEL SENHA DO CÓDIGO DO loginanalise.php
if (isset($_SESSION['senha'])) {
    $senha = $_SESSION['senha'];
}

# REALIZAR A ATUALIZAÇÃO DA SENHA NO BANCO DE DADOS
if (isset($_POST['propriasenha'])) {
    $propriasenha = $bd->real_escape_string($_POST['propriasenha']);
# ALTERA SENHA NA TABELA DE DOMÍNIOS E DE USUÁRIOS
# REDEFINE O VALOR DE SENHA PRA $PROPRIASENHA
    $_SESSION['senha'] = $propriasenha;
# REDIRECIONA PARA OUTRA PÁGINA APÓS A ALTERAÇÃO
    if (isset($_SESSION['gid'])) {
    $gid = $_SESSION['gid'];
    }
    switch($gid) {
        case '1': 
            header("location: escopoadmO.php");
            $bd->query("UPDATE USERSDOMINIOS SET senhas = '$propriasenha' WHERE senhas='$senha'");
            $bd->query("UPDATE USERS SET senha = '$propriasenha' WHERE senha ='$senha'");
            break;
        case '2':
            header("location: escopouser.php");
            $bd->query("UPDATE USERS SET senha = '$propriasenha' WHERE senha ='$senha'");
            break;
        default: header("location: indexlogin.php"); break;
    }
# se nao for nenhum desses gid (mesmo sendo praticamente impossivel sla tratei) redireciona pra pagina inicial
} 
?>
<style>
    body {
        background-color: #E3F2FD; /* Azul claro muito suave */
        color: #0D47A1; /* Azul escuro para o texto */
    }

    table {
        border-collapse: collapse;
        width: auto; /* Ajusta a tabela ao tamanho do conteúdo */
        margin: 20px auto; /* Centraliza a tabela */
    }

    th, td {
        border: 1px solid #BBDEFB; /* Azul muito claro */
        padding: 10px;
        text-align: left;
    }

    th {
        background-color: #90CAF9; /* Azul claro para o cabeçalho */
    }

    input[type="text"] {
        background-color: #E3F2FD; /* Azul claro muito suave */
        color: #0D47A1; /* Azul escuro para o texto */
        border: 1px solid #90CAF9; /* Azul claro para a borda */
        padding: 5px;
        border-radius: 4px; /* Adiciona bordas arredondadas */
    }

    input[type="submit"] {
        background-color: #90CAF9; /* Azul claro para o botão */
        color: #0D47A1; /* Azul escuro para o texto */
        border: none;
        padding: 10px 20px;
        cursor: pointer;
        border-radius: 4px; /* Adiciona bordas arredondadas */
    }

    input[type="submit"]:hover {
        background-color: #64B5F6; /* Azul médio para hover */
    }
</style>
<html>
<head>
    <title>Primeiro Login. . .</title>
</head>
<body>
    <h1>Troque sua própria senha:</h1>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
        <label for="propriasenha">Digite uma senha forte (exemplo: jul14n4volt4pr4m1m)</label>
        <input type="password" id="propriasenha" name="propriasenha" required>
        <input type="submit" value="Alterar">
    </form>
</body>
</html>

