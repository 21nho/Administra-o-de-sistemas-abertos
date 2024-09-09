<?php
/* analisa com cookies de sessão se o usuário ainda esta autenticaod, se nao tiver ele volta pra pag inicial,
depois disso seta conexão com o BD */
session_start();
if (!($_SESSION["autenticado"]))
        header("location: indexlogin.php");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$bd = new mysqli("192.168.102.100", "container26", "1F(450307)" ,"BD26");
if ($bd->connect_errno)
{
	die("falha ao conectar ao MySQL: (" . $bd->connect_errno . ") " . $bd->connect_error);
}

/*MUDAR A PRÓRPRIA SENHA: verifica se apertaram o botão e pega o que digitaram na caixa de texto, depois pega a senha que o user
digitou la no indexlogin e usa ela como parametro pra achar a linha (desse user logado) la no bd e substituir ela, depois ele atualiza
a variavel de sessao pra nova senha*/
if (isset($_POST['action']) && $_POST['action'] == 'alterarsenha') {
    $propriasenha = $bd->real_escape_string($_POST['propriasenha']);
    if (isset($_SESSION['senha'])) {
        $senha = $_SESSION['senha'];
        $bd->query("UPDATE USERS SET senha = '$propriasenha' WHERE senha ='$senha'");
        $_SESSION['senha'] = $propriasenha;
    }
    header("location: " . $_SERVER['PHP_SELF']);
    die();} # FUNCIONA #
$senha = $_SESSION['senha'];
$user = $_SESSION['email'];
?>

<!-- COMEÇO DO HTML/CSS o css eu só importei pro negocio nao ficar muito feio -->
<html>
<head>
    <TITLE>USER</TITLE>
<!-- CSS -->
<style>
    body {
        background-color: #F3E5F5; /* Lavanda bem clara */
        color: #6A1B9A; /* Roxo escuro para o texto */
     }

    table {
        border-collapse: collapse;
        width: auto; /* Ajusta a tabela ao tamanho do conteúdo */
        margin: 20px auto; /* Centraliza a tabela */
    }

    th, td {
        border: 1px solid #D1C4E9; /* Lilás muito claro */
        padding: 10px;
        text-align: left;
    }

    th {
            background-color: #E1BEE7; /* Lilás claro para o cabeçalho */
    }

    input[type="text"] {
        background-color: #F3E5F5; /* Lavanda bem clara */
        color: #6A1B9A; /* Roxo escuro para o texto */
        border: 1px solid #CE93D8; /* Lilás médio */
        padding: 5px;
        border-radius: 4px; /* Adiciona bordas arredondadas */
    }

    input[type="submit"] {
        background-color: #E1BEE7; /* Lilás claro */
        color: #6A1B9A; /* Roxo escuro para o texto */
        border: none;
        padding: 10px 20px;
        cursor: pointer;
        border-radius: 4px; /* Adiciona bordas arredondadas */
    }

    input[type="submit"]:hover {
        background-color: #CE93D8; /* Lilás médio para hover */
    }
</style>
</head>
<body>
<h1>Seja bem-vindo <?php echo $user ?>!</h1>
<table BORDER="1">
        <tr>
            <th>Senha</th>
        </tr>
            <tr>
            <td><input type="text" value="<?php echo $senha; ?>" readonly></td>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <label for="propriasenha">Alterar Própria Senha:</label>
                    <input type="text" id="propriasenha" name="propriasenha" required>
                    <input type="hidden" name="action" value="alterarsenha">
                    <input type="submit" value="Alterar"> <!-- FUNCIONA-->
                </form>
                <div class="espacobotao">
                <button type="button" onclick="window.location.href='sair.php'">SAIR</button> <!-- FUNCIONA-->
                <div>
            </td>
        </tr>
    </table>
</body>
</html>

